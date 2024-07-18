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
   *   The bearer token or NULL if an error occurred
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function getAccessToken(): ?string;

  /**
   * Make a GET or PATCH call to the API.
   *
   * @param string $method
   *   The request method, 'GET' or 'PATCH'.
   * @param string $call
   *   The API call to make.
   * @param array $data
   *   Data to provide.
   *
   * @return array
   *   Array of data returned by the API.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function apiCall(string $method, string $call, array $data = []): array;

  /**
   * Update the polygon of an entity.
   *
   * @param string $global_id
   *   The global ID of the entity to be updated.
   * @param string $polygon
   *   The polygon to update.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function updatePolygon(string $global_id, string $polygon): void;

}
