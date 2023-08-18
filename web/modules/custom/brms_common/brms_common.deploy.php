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
