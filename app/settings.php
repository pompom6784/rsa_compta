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

    // Read the current database file from /var/current_year.txt
    $db = null;
    $currentYearFile = APP_ROOT . '/var/current_year.txt';
    if (file_exists($currentYearFile)) {
        $year = file_get_contents($currentYearFile);
        $db = "/var/db_$year.sqlite";
    }
    // Pick the first sqlite file in the var directory as default database
    if (!$db) {
        // @phpstan-ignore constant.notFound
        $files = scandir(APP_ROOT . '/var');
        foreach ($files as $file) {
            if (preg_match('/db_(\d{4})\.sqlite/', $file,
                $matches)) {
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
        SettingsInterface::class => function () {
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
                        'path' => APP_ROOT . ($_SESSION['db_path'] ?? '/var/db.sqlite'),
                    ],
                ]
            ]);
        }
    ]);
};
