<?php

declare(strict_types=1);

namespace App\Providers;

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class DoctrineServiceProvider extends ServiceProvider
{
    /**
     * Register Doctrine EntityManager into the container.
     */
    public function register(): void
    {
        $this->app->singleton(EntityManager::class, function (Application $app): EntityManager {
            /** @var \Illuminate\Config\Repository $config */
            $config = $app['config'];

            $devMode     = (bool) $config->get('doctrine.dev_mode', true);
            $cacheDir    = (string) $config->get('doctrine.cache_dir', storage_path('framework/doctrine'));
            $metaDirs    = (array) $config->get('doctrine.metadata_dirs', [base_path('src/Domain')]);
            $connection  = (array) $config->get('doctrine.connection', []);

            $cache = $devMode
                ? DoctrineProvider::wrap(new ArrayAdapter())
                : DoctrineProvider::wrap(new FilesystemAdapter(directory: $cacheDir));

            $ormConfig = Setup::createAttributeMetadataConfiguration(
                $metaDirs,
                $devMode,
                null,
                $cache
            );

            // Preserve the current_year session variable used by the navbar template
            $dbPath = $connection['path'] ?? null;
            if ($dbPath !== null && session_status() === PHP_SESSION_ACTIVE) {
                if (preg_match('/db_(\d{4})\.sqlite$/', basename((string) $dbPath), $matches)) {
                    $_SESSION['current_year'] = $matches[1];
                }
            }

            return EntityManager::create($connection, $ormConfig);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
