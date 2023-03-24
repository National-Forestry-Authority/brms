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
    // Get the parent entity of the field item list.
    $entity = $items->getEntity();
    $entity_type_manager = \Drupal::service('entity_type.manager');  
    // Get the entity type definition.
    $entity_type_definition = $entity_type_manager->getDefinition('node');
    $entity_type_label =  $entity->getEntityType()->getLabel();  
    // Get the value of the entity reference field. 
    $geolayers = [];
    $referenced_entities = $entity->get('geolayers')->referencedEntities();
     // Loop through the referenced entities and do something with them.
    foreach ($referenced_entities as $referenced_entity) {
      $geolayers[] = $referenced_entity->id();
    }
    // render a map
    $element[0] = [
      '#type' => 'geolayer_map',
      '#map_type' => 'geofield',
      '#map_settings' => [
        'geolayers' => $geolayers,
        'map_type' => 'geolayers',
        'behaviors' => [
          'wkt' => [
            'zoom' => TRUE,
          ],
        ],
      ],
      '#attached' => [
        'library' => [
          'geolayer_map/behavior_wkt',
        ],
      ],
    ];
    return $element;
  }
}
