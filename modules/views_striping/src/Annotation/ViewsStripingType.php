<?php

namespace Drupal\views_striping\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the Views Striping Type plugin annotation object.
 *
 * Plugin namespace: ViewsStripingType.
 *
 * @Annotation
 */
class ViewsStripingType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id = '';

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label = '';

  /**
   * A longer description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description = '';

}
