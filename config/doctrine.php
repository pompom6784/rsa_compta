<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Doctrine ORM Configuration
|--------------------------------------------------------------------------
|
| This file configures Doctrine ORM for the application, preserving the
| original year-based database selection logic from the Slim application.
|
| The active database is determined by reading /var/current_year.txt.
| If that file does not exist, the first available SQLite file in /var/
| is used as a fallback.
|
*/

$db = null;
$varDir = base_path('var');
$currentYearFile = $varDir . '/current_year.txt';

if (file_exists($currentYearFile)) {
    $year = trim((string) file_get_contents($currentYearFile));
    if (preg_match('/^\d{4}$/', $year)) {
        $db = $varDir . "/db_{$year}.sqlite";
    }
}

if ($db === null) {
    $files = is_dir($varDir) ? (scandir($varDir) ?: []) : [];
    foreach ($files as $file) {
        if (preg_match('/^db_(\d{4})\.sqlite$/', $file)) {
            $db = $varDir . '/' . $file;
            break;
        }
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | Development Mode
    |--------------------------------------------------------------------------
    */

    'dev_mode' => (bool) env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Metadata Cache Directory
    |--------------------------------------------------------------------------
    */

    'cache_dir' => storage_path('framework/doctrine'),

    /*
    |--------------------------------------------------------------------------
    | Entity Metadata Directories
    |--------------------------------------------------------------------------
    */

    'metadata_dirs' => [base_path('src/Domain')],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Uses the year-selected SQLite file resolved above.
    |
    */

    'connection' => [
        'driver' => 'pdo_sqlite',
        'path' => $db,
    ],

];
