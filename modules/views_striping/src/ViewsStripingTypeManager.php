<?php

namespace Drupal\views_striping;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\views_striping\Annotation\ViewsStripingType;
use Drupal\views_striping\Plugin\ViewsStripingType\ViewsStripingTypeInterface;

/**
 * Manages discovery and instantiation of Views Striping Type plugins.
 */
class ViewsStripingTypeManager extends DefaultPluginManager {

  /**
   * Constructs a new ViewsStripingTypeManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/ViewsStripingType',
      $namespaces,
      $module_handler,
      ViewsStripingTypeInterface::class,
      ViewsStripingType::class
    );

    $this->alterInfo('views_striping_type_info');
    $this->setCacheBackend($cache_backend, 'views_striping_type_plugins');
  }

}
