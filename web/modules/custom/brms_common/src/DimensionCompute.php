<?php

namespace Drupal\brms_common;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\geofield\GeoPHP\GeoPHPInterface;

/**
 * Class DimensionCompute, to compute the values of the dimension fields.
 *
 * @package Drupal\brms_common
 */
class DimensionCompute extends FieldItemList implements FieldItemListInterface {

  use ComputedItemListTrait;

  /**
   * Compute the values.
   */
  protected function computeValue() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getParent()->getValue();
    $dimension_name = $this->getFieldDefinition()['label'];
    $dimension_name = (string) $dimension_name;
    $dimension_id = $this->getName();
    $layer_compute_attributes = [
      'interprotected_area_length_computed' => 'length',
      'riverline_length_computed' => 'length',
      'shoreline_length_computed' => 'length',
      'protected_area_length_computed' => 'length',
      'cutline_length_computed' => 'length',
      'wetland_length_computed' => 'length',
      'international_length_computed' => 'length',
      'total_area_computed' => 'area',
      'intermediate_pillar_computed' => 'points',
      'corner_pillar_computed' => 'points',
      'cairn_computed' => 'points',
    ];
    $dimension_to_calculate = $layer_compute_attributes[$dimension_id];
    if ($node->hasField('riverline_length') && $node->hasField('protected_area_length')) {
      $added_layers = $node->get('geolayers')->getValue();

      for ($i = 0; $i < count($added_layers); $i++) {
        $geolayer = \Drupal::entityTypeManager()->getStorage('geolayer')->load($added_layers[$i]['target_id']);
        $geofield = $geolayer->get('geofield')->getValue();
        $wkt = \geoPHP::load($geofield[0]['value'], 'wkt');
        $label = $geolayer->get('label')->value;
        $label = explode(': ', $label);
        $label = $label[1];
        if (strpos(strtolower($dimension_name), strtolower($label)) !== FALSE) {
          if ($dimension_to_calculate == 'length') {
            $this->list[0] = $this->createItem(0, $wkt->length());
          }
          elseif ($dimension_to_calculate == 'area') {
            $this->list[0] = $this->createItem(0, $wkt->area());
          }
          elseif ($dimension_to_calculate == 'points') {
            $this->list[0] = $this->createItem(0, $wkt->numPoints());
          }
        }
      }
    }
  }

}
