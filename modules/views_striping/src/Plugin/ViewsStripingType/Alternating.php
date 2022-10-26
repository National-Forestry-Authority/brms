<?php

namespace Drupal\views_striping\Plugin\ViewsStripingType;

use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;

/**
 * Views striping plugin that alternates odd/even rows.
 *
 * @ViewsStripingType(
 *   id = "alternating",
 *   label = @Translation("Alternating"),
 *   description = @Translation("Switches the striping every row"),
 * )
 */
class Alternating extends ViewsStripingTypeBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessViewRows(DisplayExtenderPluginBase $extender, &$rows) {
    foreach ($rows as $index => &$row) {
      $row['attributes']->addClass($index % 2 ? 'even' : 'odd');
    }
  }

}
