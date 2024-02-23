<?php

namespace Drupal\brms_common;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\geofield\GeoPHP\GeoPHPInterface;
use Drupal\taxonomy\Entity\Term;

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
    // Extracting the exact compute dimension to calculate.
    $dimension_id = $this->getName();
    $dimension_settings = $node->get($dimension_id)->getSettings();
    // Extracting the dimension to calculate.
    $dimension_to_calculate = $dimension_settings['computed_field'];
    // Extracting the geolayer field to match with the correct geolayer.
    $dimension_computed_id = $dimension_settings['computed_geolayer_field'];
    if (isset($dimension_to_calculate) && $dimension_to_calculate != '') {
      $added_layers = $node->get('geolayers');
      if (!isset($added_layers) || empty($added_layers)) {
        return;
      }
      $added_layers = $added_layers->getValue();
      for ($i = 0; $i < count($added_layers); $i++) {
        $geolayer = \Drupal::entityTypeManager()->getStorage('geolayer')->load($added_layers[$i]['target_id']);
        $layer_type = $geolayer->get('layer_type')->getValue();
        $layer_taxonomy = Term::load($layer_type[0]['target_id']);
        $layer_id = $layer_taxonomy->get('computed_geolayer_field')->value;
        $geofield = $geolayer->get('geofield')->getValue();
        if (!isset($geofield[0]['value']) || empty($geofield[0]['value'])) {
          return;
        }
        $wkt = \geoPHP::load($geofield[0]['value'], 'wkt');
        // Matching the dimension field with the correct geolayer.
        if ($layer_id == $dimension_computed_id) {
          // Calculating the dimension.
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
