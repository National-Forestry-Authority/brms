<?php

/**
 * @file
 * Deploy functions run after drush config:import.
 */

/**
 * Initialise taxonomy terms.
 */
function brms_common_deploy_001(&$sandbox = NULL) {
  $terms = [
    [
      'name' => 'Central Forest Reserve',
      'vid' => 'forest_reserve_type',
    ],
    [
      'name' => 'Local Forest Reserve',
      'vid' => 'forest_reserve_type',
    ],
    [
      'name' => 'Private Forest',
      'vid' => 'forest_reserve_type',
    ],
    [
      'name' => 'Work required',
      'vid' => 'boundary_records_status',
    ],
    [
      'name' => 'Work in progress',
      'vid' => 'boundary_records_status',
    ],
    [
      'name' => 'Under review',
      'vid' => 'boundary_records_status',
    ],
    [
      'name' => 'Complete',
      'vid' => 'boundary_records_status',
    ],
    [
      'name' => 'Work stalled',
      'vid' => 'boundary_records_status',
    ],
    [
      'name' => 'Surveyed',
      'vid' => 'boundary_description_status',
    ],
    [
      'name' => 'Surveyed and marked',
      'vid' => 'boundary_description_status',
    ],
    [
      'name' => 'Marked but not recently checked',
      'vid' => 'boundary_description_status',
    ],
    [
      'name' => 'Urgent',
      'vid' => 'priority',
    ],
    [
      'name' => 'High',
      'vid' => 'priority',
    ],
    [
      'name' => 'Medium',
      'vid' => 'priority',
    ],
    [
      'name' => 'Low',
      'vid' => 'priority',
    ],
    [
      'name' => 'Impromptu',
      'vid' => 'priority',
    ],
  ];

  foreach ($terms as $term) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create($term);
    $term->save();
  }
}

/**
 * Create geolayer type taxonomy terms.
 */
function brms_common_deploy_002(&$sandbox = NULL) {
  $terms = [
    [
      'name' => 'Master polygon',
      'geometry_type' => 'polygon',
      'line_style' => 'solid',
      'line_width' => 2,
      'color' => '#7F3C8D',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Cutline',
      'geometry_type' => 'polygon',
      'line_style' => 'solid',
      'line_width' => 2,
      'color' => '#FF3333',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Riverline',
      'geometry_type' => 'polygon',
      'line_style' => 'dotted',
      'line_width' => 4,
      'color' => '#3969AC',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Shoreline',
      'geometry_type' => 'polygon',
      'line_style' => 'dashed',
      'line_width' => 3,
      'color' => '#E68310',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Inter protected area line',
      'geometry_type' => 'polygon',
      'line_style' => 'solid',
      'line_width' => 4,
      'color' => '#80BA5A',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Corner pillar',
      'geometry_type' => 'point',
      'point_shape' => 'square',
      'color' => '#f97b72',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Intermediate pillar',
      'geometry_type' => 'point',
      'point_shape' => 'triangle',
      'color' => '#f97b72',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'FD numbered markstone',
      'geometry_type' => 'point',
      'point_shape' => 'circle',
      'color' => '#4b4b8f',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Cairn',
      'geometry_type' => 'point',
      'point_shape' => 'star',
      'color' => '#E73F74',
      'vid' => 'layer_type',
    ],
  ];

  foreach ($terms as $term) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create($term);
    $term->save();
  }
}

/**
 * Create geolayer type taxonomy terms for surveys.
 */
function brms_common_deploy_003(&$sandbox = NULL) {
  $terms = [
    [
      'name' => 'Old survey',
      'geometry_type' => 'survey',
      'line_style' => 'solid',
      'line_width' => 2,
      'color' => '#FF3333',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Surveyed and marked',
      'geometry_type' => 'survey',
      'line_style' => 'solid',
      'line_width' => 2,
      'color' => '#11A579',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Surveyed',
      'geometry_type' => 'survey',
      'line_style' => 'dotted',
      'line_width' => 2,
      'color' => '#F2B701',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Marked',
      'geometry_type' => 'survey',
      'line_style' => 'dashed',
      'line_width' => 2,
      'color' => '#F8A519',
      'vid' => 'layer_type',
    ],
  ];

  foreach ($terms as $term) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create($term);
    $term->save();
  }
}

/**
 * Create geolayer type taxonomy terms for base layers.
 */
function brms_common_deploy_004(&$sandbox = NULL) {
  $terms = [
    [
      'name' => 'UTM 50000 base layer',
      'geometry_type' => 'polygon',
      'layer_group' => 'base',
      'line_style' => 'solid',
      'line_width' => 2,
      'color' => '#BDABB8',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'UTM 10000 base layer',
      'geometry_type' => 'polygon',
      'layer_group' => 'base',
      'line_style' => 'solid',
      'line_width' => 2,
      'color' => '#8F818B',
      'vid' => 'layer_type',
    ],
    [
      'name' => 'Sector boundary base layer',
      'geometry_type' => 'polygon',
      'layer_group' => 'base',
      'line_style' => 'solid',
      'line_width' => 2,
      'color' => '#AD9DA9',
      'vid' => 'layer_type',
    ],
  ];

  foreach ($terms as $term) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create($term);
    $term->save();
  }
}

/**
 * Create base layers nodes.
 */
function brms_common_deploy_005(&$sandbox = NULL) {
  $nodes = [
    [
      'title' => 'UTM 50000 base layer',
      'type' => 'map_base_layer',
    ],
    [
      'title' => 'UTM 10000 base layer',
      'type' => 'map_base_layer',
    ],
    [
      'title' => 'Sector boundary base layer',
      'type' => 'map_base_layer',
    ],
  ];

  foreach ($nodes as $node) {
    $node = \Drupal::entityTypeManager()->getStorage('node')->create($node);
    $node->save();
  }
}

/**
 * Update layer group of geolayer type taxonomy terms.
 */
function brms_common_deploy_006(&$sandbox = NULL) {
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
    ->loadTree('layer_type', 0, NULL, TRUE);
  foreach ($terms as $term) {
    if (empty($term->layer_group->value)) {
      if ($term->geometry_type->value == 'survey') {
        $term->layer_group->value = 'survey';
      }
      else {
        $term->layer_group->value = 'feature';
      }
      $term->save();
    }
  }
}
