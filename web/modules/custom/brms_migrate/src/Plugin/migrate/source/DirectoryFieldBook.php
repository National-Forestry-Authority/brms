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
 *   id = "directory_fieldbook",
 *   source_module = "brms_migrate",
 * )
 */
class DirectoryFieldBook extends Directory implements ContainerFactoryPluginInterface {

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
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $filename = $row->getSourceProperty('source_file_basename');
    // The filename has the format 123A_Title.pdf where 123 is the book number
    // and A is the subsection.
    $book_number = substr($filename, 0, strpos($filename, '_'));
    $number = (int) preg_replace('/\D/', '', $book_number);
    $subsection = preg_replace('/\d/', '', $book_number);
    $name = substr($filename, strpos($filename, '_') + 1, strpos($filename, '.pdf') - strpos($filename, '_') - 1);
    $name = trim(str_replace('.', '', $name));
    $row->setSourceProperty('title', $name ?: t('Untitled'));
    if ($number) {
      $row->setSourceProperty('field_book_number', $number);
    }
    $row->setSourceProperty('field_book_subsection', $subsection);
  }

}
