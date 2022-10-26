<?php

namespace Drupal\views_striping\Plugin\ViewsStripingType;

use Drupal\Component\Plugin\PluginBase;
use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;

/**
 * Base class for Views Striping Type plugins.
 */
abstract class ViewsStripingTypeBase extends PluginBase implements ViewsStripingTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(DisplayExtenderPluginBase $extender) {
    return [];
  }

}
