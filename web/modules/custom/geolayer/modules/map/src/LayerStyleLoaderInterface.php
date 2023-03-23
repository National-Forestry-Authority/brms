<?php

namespace Drupal\geolayer_map;

use Drupal\geolayer_map\Entity\LayerStyleInterface;

/**
 * Layer style loader interface.
 */
interface LayerStyleLoaderInterface {

  /**
   * Load a single layer style definition, optionally filtered by conditions.
   *
   * @param array $conditions
   *   An array of conditions for filtering.
   *
   * @return \Drupal\geolayer_map\Entity\LayerStyleInterface|null
   *   Returns a layer style definition, or NULL if no matches were found.
   */
  public function load(array $conditions = []): ?LayerStyleInterface;

}
