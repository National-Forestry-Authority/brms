<?php

/**
 * @file
 * Hooks provided by the Views striping module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Perform alterations on Views Striping Type definitions.
 *
 * @param array $info
 *   Array of information on Views Striping Type plugins.
 */
function hook_views_striping_type_info_alter(array &$info) {
  // Change the class of the 'foo' plugin.
  $info['foo']['class'] = SomeOtherClass::class;
}

/**
 * @} End of "addtogroup hooks".
 */
