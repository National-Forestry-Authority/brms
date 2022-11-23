<?php 

$databases['default']['default'] = [
  'host' => $_SERVER['DB_HOST'],
  'port' => $_SERVER['DB_PORT'],
  'database' => $_SERVER['DB_NAME'],
  'username' => $_SERVER['DB_USER'],
  'password' => $_SERVER['DB_PASS'],
  'prefix' => $_SERVER['DB_PREFIX'] ?? '' ?? NULL,
  'driver' => $_SERVER['DB_DRIVER'],
  'namespace' => sprintf('Drupal\Core\Database\Driver\%s', $_SERVER['DB_DRIVER']),
  'charset' => 'utf8mb4',
  'collation' => 'utf8mb4_general_ci',
];

$settings['trusted_host_patterns'] = [
  sprintf('^%s$', str_replace('.', '\.', $_SERVER['PROJECT_BASE_URL'])),
  sprintf('^.+\.%s$', str_replace('.', '\.', $_SERVER['PROJECT_BASE_URL'])),
];

$trusted_hosts = $_SERVER['TRUSTED_HOSTS'] ?? '' ?? NULL;
$trusted_hosts = explode(',', $trusted_hosts);
$trusted_hosts = array_filter($trusted_hosts);
foreach ($trusted_hosts as $host) {
  $settings['trusted_host_patterns'][] = sprintf('^%s$', str_replace('.', '\.', $host));
}