<?php

declare(strict_types=1);

namespace App\Providers;

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
        $this->app->singleton(LoggerInterface::class, function (): Logger {
            $logPath = isset($_ENV['docker'])
                ? 'php://stdout'
                : storage_path('logs/app.log');

            $logger = new Logger('app');
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));

            return $logger;
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
