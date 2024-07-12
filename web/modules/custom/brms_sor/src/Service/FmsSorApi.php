<?php declare(strict_types = 1);

namespace Drupal\brms_sor\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\key\KeyRepositoryInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Client\ClientInterface;

/**
 * Implementation of System of Record API interface for FMS.
 */
final class FmsSorApi implements SorApiInterface {
  use StringTranslationTrait;

  /**
   * GuzzleHttp client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The JSON serializer service.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected $json;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * FMS API auth URL.
   *
   * @var string
   */
  protected $authUrl;

  /**
   * FMS API base URL.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * FMS API client id.
   *
   * @var string
   */
  protected $clientId;

  /**
   * Secret of the DnB client.
   *
   * @var string
   */
  protected $clientSecret;

  /**
   * Constructs a FmsSorApi object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The GuzzleHttp Client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\key\KeyRepositoryInterface $key_repository
   *   The key repository.
   * @param \Drupal\Component\Serialization\Json $json_service
   *   The JSON serializer service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    protected ClientInterface $http_client,
    protected LoggerChannelFactoryInterface $logger_factory,
    protected CacheBackendInterface $cache_backend,
    protected KeyRepositoryInterface $key_repository,
    protected Json $json_service,
    protected TimeInterface $time,
  ) {
    $this->httpClient = $http_client;
    $this->logger = $logger_factory;
    $this->cache = $cache_backend;
    $this->json = $json_service;
    $settings = Settings::get('brms_sor');
    $this->authUrl = $settings['fms_api_auth_url'];
    $this->baseUrl = $settings['fms_api_base_url'];
    $this->clientId = $settings['fms_api_client_id'];
    $this->clientSecret = $key_repository->getKey('fms_api')->getKeyValue();
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessToken(bool $use_cached_token = TRUE): string {
    if ($use_cached_token) {
      $cache_access_token = $this->cache->get('brms_sor_fms_access_token');
      if ($cache_access_token) {
        return $cache_access_token->data;
      }
    }

    try {
      $response = $this->httpClient->request('POST', $this->authUrl, [
        'headers' => [
          'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'form_params' => [
          'grant_type' => 'client_credentials',
          'client_id' => $this->clientId,
          'client_secret' => $this->clientSecret,
        ],
      ]);

      $data = $this->json->decode($response->getBody()->getContents());
      $this->cache->set('brms_sor_fms_access_token', $data['access_token'], $this->time->getRequestTime() + $data['expires_in']);

      return $data['access_token'];
    }
    catch (\Exception $e) {
      $this->logger->get('brms_sor')->error('Failed to get access token from the FMS API: @message', ['@message' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $call, array $data = [], bool $use_cached_token = TRUE): array {
    try {
      $token = $this->getAccessToken($use_cached_token);
      $headers = [
        'Authorization' => 'Bearer ' . $token,
      ];

      $args = ['headers' => $headers, 'query' => $data];
      $uri = $this->baseUrl . $call;
      $this->response = $this->httpClient->get($uri, $args);
      return $this->json->decode($this->response->getBody()->getContents());
    }
    catch (RequestException $e) {
      $this->response = $e->getResponse();
      $this->logger->get('brms_sor')->error('Failed to call GET @uri on FMS API: @message',
        ['@uri' => $uri, '@message' => $e->getMessage()]);
      // If the token expired, try again with a new token.
      if ($e->getCode() === 401 && $use_cached_token) {
        return $this->get($call, $data, FALSE);
      }
      else {
        throw $e;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function patch(string $call, array $data = [], bool $use_cached_token = TRUE): array {
    try {
      $token = $this->getAccessToken($use_cached_token);
      $headers = [
        'Authorization' => 'Bearer ' . $token,
        'Content-type' => 'application/vnd.api+json',
      ];

      $args = ['headers' => $headers, 'json' => $data];
      $uri = $this->baseUrl . $call;
      $this->response = $this->httpClient->patch($uri, $args);
      return $this->json->decode($this->response->getBody()->getContents());
    }
    catch (RequestException $e) {
      $this->response = $e->getResponse();
      $this->logger->get('brms_sor')->error('Failed to call PATCH @uri on FMS API: @message',
        ['@uri' => $uri, '@message' => $e->getMessage()]);
      // If the token expired, try again with a new token.
      if ($e->getCode() === 401 && $use_cached_token) {
        return $this->patch($call, $data, FALSE);
      }
      else {
        throw $e;
      }
    }
  }

  /**
   * Get the UUID of a CFR by its global ID.
   */
  public function getCfrUuid(string $cfr_global_id): ?string {
    try {
      $response = $this->get('/asset/cfr', ['filter[cfr_global_id]' => $cfr_global_id]);
      return $response['data'][0]['id'] ?? NULL;
    }
    catch (RequestException $e) {
      $this->logger->get('brms_sor')->error('Failed to get CFR UUID for global ID @cfr_global_id: @message',
        ['@cfr_global_id' => $cfr_global_id, '@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Update the polygon of a CFR.
   */
  public function updateCfrPolygon(string $cfr_global_id, ?string $polygon): void {
    // Get the UUID of the CFR.
    $uuid = $this->getCfrUuid($cfr_global_id);
    if ($uuid) {
      // Call the API to update the CFR.
      $body['data'] = [
        'type' => 'asset',
        'id' => $uuid,
        'attributes' => ['intrinsic_geometry' => $polygon],
      ];
      try {
        $response = $this->patch('/asset/cfr/' . $uuid, $body);
      }
      catch (RequestException $e) {
        $this->logger->get('brms_sor')
          ->error('Failed to update CFR polygon for global ID @cfr_global_id: @message', [
            '@cfr_global_id' => $cfr_global_id,
            '@message' => $e->getMessage(),
          ]);
      }
    }
    else {
      $this->logger->get('brms_sor')
        ->error('Failed to update CFR polygon for global ID @cfr_global_id: CFR not found.', [
          '@cfr_global_id' => $cfr_global_id,
        ]);
    }
  }

}
