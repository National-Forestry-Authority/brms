<?php declare(strict_types = 1);

namespace Drupal\brms_sor\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\key\KeyRepositoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

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
   * The temp store.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

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
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $temp_store_factory
   *   The temp store factory.
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
    SharedTempStoreFactory $temp_store_factory,
    protected KeyRepositoryInterface $key_repository,
    protected Json $json_service,
    protected TimeInterface $time,
  ) {
    $this->httpClient = $http_client;
    $this->logger = $logger_factory;
    $this->tempStore = $temp_store_factory->get('brms_sor_fms_access_token');
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
  public function getAccessToken(): ?string {
    // If there is a stored token, and it's not going to expire in the next
    // minute, return it, otherwise generate a new one.
    $tokenData = $this->tempStore->get('token_data');
    if ($tokenData && $tokenData['expire'] > ($this->time->getRequestTime() + 60)) {
      return $tokenData['token'];
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
      // Store the token and its expiration time in the temp store.
      $this->tempStore->set('token_data', [
        'token' => $data['access_token'],
        'expire' => $this->time->getRequestTime() + $data['expires_in'],
      ]);
      return $data['access_token'];
    }
    catch (\Exception $e) {
      $this->logger->get('brms_sor')->error('Failed to get access token from the FMS API: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function apiCall(string $method, string $call, array $data = []): array {
    try {
      $token = $this->getAccessToken();
      $headers = [
        'Authorization' => 'Bearer ' . $token,
      ];
      if ($method === 'PATCH') {
        $headers['Content-type'] = 'application/vnd.api+json';
      }

      $args = ['headers' => $headers];
      if ($method === 'GET') {
        $args['query'] = $data;
      }
      else {
        $args['json'] = $data;
      }
      $uri = $this->baseUrl . $call;
      $response = $this->httpClient->request($method, $uri, $args);
      return $this->json->decode($response->getBody()->getContents());
    }
    catch (RequestException $e) {
      $this->logger->get('brms_sor')->error('Failed to call @method @uri on FMS API: @message',
        ['@method' => $method, '@uri' => $uri, '@message' => $e->getMessage()]);
      // Throw the exception to the queue processor so the item stays in the
      // queue and can be retried.
      throw $e;
    }
  }

  /**
   * Get the UUID of a CFR by its global ID.
   */
  protected function getCfrUuid(string $cfr_global_id): ?string {
    try {
      // Get the UUID of the CFR with the given global ID in the FMS.
      $response = $this->apiCall('GET', '/asset/cfr', ['filter[cfr_global_id]' => $cfr_global_id]);
      return $response['data'][0]['id'] ?? NULL;
    }
    catch (RequestException $e) {
      $this->logger->get('brms_sor')->error('Failed to get CFR UUID for global ID @cfr_global_id: @message',
        ['@cfr_global_id' => $cfr_global_id, '@message' => $e->getMessage()]);
      // Throw the exception to the queue processor so the item stays in the
      // queue and can be retried.
      throw $e;
    }
  }

  /**
   * Update the polygon of a CFR.
   */
  public function updatePolygon(string $global_id, ?string $polygon): void {
    // Get the UUID of the CFR.
    $uuid = $this->getCfrUuid($global_id);
    if ($uuid) {
      // Call the API to update the CFR.
      $body['data'] = [
        'type' => 'asset',
        'id' => $uuid,
        'attributes' => ['intrinsic_geometry' => $polygon],
      ];
      try {
        $response = $this->apiCall('PATCH', '/asset/cfr/' . $uuid, $body);
      }
      catch (RequestException $e) {
        $this->logger->get('brms_sor')
          ->error('Failed to update CFR polygon for global ID @cfr_global_id: @message', [
            '@cfr_global_id' => $global_id,
            '@message' => $e->getMessage(),
          ]);
        // Throw the exception to the queue processor so the item stays in the
        // queue and can be retried.
        throw $e;
      }
    }
    else {
      $this->logger->get('brms_sor')
        ->error('Failed to update CFR polygon for global ID @cfr_global_id: CFR not found.', [
          '@cfr_global_id' => $global_id,
        ]);
      // We don't throw an exception here because this is a permanent error and
      // the item should be removed from the queue.
    }
  }

}
