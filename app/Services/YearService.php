<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class YearService
{
    private const YEARS_CACHE_KEY = 'accounting:available_years';
    private const CURRENT_YEAR_CACHE_KEY = 'accounting:current_year';
    private const VAR_PATH = 'var';

    /**
     * Get the current accounting year with safe fallback.
     *
     * @return string|null The current accounting year, or null if none is available.
     */
    public function getCurrentYear(): ?string
    {
        $cachedYear = Cache::get(self::CURRENT_YEAR_CACHE_KEY);
        if ($cachedYear && $this->yearExists($cachedYear)) {
            return $cachedYear;
        }

        $firstYear = $this->getFirstAvailableYear();
        if ($firstYear) {
            $this->setCurrentYear($firstYear);
            return $firstYear;
        }

        // No valid year found; return null so callers can handle the absence explicitly.
        return null;
    }

    /**
     * Set the current year and cache it for 7 days.
     */
    public function setCurrentYear(string $year): void
    {
        if ($this->yearExists($year)) {
            Cache::put(self::CURRENT_YEAR_CACHE_KEY, $year, now()->addDays(7));
        }
    }

    /**
     * Get all available years by scanning for db_YYYY.sqlite files.
     * Results are cached for 1 hour.
     */
    public function getAvailableYears(): array
    {
        return Cache::remember(self::YEARS_CACHE_KEY, now()->addHour(), function () {
            $years = [];
            $varPath = base_path(self::VAR_PATH);

            if (!is_dir($varPath)) {
                return $years;
            }

            foreach (File::files($varPath) as $file) {
                if (preg_match('/db_(\d{4})\.sqlite/', $file->getFilename(), $matches)) {
                    $years[] = $matches[1];
                }
            }

            sort($years);
            return $years;
        });
    }

    /**
     * Check if a year has a corresponding SQLite database file.
     */
    public function yearExists(string $year): bool
    {
        if (!preg_match('/^\d{4}$/', $year)) {
            return false;
        }

        return in_array($year, $this->getAvailableYears(), true);
    }

    /**
     * Get the first available year, or null if none exist.
     */
    public function getFirstAvailableYear(): ?string
    {
        $years = $this->getAvailableYears();
        return $years[0] ?? null;
    }

    /**
     * Clear the cached list of available years.
     * Call this when new database files are added.
     */
    public function refreshAvailableYears(): void
    {
        Cache::forget(self::YEARS_CACHE_KEY);
    }
}
