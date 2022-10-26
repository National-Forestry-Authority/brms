<?php

namespace Drupal\views_striping\Plugin\ViewsStripingType;

use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Views striping plugin that changes the stripe when a field value changes.
 *
 * @ViewsStripingType(
 *   id = "field_value",
 *   label = @Translation("Field value"),
 *   description = @Translation("Switches the striping each time the value of a particular field changes"),
 * )
 */
class FieldValue extends ViewsStripingTypeBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    return [
      'striping_field' => ['default' => ''],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(DisplayExtenderPluginBase $extender) {
    $fields = $extender->view->getHandlers('field');

    $options = [];
    foreach ($fields as $id => $field) {
      $options[$id] = $field['label'] ?: $field['id'];
    }

    $form['striping_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Striping field'),
      '#description' => $this->t('The field whose value is to be used for striping the view. Adjacent rows with the same value in this field get the same striping.'),
      '#options' => $options,
      '#empty_value' => '',
      '#default_value' => $extender->options['striping_field'],
      '#states' => [
        'required' => [
          ':input[name="striping_type"]' => ['value' => $this->getPluginId()],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessViewRows(DisplayExtenderPluginBase $extender, &$rows) {
    $field_name = $extender->options['striping_field'];

    $alternator = [
      'even' => 'odd',
      'odd'  => 'even',
    ];

    $last_value = '';
    $current_striping = 'odd';
    foreach ($rows as $index => &$row) {
      $row_value = $row['columns'][$field_name]['content'];

      // Compare the whole render array of the field. This is to account for
      // formatting options that may cause two different values to output the
      // same, for example, date formatting.
      if ($row_value == $last_value || empty($last_value)) {
        // Continue to apply the same CSS class.
        $row['attributes']->addClass($current_striping);
      }
      else {
        // Value has changed: flip the CSS class.
        $current_striping = $alternator[$current_striping];

        $row['attributes']->addClass($current_striping);
      }

      $last_value = $row_value;
    }
  }

}
