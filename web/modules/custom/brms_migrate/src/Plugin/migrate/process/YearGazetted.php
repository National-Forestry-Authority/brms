<?php

namespace Drupal\brms_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "brms_year_gazetted"
 * )
 */
class YearGazetted extends ProcessPluginBase {

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

    foreach ($gazettes as $gazette) {
      if ($row->getSourceProperty($gazette) == 'Yes') {
        // Search the gazettes in chronological order and stop at the earliest
        // gazette date.
        return substr($gazette, 0, 4);
      }
    }
    return $value;
  }

}
