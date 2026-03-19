<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent cast for the `breakdown` column.
 *
 * The column was previously managed by Doctrine with type `simple_array`,
 * which stores values as a comma-separated string (e.g. "PlaneRenewal,CustomerFees").
 * After the migration to Eloquent the new format is a JSON array
 * (e.g. '["PlaneRenewal","CustomerFees"]').
 *
 * This cast reads **both** the old comma-separated format and the new JSON
 * format, always returning a plain PHP array.  On write it always persists
 * the JSON representation so that rows are gradually normalised as they are
 * saved.
 */
class SimpleArrayCast implements CastsAttributes
{
    /**
     * Read the stored value and return a PHP array regardless of whether the
     * value on disk is a JSON array or a comma-separated simple_array string.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<int, string>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        // Try JSON first (new format written by Eloquent).
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Fall back to Doctrine simple_array format: comma-separated values.
        return array_values(array_filter(explode(',', $value), fn ($v) => $v !== ''));
    }

    /**
     * Encode the PHP array as JSON for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string|null
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return json_encode(array_values($value));
        }

        // Already a string — store as-is (should not normally happen).
        return $value;
    }
}
