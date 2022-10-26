<?php

namespace Drupal\autotitle;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AutotitleEntityFormManager.
 *
 * @package Drupal\autotitle
 */
class AutotitleEntityFormManager {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AutotitleEntityFormManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Load entity form display configuration.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Bundle of the entity.
   * @param string $mode
   *   Form mode.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   EntityFormDisplay or null
   */
  public function getEntityFormConfiguration($entity_type, $bundle, $mode = 'default') {
    $config_keys = [$entity_type, $bundle, $mode];
    $config_id = implode('.', $config_keys);
    try {
      return $this->entityTypeManager->getStorage('entity_form_display')->load($config_id);
    }
    catch (PluginException $e) {
      return NULL;
    }
  }

  /**
   * Hide given field on entity form.
   *
   * @param \Drupal\Core\Entity\Display\EntityFormDisplayInterface $entity_form
   *   Entity form configuration object.
   * @param string $field_name
   *   Name of the field to hide.
   */
  public function hideField(EntityFormDisplayInterface $entity_form, $field_name) {
    try {
      $content = $entity_form->get('content');
      unset($content[$field_name]);

      $hidden = $entity_form->get('hidden');
      $hidden[$field_name] = TRUE;

      $entity_form->set('content', $content);
      $entity_form->set('hidden', $hidden);

      $entity_form->save();
    }
    catch (EntityStorageException $e) {

    }
  }

}
