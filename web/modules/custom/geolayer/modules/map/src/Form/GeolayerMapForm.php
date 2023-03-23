<?php

namespace Drupal\geolayer_map\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * geolayer map form.
 *
 * @ingroup geolayer
 */
class GeolayerMapForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'geolayer_map_render';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Set the form title.
    // $form['#title'] = $this->t('Geolayer Map');
    $form['geolayer_map'] = [
      '#type' => 'geolayer_map',
      '#map_type' => 'default',      
      '#map_settings' => [
        'wkt' => 'POINT(-122.4194 37.7749)',
        'geolayers' => 'geolayer_map_default',
        'entity_type' => 'node',
        'behaviors' => [
          'wkt' => [
            'zoom' => TRUE,
          ],
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
