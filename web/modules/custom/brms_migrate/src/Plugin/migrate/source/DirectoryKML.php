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
 *   id = "directory_kml",
 *   source_module = "brms_migrate",
 * )
 */
class DirectoryKML extends Directory implements ContainerFactoryPluginInterface {
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

    // Set the source id to filename without the KML suffix.
    $filename = $row->getSourceProperty('source_file_basename');
    $source_id = substr($filename, 0, strrpos($filename, '.'));
    $row->setSourceProperty('sourceID', $source_id);
  }

}
