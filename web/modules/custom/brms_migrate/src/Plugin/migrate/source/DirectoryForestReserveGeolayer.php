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
 *   id = "directory_forest_reserve_geolayer",
 *   source_module = "brms_migrate",
 * )
 */
class DirectoryForestReserveGeolayer extends Directory implements ContainerFactoryPluginInterface {
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
    // The KML filename has the format NID-reservename.layertype.kml for example
    // 26138-Abera.cairn.kml. We will extract the nid and source id of the
    // brms_migrate_geolayers migration from the filename.
    $filename = $row->getSourceProperty('source_file_basename');

    $parts = explode('.', $filename);

    $nid = explode('-', $parts[0])[0];

    // Set the nid source property of the forest reserve that the geolayer will
    // be assigned to.
    $row->setSourceProperty('nid', $nid);

    // Set the unique source id to nid-layertype.
    $row->setSourceProperty('sourceID', $nid . '-' . $parts[1]);
    // Set the migration id so we can look up the brms_migrate_geolayers
    // migration to retrieve the geolayer id that will be assigned to the
    // forest reserve.
    $row->setSourceProperty('migration_id', $nid . '-' . $parts[1]);
  }

}
