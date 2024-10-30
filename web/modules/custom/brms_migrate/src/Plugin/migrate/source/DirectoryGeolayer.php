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
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
    $instance->entityTypeManager = $container->get('entity_type.manager');
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
      case 'brms_migrate_forest_reserve_geolayers':
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

      case 'brms_migrate_forest_reserve_geolayer_ref':
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

        // Set the Legal SI area source property.
        if (preg_match('/<SimpleData name="areaha">([^<]+)<\/SimpleData>/', $content, $matches)) {
          $area = $matches[1];
          $row->setSourceProperty('legal_si_area', $area);
        }
        else {
          // If the area is not found in the KML file, check if the node
          // already has a value for legal SI area field, and use that.
          $node = $this->entityTypeManager->getStorage('node')->load($nid);
          if ($node && $node->hasField('legal_si_area') && !$node->get('legal_si_area')->isEmpty()) {
            $row->setSourceProperty('legal_si_area', $node->get('legal_si_area')->value);
          }
        }

        break;

      case 'brms_migrate_utm10000_geolayers':
        $parts = explode('_', $parts[0]);
        $map_sheet = $parts[2] . '/' . $parts[3] . '/' . $parts[4];
        $name = str_replace('-', ' ', $parts[1]) . ' - UTM 10000 - map sheet ' . $map_sheet;
        $row->setSourceProperty('label', $name);
        $row->setSourceProperty('description', 'Map sheet: ' . $map_sheet);
        // Set the unique source id to the map sheet number.
        $row->setSourceProperty('sourceID', $map_sheet);
        break;

      case 'brms_migrate_utm10000':
        $parts = explode('_', $parts[0]);
        $name = str_replace('-', ' ', $parts[1]);
        $map_sheet = $parts[2] . '/' . $parts[3] . '/' . $parts[4];
        // Set the migration id so that we can look up the
        // brms_migrate_utm50000_geolayers migration to retrieve the geolayer id
        // that will be assigned to the base layer node.
        $row->setSourceProperty('migration_id', $map_sheet);
        // There is a UTM 10000 base layer node for each CFR. Construct the node
        // title to be search for in the migration.
        $row->setSourceProperty('title', 'UTM 10000 base layer - ' . $name);
        break;

      case 'brms_migrate_utm50000_geolayers':
        $name = str_replace('-', ' ', explode('_', $parts[0])[2]) . ' - UTM 50000';
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
        $row->setSourceProperty('label', $name . ' - Sector');
        $row->setSourceProperty('sourceID', $name);
        break;

      case 'brms_migrate_sector':
        $name = str_replace('-', ' ', explode('_', $parts[0])[1]);
        // Set the migration id so that we can look up the
        // brms_migrate_sector_geolayers migration to retrieve the geolayer id
        // that will be assigned to the base layer node.
        $row->setSourceProperty('migration_id', $name);
        break;

      case 'brms_migrate_district2022_geolayers':
        $name = str_replace('-', ' ', explode('_', $parts[0])[2]) . ' - District 2022';
        $row->setSourceProperty('label', $name);
        // Set the unique source id to the map sheet number.
        $row->setSourceProperty('sourceID', $parts[0]);
        break;

      case 'brms_migrate_district1997_geolayers':
        $name = str_replace('-', ' ', explode('_', $parts[0])[1]) . ' - District 1997';
        $row->setSourceProperty('label', $name);
        // Set the unique source id to the map sheet number.
        $row->setSourceProperty('sourceID', $parts[0]);
        break;

      case 'brms_migrate_district2022':
      case 'brms_migrate_district1997':
        // Set the migration id so that we can look up the
        // brms_migrate_sector_geolayers migration to retrieve the geolayer id
        // that will be assigned to the base layer node.
        $row->setSourceProperty('migration_id', $parts[0]);
        break;
    }

  }

}
