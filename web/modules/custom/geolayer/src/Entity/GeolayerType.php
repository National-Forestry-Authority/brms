<?php

namespace Drupal\geolayer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Geolayer type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "geolayer_type",
 *   label = @Translation("Geolayer type"),
 *   label_collection = @Translation("Geolayer types"),
 *   label_singular = @Translation("geolayer type"),
 *   label_plural = @Translation("geolayers types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count geolayers type",
 *     plural = "@count geolayers types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\geolayer\Form\GeolayerTypeForm",
 *       "edit" = "Drupal\geolayer\Form\GeolayerTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\geolayer\GeolayerTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   admin_permission = "administer geolayer types",
 *   bundle_of = "geolayer",
 *   config_prefix = "geolayer_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/geolayer_types/add",
 *     "edit-form" = "/admin/structure/geolayer_types/manage/{geolayer_type}",
 *     "delete-form" = "/admin/structure/geolayer_types/manage/{geolayer_type}/delete",
 *     "collection" = "/admin/structure/geolayer_types"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   }
 * )
 */
class GeolayerType extends ConfigEntityBundleBase {

  /**
   * The machine name of this geolayer type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the geolayer type.
   *
   * @var string
   */
  protected $label;

}
