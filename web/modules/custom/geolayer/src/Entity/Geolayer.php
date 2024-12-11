<?php

namespace Drupal\geolayer\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\geolayer\GeolayerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the geolayer entity class.
 *
 * @ContentEntityType(
 *   id = "geolayer",
 *   label = @Translation("Geolayer"),
 *   label_collection = @Translation("Geolayers"),
 *   label_singular = @Translation("geolayer"),
 *   label_plural = @Translation("geolayers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count geolayers",
 *     plural = "@count geolayers",
 *   ),
 *   bundle_label = @Translation("Geolayer type"),
 *   handlers = {
 *     "list_builder" = "Drupal\geolayer\GeolayerListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\geolayer\GeolayerAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\geolayer\Form\GeolayerForm",
 *       "edit" = "Drupal\geolayer\Form\GeolayerForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "geolayer",
 *   data_table = "geolayer_field_data",
 *   revision_table = "geolayer_revision",
 *   revision_data_table = "geolayer_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer geolayer types",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "langcode" = "langcode",
 *     "bundle" = "bundle",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/content/geolayer",
 *     "add-form" = "/geolayer/add/{geolayer_type}",
 *     "add-page" = "/geolayer/add",
 *     "canonical" = "/geolayer/{geolayer}",
 *     "edit-form" = "/geolayer/{geolayer}/edit",
 *     "delete-form" = "/geolayer/{geolayer}/delete",
 *   },
 *   bundle_entity_type = "geolayer_type",
 *   field_ui_base_route = "entity.geolayer_type.edit_form",
 * )
 */
class Geolayer extends RevisionableContentEntityBase implements GeolayerInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    // Load all nodes that reference this geolayer entity.
    $nids = \Drupal::entityQuery('node')
      ->condition('geolayers', $this->id())
      ->accessCheck(FALSE)
      ->execute();

    if (!empty($nids)) {
      $storage_handler = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $storage_handler->loadMultiple($nids);

      foreach ($nodes as $node) {
        // Remove the reference to the deleted geolayer entity.
        $geolayers = $node->get('geolayers')->getValue();
        foreach ($geolayers as $key => $geolayer) {
          if ($geolayer['target_id'] == $this->id()) {
            $node->get('geolayers')->removeItem($key);
          }
        }
        // Save the node.
        $node->save();
      }
    }

    // Call the parent delete method.
    parent::delete();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['geofield'] = BaseFieldDefinition::create('geofield')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Geometry'))
      ->setDescription(t('Add geometry data to be displayed on a map layer.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'geofield_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'geofield_default',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['kml_file'] = BaseFieldDefinition::create('file')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('KML file'))
      ->setSettings([
        'uri_scheme' => 'public',
        'file_directory' => 'kml_upload',
        'file_extensions' => 'kml',
      ])
      ->setDescription(t('The uploaded KML will be used as the source for the Geofield field and <strong>will overwrite any existing Geometry data.</strong>'))
      ->setDisplayOptions('form', [
        'type' => 'file_generic',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'file',
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setRevisionable(TRUE)
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the geolayer was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the geolayer was last edited.'));

    return $fields;
  }

}
