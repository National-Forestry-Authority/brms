<?php

namespace Drupal\geolayer_map\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
/**
 * Plugin implementation of the map 'geolayers' formatter.
 *
 * @FieldFormatter(
 *   id = "geolayer_map_geolayers",
 *   label = @Translation("Geolayer Map"),
 *   field_types = {
 *     "entity_reference",
 *   }
 * )
 */
class GeolayerFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    // Get the parent node of the field item list of geolayers.
    $entity = $items->getEntity();
    // Get the value of the geolayer entity reference field.
    $geolayers = [];
    $referenced_entities = $entity->get('geolayers')->referencedEntities();
    // Loop through the referenced geolayers and store their ids.
    foreach ($referenced_entities as $referenced_entity) {
      $geolayers[] = $referenced_entity->id();
    }

    // Render the map.
    $element[0] = [
      '#type' => 'geolayer_map',
      '#map_type' => 'geolayers',
      '#map_settings' => [
        'geolayers' => $geolayers,
      ],
    ];
    return $element;
  }

}
