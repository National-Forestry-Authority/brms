<?php

namespace Drupal\brms_common;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;
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
    // The dimension we want to calculate is stored in the computed field's
    // settings.
    $field_id = $this->getName();
    $field_settings = $node->get($field_id)->getSettings();
    $dimension_to_calculate = $field_settings['computed_field'];
    $computed_type = $field_settings['computed_geolayer_field'];

    if (!empty($dimension_to_calculate)) {
      $added_layers = $node->geolayers->getValue();
      if (empty($added_layers)) {
        return;
      }
      // Iterate through the geolayers uploaded to the node and look for one
      // with a layer type that matches the dimension we are calculating.
      foreach ($added_layers as $added_layer) {
        $geolayer = \Drupal::entityTypeManager()->getStorage('geolayer')->load($added_layer['target_id']);
        $layer_type = Term::load($geolayer->layer_type->target_id);
        $associated_computed_type = $layer_type->computed_geolayer_field->value;

        // If the geolayer type matches the dimension we want to calculate go
        // ahead and extract the geolayer's geometry and do the calculation.
        if ($associated_computed_type == $computed_type) {
          $geofield = $geolayer->geofield->getValue();
          if (empty($geofield[0]['value'])) {
            return;
          }

          // @todo inject the geophp service.
          $wkt = \Drupal::service('geofield.geophp')->load($geofield[0]['value'], 'wkt');
          if ($dimension_to_calculate == 'length') {
            // To calculate the length of a polygon we must add together the
            // lengths of the linestring components that make up the polygon.
            if ($wkt->getGeomType() == 'Polygon') {
              $length = 0;
              foreach ($wkt->components as $component) {
                // Return length in meters.
                $length += $component->greatCircleLength();
              }
              // Set length in kilometers.
              $this->list[0] = $this->createItem(0, $length / 1000);
            }
            else {
              // Set length in kilometers.
              $this->list[0] = $this->createItem(0, $wkt->greatCircleLength() / 1000);
            }
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
