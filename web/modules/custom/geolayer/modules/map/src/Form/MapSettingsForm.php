<?php

namespace Drupal\geolayer_map\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 * Provides a geolayer_map settings form.
 */
class MapSettingsForm extends ConfigFormbase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'geolayer_map_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

}
