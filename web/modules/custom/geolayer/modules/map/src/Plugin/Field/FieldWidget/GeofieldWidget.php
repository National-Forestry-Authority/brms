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
 *   label = @Translation("Geofield Map"),
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

    $element['#description'] = $element['#description'] . '<br />' . $this->t('Geometry Validation enabled (valid WKT, KML or Geojson format & values required)');
    $element['#element_validate'] = [[get_class($this), 'validateGeofieldGeometryText']];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Always store data as WKT.
    foreach ($values as $delta => $value) {
      $values[$delta]['value'] = $this->geofieldBackendValue($value['value']);
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateGeofieldGeometryText(array $element, FormStateInterface &$form_state) {
    if (!empty($element['#value']) && is_null(\Drupal::service('geofield.geophp')->load($element['#value']))) {
      $form_state->setError($element, t('The @value is not a valid geospatial content.', [
        '@value' => $element['#value'],
      ]));
    }
  }
}
