<?php

namespace Drupal\brms_sor\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Plugin implementation of the 'master_polygon_kml' field type.
 *
 * @FieldType(
 *   id = "master_polygon_kml",
 *   label = @Translation("Master Polygon KML"),
 *   description = @Translation("Computed field that gets master polygon KML string of a forest reserve."),
 *   default_widget = "string_textfield",
 *   default_formatter = "string",
 * )
 */
class MasterPolygonKML extends FieldItemList {
  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  public function computeValue() {
    $node = $this->getEntity();
    if ($node->getType() === 'forest_reserve') {
      $polygon = NULL;
      if ($node->hasField('geolayers') && !$node->get('geolayers')->isEmpty()) {
        $geolayers = $node->get('geolayers')->referencedEntities();
        foreach ($geolayers as $geolayer) {
          if ($geolayer->hasField('layer_type') && !$geolayer->get('layer_type')->isEmpty()) {
            $layer_type = $geolayer->get('layer_type')
              ->first()
              ->get('entity')
              ->getTarget()
              ->getValue();
            if ($layer_type->get('master_polygon')->value) {
              $polygon = $geolayer->get('geofield')->value;
              break;
            }
          }
        }
      }
    }
    $item = $this->createItem(0, $polygon);
    $this->list[0] = $item;
  }

}
