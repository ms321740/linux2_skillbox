<?php
 
// phpcs:ignoreFile
 
$databases['default']['default'] = array (
  'database' => '%example_db_name%',
  'username' => '%example_db_user_name%',
  'password' => '%example_db_password%',
  'host' => 'localhost',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => 'drupal_',
  'collation' => 'utf8mb4_general_ci',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
);
