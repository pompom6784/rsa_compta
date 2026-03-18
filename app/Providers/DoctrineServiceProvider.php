<?php

declare(strict_types=1);

namespace App\Providers;

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use App\Services\YearService;
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

            $devMode  = (bool) $config->get('doctrine.dev_mode', true);
            $cacheDir = (string) $config->get('doctrine.cache_dir', storage_path('framework/doctrine'));
            $metaDirs = (array) $config->get('doctrine.metadata_dirs', [base_path('src/Domain')]);

            $cache = $devMode
                ? DoctrineProvider::wrap(new ArrayAdapter())
                : DoctrineProvider::wrap(new FilesystemAdapter(directory: $cacheDir));

            $ormConfig = Setup::createAttributeMetadataConfiguration(
                $metaDirs,
                $devMode,
                null,
                $cache
            );

            /** @var YearService $yearService */
            $yearService = $app->make(YearService::class);
            $year        = $yearService->getCurrentYear();

            $connection = [
                'driver' => 'pdo_sqlite',
                'path'   => base_path("var/db_{$year}.sqlite"),
            ];

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
