<?php

namespace Drupal\brms_common;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\taxonomy\Entity\Term;

/**
 * Computes various forest reserve dimensions from geolayer geometry.
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

    // The metric we want to calculate is stored in the computed field's
    // settings.
    $field_settings = $node->get($this->getName())->getSettings();
    $dimension_to_calculate = $field_settings['metric'];

    if (!empty($dimension_to_calculate)) {
      $added_layers = $node->geolayers->getValue();
      if (empty($added_layers)) {
        return;
      }
      // Iterate through the geolayers uploaded to the node and look for one
      // with a layer type that matches the field we are calculating.
      foreach ($added_layers as $added_layer) {
        if (!$added_layer['target_id']) {
          continue;
        }
        $geolayer = \Drupal::entityTypeManager()->getStorage('geolayer')->load($added_layer['target_id']);
        if (empty($geolayer)) {
          return;
        }

        $layer_type = Term::load($geolayer->layer_type->target_id);
        if (!$layer_type->hasField('forest_reserve_computed_field')) {
          return;
        }

        // Get the computed fields that can be generated from the layer type.
        $associated_computed_fields = $layer_type->forest_reserve_computed_field->getValue();
        foreach ($associated_computed_fields as $key => $value) {
          $associated_computed_field = $value['value'];

          // If the geolayer type matches the dimension we want to calculate go
          // ahead and extract the geolayer's geometry and do the calculation.
          if ($associated_computed_field == $this->getName()) {
            $geofield = $geolayer->geofield->getValue();
            if (empty($geofield[0]['value'])) {
              return;
            }

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
              // @todo area is not in hectares. Figure out how to generate metric
              // units. See https://www.drupal.org/project/farm/issues/3425516
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

}
