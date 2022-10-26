<?php

namespace Drupal\views_striping\Plugin\ViewsStripingType;

use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;

/**
 * Interface for Views Striping Type plugins.
 */
interface ViewsStripingTypeInterface {

  /**
   * Defines options for this plugin.
   *
   * @return array
   *   An array of options, in the same format as this method on Views plugins.
   */
  public function defineOptions();

  /**
   * Returns the options form for this plugin.
   *
   * @param \Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase $extender
   *  The display extender plugin
   *
   * @return array
   *   The form array.
   */
  public function buildOptionsForm(DisplayExtenderPluginBase $extender);

  /**
   * Alters the view rows to add the striping CSS classes.
   *
   * This is called from our display extender.
   *
   * @param \Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase $extender
   *    The display extender plugin.
   * @param array $rows
   *    The view rows from the preprocessor variables.
   */
  public function preprocessViewRows(DisplayExtenderPluginBase $extender, &$rows);

}
