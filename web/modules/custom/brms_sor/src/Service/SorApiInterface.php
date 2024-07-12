<?php declare(strict_types = 1);

namespace Drupal\brms_sor\Service;

/**
 * System of Record API interface.
 */
interface SorApiInterface {

  /**
   * Gets a bearer token for accessing the API.
   *
   * @return string|null
   *   The bearer token.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function getAccessToken(bool $use_cached_token = TRUE): ?string;

  /**
   * Make a GET call to the API.
   *
   * @param string $call
   *   The API call to make.
   * @param array $data
   *   Data to provide.
   * @param bool $use_cached_token
   *   Whether to use the cached bearer token or generate a new one.
   *
   * @return array
   *   Array of data returned by the API.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function get(string $call, array $data = [], bool $use_cached_token = TRUE): array;

  /**
   * Make a PATCH call to the API.
   *
   * @param string $call
   *   The API call to make.
   * @param array $data
   *   Data to provide.
   * @param bool $use_cached_token
   *   Whether to use the cached bearer token or generate a new one.
   *
   * @return array
   *   Array of data returned by the API.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function patch(string $call, array $data = [], bool $use_cached_token = TRUE): array;

}
