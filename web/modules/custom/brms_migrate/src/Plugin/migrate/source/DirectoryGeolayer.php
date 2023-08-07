<?php

namespace Drupal\brms_migrate\Plugin\migrate\source;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_source_directory\Plugin\migrate\source\Directory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Source for a given directory path.
 *
 * @MigrateSource(
 *   id = "directory_geolayer",
 *   source_module = "brms_migrate",
 * )
 */
class DirectoryGeolayer extends Directory implements ContainerFactoryPluginInterface {
  /**
   * The geoPhpWrapper service.
   *
   * @var \Drupal\geofield\GeoPHP\GeoPHPInterface
   */
  protected $geoPhpWrapper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
    );
    $instance->geoPhpWrapper = $container->get('geofield.geophp');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Load the KML content and convert it to WKT using Geofield's geophp
    // service.
    $content = file_get_contents($row->getSourceProperty('source_file_pathname'));
    $geometry = $this->geoPhpWrapper->load($content);

    if ($geometry instanceof \Geometry) {
      $row->setSourceProperty('kml', $geometry->out('wkt'));
    }

    // The KML filename has the format NID-reservename.layertype.kml for example
    // 26138-Abera.cairn.kml.
    $filename = $row->getSourceProperty('source_file_basename');
    $parts = explode('.', $filename);

    $nid = explode('-', $parts[0])[0];
    $reserve_name = explode('-', $parts[0])[1];
    $layer_type = str_replace('-', ' ', $parts[1]);

    // Set the geolayer label to reserve-name: layer type (hyphens replaced).
    $row->setSourceProperty('label', $reserve_name . ': ' . $layer_type);
    // Set the unique source id to nid-layertype (with hyphens).
    $row->setSourceProperty('sourceID', $nid . '-' . $parts[1]);
    // Set the layer type term source property.
    $row->setSourceProperty('layer_type', $layer_type);
  }

}
