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

    $filename = $row->getSourceProperty('source_file_basename');
    $parts = explode('.', $filename);

    switch ($this->migration->getPluginId()) {
      case 'brms_migrate_geolayers':
        // The KML filename has the format NID-reservename.layertype.kml e.g.
        // 26138-Abera.cairn.kml.
        $nid = explode('-', $parts[0])[0];
        $reserve_name = explode('-', $parts[0])[1];
        $layer_type = str_replace('-', ' ', $parts[1]);

        // Set the label to reserve-name: layer type (hyphens replaced).
        $row->setSourceProperty('label', $reserve_name . ': ' . $layer_type);
        // Set the unique source id to nid-layertype (with hyphens).
        $row->setSourceProperty('sourceID', $nid . '-' . $parts[1]);
        // Set the layer type term source property.
        $row->setSourceProperty('layer_type', $layer_type);
        break;

      case 'brms_migrate_forest_reserve_geolayers':
        $nid = explode('-', $parts[0])[0];
        // Set the nid source property of the forest reserve that the geolayer
        // will be assigned to.
        $row->setSourceProperty('nid', $nid);

        // Set the unique source id to nid-layertype.
        $row->setSourceProperty('sourceID', $nid . '-' . $parts[1]);
        // Set the migration id so we can look up the brms_migrate_geolayers
        // migration to retrieve the geolayer id that will be assigned to the
        // forest reserve.
        $row->setSourceProperty('migration_id', $nid . '-' . $parts[1]);
        break;

      case 'brms_migrate_utm50000_geolayers':
        $name = str_replace('-', ' ', explode('_', $parts[0])[2]);
        $map_sheet = explode('_', $parts[0])[1];
        $row->setSourceProperty('label', $name);
        $row->setSourceProperty('description', 'Map sheet: ' . $map_sheet);
        // Set the unique source id to the map sheet number.
        $row->setSourceProperty('sourceID', $map_sheet);
        break;

      case 'brms_migrate_utm50000':
        $map_sheet = explode('_', $parts[0])[1];
        // Set the migration id so that we can look up the
        // brms_migrate_utm50000_geolayers migration to retrieve the geolayer id
        // that will be assigned to the base layer node.
        $row->setSourceProperty('migration_id', $map_sheet);
        break;

      case 'brms_migrate_sector_geolayers':
        $name = str_replace('-', ' ', explode('_', $parts[0])[1]);
        $row->setSourceProperty('label', $name);
        $row->setSourceProperty('sourceID', $name);
        break;

      case 'brms_migrate_sector':
        $name = str_replace('-', ' ', explode('_', $parts[0])[1]);
        // Set the migration id so that we can look up the
        // brms_migrate_sector_geolayers migration to retrieve the geolayer id
        // that will be assigned to the base layer node.
        $row->setSourceProperty('migration_id', $name);
        break;
    }

  }

}
