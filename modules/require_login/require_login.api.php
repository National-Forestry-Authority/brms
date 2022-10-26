<?php

/**
 * @file
 * Hooks for the require_login module.
 */

/**
 * Alter default authentication checks.
 *
 * @param array &$default_checks
 *   An array of boolean values indicating authentication status.
 */
function hook_require_login_authcheck_alter(array &$default_checks) {
  $var1 = $var2 = 'some-value';

  // If $var1 equals $var2 then allow unauthenticated access.
  $default_checks[] = ($var1 == $var2);
}
