<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\YearService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

/**
 * Runs `php artisan migrate` against every accounting-year SQLite database
 * found in the `var/` directory.
 *
 * Because each accounting year lives in its own `var/db_YYYY.sqlite` file,
 * a single `php artisan migrate` only updates the currently-selected year.
 * This command iterates all available years and migrates each one in turn.
 *
 * Usage:
 *   php artisan migrate:all-years
 *   php artisan migrate:all-years --rollback   # roll back the last batch on every DB
 */
class MigrateAllYears extends Command
{
    protected $signature = 'migrate:all-years
                            {--rollback : Roll back the last migration batch on every year database}
                            {--force : Force the operation to run in production}';

    protected $description = 'Run database migrations against every accounting-year SQLite database';

    public function handle(YearService $yearService): int
    {
        $years = $yearService->getAvailableYears();

        if (empty($years)) {
            $this->warn('No accounting-year databases found in var/.');
            return self::SUCCESS;
        }

        $rollback = (bool) $this->option('rollback');
        $force    = (bool) $this->option('force');

        foreach ($years as $year) {
            $this->info("Migrating year <comment>{$year}</comment>…");

            Config::set(
                'database.connections.sqlite.database',
                base_path("var/db_{$year}.sqlite")
            );

            // Invalidate the resolved connection so Laravel picks up the new path.
            app('db')->purge('sqlite');
            app('db')->reconnect('sqlite');

            $artisanArgs = ['--database' => 'sqlite', '--force' => $force];

            if ($rollback) {
                $this->call('migrate:rollback', $artisanArgs);
            } else {
                $this->call('migrate', $artisanArgs);
            }
        }

        return self::SUCCESS;
    }
}
