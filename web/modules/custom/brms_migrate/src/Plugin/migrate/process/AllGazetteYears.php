<?php

namespace Drupal\brms_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "brms_all_gazette_years"
 * )
 */
class AllGazetteYears extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $gazettes = [
      '1932_gazette',
      '1948_gazette',
      '1968_gazette',
      '1998_gazette',
    ];

    $year_gazetted = NULL;
    foreach ($gazettes as $gazette) {
      if ($row->getSourceProperty($gazette) == 'Yes') {
        $value[] = substr($gazette, 0, 4);
      }
    }
    return $value;
  }

}
