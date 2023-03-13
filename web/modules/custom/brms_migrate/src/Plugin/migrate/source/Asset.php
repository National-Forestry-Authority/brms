<?php

namespace Drupal\brms_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Asset source from database.
 *
 * @MigrateSource(
 *   id = "farmos_asset",
 *   source_module = "brms_migrate"
 * )
 */
class Asset extends SqlBase  {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('asset_field_data', 'a')
      ->fields('a')
      ->condition('a.status', 'active')
      ->orderBy('id');

    if (isset($this->configuration['bundle'])) {
      $query->condition('a.type', (array) $this->configuration['bundle'], 'IN');
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => $this->t('The asset ID'),
      'name' => $this->t('The asset name'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['id']['type'] = 'integer';
    return $ids;
  }

}
