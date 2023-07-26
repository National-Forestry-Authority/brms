<?php

namespace Drupal\brms_migrate\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "brms_forest_reserve_geolayer"
 * )
 */
class ForestReserveGeolayer extends ProcessPluginBase implements ContainerFactoryPluginInterface {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Forest reserves have multiple geolayers, so we first load all existing
    // geolayers and then append the new one, removing duplicates if it was
    // already added.
    $nid = $row->getSourceProperty('nid');
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if ($geolayers = $node->geolayers->getValue()) {
      $geolayers[] = ['target_id' => $value];
      // Remove duplicates.
      $geolayers = array_unique($geolayers, SORT_REGULAR);
    }
    else {
      $geolayers = [0 => ['target_id' => $value]];
    }

    // The source value is the geolayer id. The source row also contains title,
    // description and layer type values. Update the geolayer entity with these
    // values.
    $type = $row->getSourceProperty('type');
    $title = $row->getSourceProperty('title');
    if (empty($title)) {
      $title = $node->getTitle() . ': ' . $type;
    }
    $description = $row->getSourceProperty('description');

    /** @var \Drupal\geolayer\GeolayerInterface $geolayer */
    $geolayer = $this->entityTypeManager->getStorage('geolayer')->load($value);
    $geolayer->set('label', $title);
    $geolayer->set('description', $description);

    // Load the layer type taxonomy term.
    /** @var \Drupal\taxonomy\Entity\term $layer_type */
    $layer_type = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadByProperties(['name' => $type, 'vid' => 'layer_type']);
    if (!empty($layer_type)) {
      $geolayer->set('layer_type', reset($layer_type)->id());
    }
    else {
      throw new MigrateException("Invalid layer type $type");
    }
    $geolayer->save();
    return $geolayers;
  }

}
