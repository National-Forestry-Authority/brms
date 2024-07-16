<?php

namespace Drupal\brms_sor\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Service for retrieving Forest Reserves data.
 */
class ForestReserveService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ForestReserveService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get the master polygon for a forest reserve.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The forest reserve node.
   *
   * @return string|null
   *   The master polygon.
   */
  public function getMasterPolygon(NodeInterface $node) {
    if ($node->getType() != 'forest_reserve') {
      return NULL;
    }

    // Iterate through the geolayers to find the master polygon.
    if ($node->hasField('geolayers') && !$node->get('geolayers')->isEmpty()) {
      $geolayers = $node->get('geolayers')->referencedEntities();
      foreach ($geolayers as $geolayer) {
        if ($geolayer->hasField('layer_type') && !$geolayer->get('layer_type')->isEmpty()) {
          $layer_type = $geolayer->get('layer_type')->referencedEntities();
          $layer_type = reset($layer_type);
          if ($layer_type->get('master_polygon')->value) {
            return $geolayer->get('geofield')->value;
          }
        }
      }
    }

    return NULL;
  }

}
