<?php

declare(strict_types=1);

namespace App\Providers;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
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
            $metaDirs = (array) $config->get('doctrine.metadata_dirs', []);

            $cache = $devMode
                ? new ArrayAdapter()
                : new FilesystemAdapter(directory: $cacheDir);

            $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
                paths: $metaDirs,
                isDevMode: $devMode,
                cache: $cache,
            );

            /** @var YearService $yearService */
            $yearService = $app->make(YearService::class);
            $year        = $yearService->getCurrentYear();

            $connection = DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'path'   => base_path("var/db_{$year}.sqlite"),
            ], $ormConfig);

            return new EntityManager($connection, $ormConfig);
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
