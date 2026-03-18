<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Doctrine ORM Configuration
|--------------------------------------------------------------------------
|
| Static configuration for Doctrine ORM. The active database connection
| path is resolved at runtime by DoctrineServiceProvider via YearService,
| so only non-dynamic settings live here.
|
*/

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

];
