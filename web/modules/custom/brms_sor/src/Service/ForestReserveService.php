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

    $geolayers = $node->get('geolayers')->getValue();
    foreach ($geolayers as $geolayer) {
      $geolayerEntity = $this->entityTypeManager->getStorage('geolayer')->load($geolayer['target_id']);
      $layerType = $geolayerEntity->get('layer_type')->entity;

      if ($layerType && $layerType->getName() == 'Master polygon') {
        return $geolayerEntity->get('geofield')->value;
      }
    }

    return NULL;
  }

}
