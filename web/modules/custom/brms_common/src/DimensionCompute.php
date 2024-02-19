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
    $dimension_name = $this->getFieldDefinition()['label'];
    $dimension_name = (string) $dimension_name;
    $dimension_id = $this->getName();
    $dimension_settings = $node->get($dimension_id)->getSettings();
    $dimension_to_calculate = $dimension_settings['computed_field'];
    $dimension_computed_id = $dimension_settings['layer_type_id'];
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
        $layer_id = $layer_taxonomy->get('layer_type_id')->value;
        $geofield = $geolayer->get('geofield')->getValue();
        $wkt = \geoPHP::load($geofield[0]['value'], 'wkt');
        if ($layer_id == $dimension_computed_id) {
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
