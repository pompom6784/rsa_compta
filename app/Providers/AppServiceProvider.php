<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\YearService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(YearService::class);

        $this->app->singleton(LoggerInterface::class, function (): LoggerInterface {
            return Log::channel();
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
