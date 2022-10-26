<?php

namespace Drupal\require_login\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Require Login settings for this site.
 */
class RequireLoginSettingsForm extends ConfigFormBase {

  /**
   * The routing provider.
   *
   * @var \Drupal\Core\Routing\RouteProvider
   */
  protected $routeProvider;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, RouteProvider $route_provider) {
    parent::__construct($config_factory);
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'require_login_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['require_login.config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('require_login.config');

    // Basic settings.
    $form['auth_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Login path'),
      '#description' => $this->t('Path to the user login page. Default: /user/login'),
      '#default_value' => $config->get('auth_path') ? $config->get('auth_path') : '/user/login',
    ];
    $form['destination_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Login destination'),
      '#description' => $this->t('Predetermined post-login destination path. Leave blank for the default behavior.'),
      '#default_value' => $config->get('destination_path'),
    ];
    $form['deny_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Access denied message'),
      '#description' => $this->t('Show message to user attempting to access a restricted page. Leave blank to disable.'),
      '#default_value' => $config->get('deny_message'),
    ];
    $items = [
      '#theme' => 'item_list',
      '#prefix' => $this->t('Exclude authentication checks on specific paths. <strong>Limit one path per line.</strong>'),
      '#items' => [
        $this->t('Use &lt;front&gt; to exclude the front page.'),
        $this->t('Use internal paths to exclude site pages. <em>I.E. /about/contact</em>'),
        $this->t('Use URL parameters to further refine path matches. <em>I.E. /blog?year=current</em>'),
        $this->t('Use wildcards (*) to match any part of a path. <em>I.E. /shop/*/orders</em>'),
      ],
    ];
    $form['excluded_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Excluded paths'),
      '#description' => render($items),
      '#default_value' => $config->get('excluded_paths'),
    ];
    $node_types = $config->get('excluded_node_types');
    $form['excluded_node_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Excluded content types'),
      '#description' => $this->t('Exclude authentication checks on all pages of a specific content type.'),
      '#options' => node_type_get_names(),
      '#multiple' => TRUE,
      '#default_value' => $node_types ? $node_types : [],
    ];

    // Additional settings.
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced settings'),
    ];
    $form['advanced']['excluded_403'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Exclude 403 (access denied) page'),
      '#description' => $this->t('Allow unauthenticated access to the 403 (access denied) page.'),
      '#default_value' => $config->get('excluded_403'),
    ];
    $form['advanced']['excluded_404'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Exclude 404 (not found) page'),
      '#description' => $this->t('Allow unauthenticated access to the 404 (not found) page.'),
      '#default_value' => $config->get('excluded_404'),
    ];
    $form['advanced']['excluded_routes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Excluded route names'),
      '#description' => $this->t('Exclude authentication checks on specific route names. <strong>Limit one path per line.</strong>'),
      '#default_value' => $config->get('excluded_routes'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Process user inputted path.
   *
   * @var string &$path
   *   The path input.
   * @var string $config_key
   *   The configuration key.
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  private function processPathInput(&$path, $config_key, FormStateInterface $form_state) {
    $url_parts = parse_url(trim($path));
    // Verify path is valid.
    if (!UrlHelper::isValid($url_parts['path'])) {
      $form_state->setErrorByName($config_key, $this->t('Invalid path: %s', ['%s' => $path]));
      return;
    }
    // Use parsed path in case user inputted schema and/or host.
    $path = $url_parts['path'];
    if (!empty($url_parts['query'])) {
      $path .= '?' . $url_parts['query'];
    }
    // Prepend leading forward slash.
    if (substr($path, 0, 1) !== '/') {
      $path = '/' . $path;
    }
  }

  /**
   * Array filter callback to remove empty elements.
   *
   * @var string $element
   *   The array element.
   *
   * @return bool
   *   TRUE if element should be removed. Otherwise FALSE.
   */
  protected function arrayFilterEmpty($element) {
    return !empty(trim($element));
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate login and destination paths.
    $config_keys = [
      'require_login_auth_path',
      'require_login_destination_path',
    ];
    foreach ($config_keys as $config_key) {
      if ($path = $form_state->getValue($config_key)) {
        $this->processPathInput($path, $config_key, $form_state);
        $form_state->setValue($config_key, $path);
      }
    }

    // Validate excluded paths.
    $excluded_paths = preg_split('/\r\n|\r|\n/', $form_state->getValue('require_login_excluded_paths'));
    $excluded_paths = array_filter($excluded_paths, [
      $this,
      'arrayFilterEmpty',
    ]);
    foreach ($excluded_paths as $key => $path) {
      if (!empty($path) && $path !== '<front>') {
        $this->processPathInput($path, 'require_login_excluded_paths', $form_state);
        $excluded_paths[$key] = $path;
      }
    }
    $form_state->setValue('require_login_excluded_paths', implode(PHP_EOL, $excluded_paths));

    // Validate excluded route names.
    $excluded_routes = preg_split('/\r\n|\r|\n/', $form_state->getValue('require_login_excluded_routes'));
    $excluded_routes = array_filter($excluded_routes, [
      $this,
      'arrayFilterEmpty',
    ]);
    if (!empty($excluded_routes)) {
      $valid_route_names = $this->routeProvider->getRoutesByNames($excluded_routes);
      if ($invalid_route_names = array_diff($excluded_routes, array_keys($valid_route_names))) {
        $items = [
          '#theme' => 'item_list',
          '#prefix' => $this->t('Missing route names detected. You may remove them if the related modules will not be installed.'),
          '#items' => $invalid_route_names,
        ];
        $this->messenger()->addWarning(render($items));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('require_login.config')
      ->set('auth_path', $form_state->getValue('auth_path'))
      ->set('destination_path', $form_state->getValue('destination_path'))
      ->set('deny_message', $form_state->getValue('deny_message'))
      ->set('excluded_403', $form_state->getValue('excluded_403'))
      ->set('excluded_404', $form_state->getValue('excluded_404'))
      ->set('excluded_paths', $form_state->getValue('excluded_paths'))
      ->set('excluded_node_types', $form_state->getValue('excluded_node_types'))
      ->set('excluded_routes', $form_state->getValue('excluded_routes'))
      ->save();

    // Flush caches so changes take immediate effect.
    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
