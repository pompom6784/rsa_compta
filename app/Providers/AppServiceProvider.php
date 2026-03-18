<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\Persistence\CheckDelivery\DbCheckDeliveryRepository;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use App\Services\CheckDeliveryImportService;
use App\Services\ExcelExportService;
use App\Services\PaypalImportService;
use App\Services\SGImportService;
use App\Services\SogecomImportService;
use App\Services\YearService;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(YearService::class);

        $this->app->singleton(LoggerInterface::class, function (): Logger {
            $logPath = isset($_ENV['docker'])
                ? 'php://stdout'
                : storage_path('logs/app.log');

            $logger = new Logger('app');
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));

            return $logger;
        });

        // Repositories
        $this->app->singleton(DbLineRepository::class, function ($app): DbLineRepository {
            return new DbLineRepository($app->make(EntityManager::class));
        });

        $this->app->singleton(DbCheckDeliveryRepository::class, function ($app): DbCheckDeliveryRepository {
            return new DbCheckDeliveryRepository($app->make(EntityManager::class));
        });

        // Services
        $this->app->singleton(PaypalImportService::class, function ($app): PaypalImportService {
            return new PaypalImportService($app->make(EntityManager::class));
        });

        $this->app->singleton(SogecomImportService::class, function ($app): SogecomImportService {
            return new SogecomImportService($app->make(EntityManager::class));
        });

        $this->app->singleton(SGImportService::class, function ($app): SGImportService {
            return new SGImportService($app->make(EntityManager::class));
        });

        $this->app->singleton(CheckDeliveryImportService::class, function ($app): CheckDeliveryImportService {
            return new CheckDeliveryImportService($app->make(EntityManager::class));
        });

        $this->app->singleton(ExcelExportService::class, function ($app): ExcelExportService {
            return new ExcelExportService($app->make(DbLineRepository::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** @var YearService $yearService */
        $yearService = $this->app->make(YearService::class);
        $year = $yearService->getCurrentYear();

        Config::set(
            'database.connections.sqlite.database',
            base_path("var/db_{$year}.sqlite")
        );

        // Make current_year available in all Blade views automatically
        View::share('current_year', $year);
    }
}
