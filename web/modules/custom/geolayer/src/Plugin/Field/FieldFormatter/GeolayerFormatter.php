<?php

namespace Drupal\geolayer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\geofield\GeoPHP\GeoPHPInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
/**
 * Plugin implementation of the map 'geolayers' formatter.
 *
 * @FieldFormatter(
 *   id = "geolayer_map_geolayers",
 *   label = @Translation("Geolayer Map"),
 *   field_types = {
 *     "geolayers"
 *   }
 * )
 */
class GeolayerFormatter extends FormatterBase {
  
  /**
   * {@inheritdoc}
   */
   public function viewElements(FieldItemListInterface $items, $langcode) {
      echo 'geolayer formatter called';
      $element = [];
  
      foreach ( $items as $delta => $item) {
          $element[$delta] = [
              '#markup' => $this->getSetting('concat') . $item->value,
          ];
      }
      return $element;
   }
}
