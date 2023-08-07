<?php

namespace Drupal\geolayer_map\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
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
    $item = $items[$delta];
    $current_value = $item->value ?? NULL;

    // Set the Geolayer style.
    $style = [];
    if ($item->getParent() && $item->getParent()->getParent() && $item->getParent()->getParent() instanceof EntityAdapter) {
      $geolayer = $item->getParent()->getParent()->getEntity();
      if ($geolayer->hasField('layer_type')) {
        $style = [
          'color' => $geolayer->layer_type?->entity?->color?->color,
          'line_style' => $geolayer->layer_type?->entity?->line_style?->value,
          'line_width' => $geolayer->layer_type?->entity?->line_width?->value,
        ];
      }
    }

    // Define the map render array.
    $element['map'] = [
      '#type' => 'geolayer_map',
      '#map_type' => 'geofield',
      '#map_settings' => [
        'wkt' => $current_value,
        'layer_style' => $style,
        'behaviors' => [
          'wkt' => [
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
