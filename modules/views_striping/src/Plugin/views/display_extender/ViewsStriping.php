<?php

namespace Drupal\views_striping\Plugin\views\display_extender;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;
use Drupal\views_striping\ViewsStripingTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Display extender to add striping to views.
 *
 * This hands over to a striping type plugin.
 *
 * @ViewsDisplayExtender(
 *   id = "views_striping",
 *   title = @Translation("Row striping"),
 * )
 */
class ViewsStriping extends DisplayExtenderPluginBase {

  /**
   * The Views striping type manager.
   *
   * @var \Drupal\views_striping\ViewsStripingTypeManager
   */
  protected $viewsStripingTypeManager;

  /**
   * The style plugins that are supported.
   *
   * @var array
   */
  protected $supportedStylePlugins = [
    'table',
    'views_aggregator_plugin_style_table',
  ];

  /**
   * Creates a ViewsStriping instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\views_striping\ViewsStripingTypeManager $views_striping_type_manager
   *   The Views striping type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ViewsStripingTypeManager $views_striping_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewsStripingTypeManager = $views_striping_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.views_striping_type')
    );
  }

   /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['striping_type'] = ['default' => ''];

    // Add options from all the striping type plugins.
    foreach ($this->viewsStripingTypeManager->getDefinitions() as $id => $definition) {
      $striping_type_plugin = $this->viewsStripingTypeManager->createInstance($id);
      $options += $striping_type_plugin->defineOptions();
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    if ($form_state->get('section') != 'style_options') {
      return;
    }
    if (!$this->view->getStyle()->usesRowClass()) {
      return;
    }
    if (!in_array($this->view->getStyle()->getPluginId(), $this->supportedStylePlugins)) {
      return;
    }

    $options = [];

    foreach ($this->viewsStripingTypeManager->getDefinitions() as $id => $definition) {
      $options[$id] = $definition['label'];
    }
    natcasesort($options);

    // Would be nice if core did this the same way it does for select elements.
    // See https://www.drupal.org/project/drupal/issues/3194345.
    $options = array_merge(['' => $this->t('None')], $options);

    $form['striping_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Striping type'),
      '#description' => $this->t("The type of striping to use on this view's rows."),
      '#options' => $options,
      '#required' => TRUE,
      '#empty_value' => '',
      '#default_value' => $this->options['striping_type'],
    ];

    // Add form elements from all the striping type plugins, and use form states
    // to only show the one for the selected striping type.
    foreach ($this->viewsStripingTypeManager->getDefinitions() as $id => $definition) {
      // Add the description to each radio button.
      $form['striping_type'][$id]['#description'] = $definition['description'];

      $striping_type_plugin = $this->viewsStripingTypeManager->createInstance($id);
      if ($striping_type_plugin_form = $striping_type_plugin->buildOptionsForm($this)) {
        $form['striping_plugin_types'][$id] = [
          '#type' => 'container',
          '#states' => [
            'visible' => [
              ':input[name="striping_type"]' => ['value' => $id],
            ],
          ],
        ] + $striping_type_plugin_form;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    parent::submitOptionsForm($form, $form_state);

    if ($form_state->get('section') == 'style_options') {
      // Save options for this plugin and the striping type plugin too.
      foreach ($this->defineOptions() as $option_name => $def) {
        $this->options[$option_name] = $form_state->getValue($option_name);
      }
    }
  }

  /**
   * Adds the styles to the view rows.
   *
   * This should be called from a template preprocessor for the view style.
   *
   * @param array &$variables
   *   The variables from the preprocessor hook implementation.
   */
  public function preprocessViewStyle(&$variables) {
    // Hand over to the striping type plugin to add the striping classes to
    // the rendered rows.
    $striping_type_plugin = $this->viewsStripingTypeManager->createInstance($this->options['striping_type']);
    $striping_type_plugin->preprocessViewRows($this, $variables['rows']);
  }

}
