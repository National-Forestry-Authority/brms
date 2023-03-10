<?php

/**
 * @file
 * Deploy functions run after drush config:import
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

  foreach($terms as $term) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create($term);
    $term->save();
  }
}
