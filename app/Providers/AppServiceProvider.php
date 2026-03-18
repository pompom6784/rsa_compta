<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\YearService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('logger.app', function (): Logger {
            $logPath = isset($_ENV['docker'])
                ? 'php://stdout'
                : storage_path('logs/app.log');

            $logger = new Logger('app');
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));

            return $logger;
        });

        $this->app->singleton(YearService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $yearService = $this->app->make(YearService::class);
        $year = $yearService->getCurrentYear();

        Config::set(
            'database.connections.sqlite.database',
            base_path("var/db_{$year}.sqlite")
        );
    }
}
