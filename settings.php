<?php

if (!defined('APP_ROOT')) {
  define('APP_ROOT', __DIR__);
}

// Read the current database file from /var/current_year.txt
$db = null;
$currentYearFile = APP_ROOT . '/var/current_year.txt';
if (file_exists($currentYearFile)) {
    $year = file_get_contents($currentYearFile);
    $db = "/var/db_$year.sqlite";
}
// Pick the first sqlite file in the var directory as default database
if (!$db) {
    $files = scandir(APP_ROOT . '/var');
    foreach ($files as $file) {
        if (preg_match('/db_(\d{4})\.sqlite/', $file,
            $matches)) {
            $db = "/var/$file";
            break;
        }
    }
}

return [
  'settings' => [
    'slim' => [
      'displayErrorDetails' => true,
      'logErrors' => true,
      'logErrorDetails' => true,
    ],
    'doctrine' => [
      'dev_mode' => true,
      'cache_dir' => APP_ROOT . '/var/doctrine',
      'metadata_dirs' => [APP_ROOT . '/src/Domain'],
      'connection' => [
        'driver' => 'pdo_sqlite',
        'path' => APP_ROOT . $db,
      ],
    ]
  ]
];