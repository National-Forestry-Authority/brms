<?php

namespace Drupal\geolayer_map\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geofield\Plugin\Field\FieldWidget\GeofieldBaseWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\File\FileSystem;
use Drupal\file\FileInterface;
use Drupal\geofield\GeoPHP\GeoPHPInterface;
use Drupal\geofield\Plugin\GeofieldBackendManager;
use Drupal\geofield\WktGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;

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

  // use WktTrait;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Supported GeoPHP file types.
   *
   * @var string[]
   *   GeoPHP type keyed by file extension.
   */
  public static $geoPhpTypes = [
    'geojson' => 'geojson',
    'gpx' => 'gpx',
    'kml' => 'kml',
    'kmz' => 'kml',
    'wkb' => 'wkb',
    'wkt' => 'wkt',
  ];

  /**
   * GeofieldWidget constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\geofield\GeoPHP\GeoPHPInterface $geophp_wrapper
   *   The geoPhpWrapper.
   * @param \Drupal\geofield\WktGeneratorInterface $wkt_generator
   *   The WKT format Generator service.
   * @param \Drupal\geofield\Plugin\GeofieldBackendManager $geofield_backend_manager
   *   The geofieldBackendManager.
   * @param \Drupal\Core\File\FileSystem $file_system
   *   The file system service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, GeoPHPInterface $geophp_wrapper, WktGeneratorInterface $wkt_generator, GeofieldBackendManager $geofield_backend_manager, FileSystem $file_system, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $geophp_wrapper, $wkt_generator, $geofield_backend_manager);
    $this->fileSystem = $file_system;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('geofield.geophp'),
      $container->get('geofield.wkt_generator'),
      $container->get('plugin.manager.geofield_backend'),
      $container->get('file_system'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Wrap the map in a collapsible details element.
    $element['#type'] = 'details';
    $element['#title'] = $this->t('Geometry');
    $element['#open'] = TRUE;

    // The geofield might be in an inline entity form, so we have to find the
    // field name deep down in form state values.
    $field_name = array_merge($element['#field_parents'], [$this->fieldDefinition->getName()]);

    $file_field = $element['#field_parents'];
    array_push($file_field, 'kml_file');

    // Get the current form state value. Prioritize form state over field value.
    $form_value = $form_state->getValue($field_name);
    $field_value = $items[$delta]->value;
    $current_value = $form_value['value'] ?? $field_value;

    // Set the Geolayer style.
    $item = $items[$delta];
    $style = [];
    if ($item->getParent() && $item->getParent()->getParent() && $item->getParent()->getParent() instanceof EntityAdapter) {
      $geolayer = $item->getParent()->getParent()->getEntity();
      if ($geolayer->hasField('layer_type')) {
        $style = [
          'geometry_type' => $geolayer->layer_type?->entity?->geometry_type?->value ?? 'polygon',
          'color' => $geolayer->layer_type?->entity?->color?->color ?? 'orange',
          'line_style' => $geolayer->layer_type?->entity?->line_style?->value ?? 'solid',
          'line_width' => $geolayer->layer_type?->entity?->line_width?->value ?? 2,
          'point_shape' => $geolayer->layer_type?->entity?->point_shape?->value ?? 'circle',
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
      '#default_value' => $current_value,
      '#type' => 'textarea',
      '#title' => $this->t('Geometry'),
    ];

    // Wrap the map with a unique id for populating form files.
    $field_wrapper_id = Html::getUniqueId($this->fieldDefinition->getName() . '_wrapper');
    $element['#prefix'] = '<div id="' . $field_wrapper_id . '">';
    $element['#suffix'] = '</div>';

    // The button value needs to be unique so that the correct triggering
    // element is set. In geolayer multivalue entity reference fields append the
    // parent element's delta to the button value.
    if (!empty($element['#field_parents'])) {
      if (in_array('surveys', $element['#field_parents'])) {
        // The survey paragraph has a single geolayer but there can be multiple
        // surveys, so use the delta of the survey itself as the unique id for
        // the upload button.
        $delta = $element['#field_parents'][1];
      }
      elseif (in_array('inline_entity_form', $element['#field_parents'])) {
        // The geolayer field is a multiple value entity reference field. Use
        // the geolayer delta as the unique id for the upload button.
        $delta = $element['#field_parents'][count($element['#field_parents']) - 2];
      }
      else {
        // When adding a new geolayer the delta is the last parent.
        $delta = end($element['#field_parents']);
      }
      $button_label = $this->t('Import geometry from uploaded files (@delta)', ['@delta' => $delta + 1]);
    }
    else {
      // On the geolayer edit form accesed from admin/content/geolayer we don't
      // have deltas so we can use a simple butotn label.
      $button_label = $this->t('Import geometry from uploaded files');
    }
    // Add a button to import geometry from the uploaded KML file.
    $element['trigger'] = [
      '#type' => 'submit',
      '#value' => $button_label,
      '#submit' => [[$this, 'fileParse']],
      '#ajax' => [
        'wrapper' => $field_wrapper_id,
        'callback' => [$this, 'fileCallback'],
        'message' => $this->t('Working...'),
      ],
      '#limit_validation_errors' => [$file_field],
      '#states' => [
        'disabled' => [
          ':input[name="' . 'geolayers[form][inline_entity_form][entities][0][form][kml_file]' . '[0][fids]"]' => ['empty' => TRUE],
        ],
      ],
      '#weight' => 10,
    ];

    $element['#description'] = $element['#description'] . '<br />' . $this->t('Geometry Validation enabled (valid WKT, KML or Geojson format & values required)');
    return $element;
  }

  /**
   * Submit function to parse geometries from uploaded files.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function fileParse(array &$form, FormStateInterface $form_state) {
    // Get the form field element.
    $triggering_element = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($triggering_element['#array_parents'], 0, -1));

    // The geofield might be in an inline entity form, so we have to find the
    // field name deep down in form state values.
    $field_name = array_slice($element['#parents'], 0, -1);
    $populate_file_field = array_slice($field_name, 0, -1);
    array_push($populate_file_field, 'kml_file');

    // Load the uploaded files.
    $uploaded_files = $form_state->getValue($populate_file_field);
    if (!empty($uploaded_files)) {
      // Get file IDs.
      $file_ids = array_reduce($uploaded_files, function ($carry, $file) {
        return array_merge($carry, array_values($file['fids']));
      }, []);

      // Load and process each file.
      /** @var \Drupal\file\Entity\File[] $files */
      $files = $this->entityTypeManager->getStorage('file')->loadMultiple($file_ids);

      // @todo Support geometry field with > 1 cardinality.
      $wkt_strings = [];
      if (!empty($files)) {
        foreach ($files as $file) {

          // Get the geometry type.
          $geophp_type = $this->getGeoPhpType($file);

          // Bail if the file is not a supported format.
          if ($geophp_type === FALSE) {
            $this->messenger()->addWarning(
              $this->t('%filename is not a supported geometry file format. Supported formats: %formats', [
                '%filename' => $file->getFilename(),
                '%formats' => implode(', ', array_keys(static::$geoPhpTypes)),
              ],
              ));
            return;
          }

          // Try to parse geometry using the specified geoPHP type.
          $path = $file->getFileUri();
          if ($geophp_type == 'kml' && $file->getMimeType() === 'application/vnd.google-earth.kmz' && extension_loaded('zip')) {
            $path = 'zip://' . $this->fileSystem->realpath($path) . '#doc.kml';
          }
          $data = file_get_contents($path);
          if ($geom = $this->geoPhpWrapper->load($data, $geophp_type)) {
            $wkt_strings[] = $geom->out('wkt');
          }
        }
      }

      // Merge WKT geometries into a single geometry collection.
      $wkt = '';
      if (!empty($wkt_strings)) {
        if (count($wkt_strings) > 1) {
          $wkt = $this->combineWkt($wkt_strings);
        }
        else {
          $wkt = reset($wkt_strings);
        }
      }

      // Bail if no geometry was parsed.
      if (empty($wkt)) {
        $this->messenger()->addWarning($this->t('No geometry could be parsed from files.'));
        return;
      }

      // Unset the current geometry value from the user input.
      $user_input = $form_state->getUserInput();
      NestedArray::setValue($user_input, $field_name, NULL);
      $form_state->setUserInput($user_input);

      // Set the new form value.
      $form_state->setValue($field_name, ['value' => $wkt]);

      // Rebuild the form so the map widget is rebuilt with the new value.
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * Combines an array of WKT strings into a single WKT GeometryCollection.
   */
  public function combineWkt(array $geoms) {
    // If no geometries were found, return an empty string.
    if (empty($geoms)) {
      return '';
    }
    // If there is more than one geometry, we will wrap it all in a
    // GEOMETRYCOLLECTION() at the end.
    $geometrycollection = FALSE;
    if (count($geoms) > 1) {
      $geometrycollection = TRUE;
    }
    // Build an array of WKT strings.
    $wkt_strings = [];
    foreach ($geoms as $geom) {
      // If the geometry is empty, skip it.
      if (empty($geom)) {
        continue;
      }
      // Convert to a GeoPHP geometry object.
      $geometry = \geoPHP::load($geom, 'wkt');
      // If this is a geometry collection, multi-point, multi-linestring, or
      // multi-polygon, then extract its components and add them individually to
      // the array.
      $multigeometries = [
        'GeometryCollection',
        'MultiPoint',
        'MultiLineSting',
        'MultiPolygon',
      ];
      if (in_array($geometry->geometryType(), $multigeometries)) {
        // Iterate through the geometry components and add each to the array.
        $components = $geometry->getComponents();
        foreach ($components as $component) {
          $wkt_strings[] = $component->out('wkt');
        }
        // Set $geometrycollection to TRUE in case there was only one geometry
        // in the $geoms parameter of this function, so that we know to wrap the
        // WKT in a GEOMETRYCOLLECTION() at the end.
        $geometrycollection = TRUE;
      }
      // Otherwise, add it to the array.
      else {
        $wkt_strings[] = $geometry->out('wkt');
      }
    }
    // Combine all the WKT strings together into one.
    $wkt = implode(',', $wkt_strings);
    // If the WKT is empty, return it.
    if (empty($wkt)) {
      return $wkt;
    }
    // If there is more than one geometry, wrap them all in a geometry
    // collection.
    if ($geometrycollection) {
      $wkt = 'GEOMETRYCOLLECTION (' . $wkt . ')';
    }
    // Return the combined WKT.
    return $wkt;
  }

  /**
   * {@inheritdoc}
   */
  public function fileCallback(array &$form, FormStateInterface $form_state) {
    // Return the rebuilt map form field field element.
    $triggering_element = $form_state->getTriggeringElement();
    return NestedArray::getValue($form, array_slice($triggering_element['#array_parents'], 0, -1));
  }

  /**
   * Helper function to check if the file extension is a supported geometry.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file to check.
   *
   * @return string|false
   *   The GeoPHP type or FALSE.
   */
  private function getGeoPhpType(FileInterface $file) {

    // Get the file extension.
    $matches = [];
    if (preg_match('/(?<=\.)[^.]+$/', $file->getFilename(), $matches) && isset($matches[0])) {
      // Return the associated GeoPHP type.
      if (isset(self::$geoPhpTypes[$matches[0]])) {
        return self::$geoPhpTypes[$matches[0]];
      }
    }

    // Otherwise the file extension is not a valid GeoPHP geometry type.
    return FALSE;
  }

}
