<?php

namespace Drupal\geolayer_map\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geofield\Plugin\Field\FieldWidget\GeofieldBaseWidget;

/**
 * Plugin implementation of the map 'geofield' widget.
 *
 * @FieldWidget(
 *   id = "geolayer_map_geofield",
 *   label = @Translation("Geolayer Map"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class GeofieldWidget extends GeofieldBaseWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Wrap the map in a collapsible details element.
    $element['#type'] = 'details';
    $element['#title'] = $this->t('Geometry');
    $element['#open'] = TRUE;

    // Get the current value.
    $current_value = isset($items[$delta]->value) ? $items[$delta]->value : NULL;
    // Define the map render array.
    $element['map'] = [
      '#type' => 'geolayer_map',
      '#map_type' => 'geofield_widget',
      '#map_settings' => [
        'wkt' => $current_value,
        'geolayers' => 'geolayer_map_geofield_widget',
        'behaviors' => [
          'wkt' => [
            'edit' => FALSE,
            'zoom' => TRUE,
          ],
        ],
      ],
    ];

    // Add a textarea for the WKT value.
    $element['value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Geometry'),
      '#default_value' => $current_value,
    ];

    return $element;
  }

}
