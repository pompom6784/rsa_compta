<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

return function (ContainerBuilder $containerBuilder) {

    // Resolve the current year via YearService (file cache-based, with safe fallback)
    $year = null;
    if (class_exists(\App\Services\YearService::class) && function_exists('app')) {
        try {
            $year = app(\App\Services\YearService::class)->getCurrentYear();
        } catch (\Throwable $e) {
            // Laravel container not yet available; fall through to file-based fallback
        }
    }

    // Fallback: scan for first available sqlite file (used during early bootstrap or tests)
    $db = null;
    if ($year) {
        $db = "/var/db_$year.sqlite";
    } else {
        // @phpstan-ignore constant.notFound
        $files = scandir(APP_ROOT . '/var');
        foreach ($files as $file) {
            if (preg_match('/db_(\d{4})\.sqlite/', $file, $matches)) {
                $db = "/var/$file";
                break;
            }
        }
    }
    // Set the current file name as Session variable to display it in the navbar
    if ($db) {
        $_SESSION['current_year'] = preg_replace('/db_(\d{4})\.sqlite/', '$1', basename($db));
    }
    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () use ($db) {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
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
            ]);
        }
    ]);
};
