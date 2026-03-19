<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Convert the `breakdown` column in the `lines` table from the legacy
 * Doctrine `simple_array` format (comma-separated plain string, e.g.
 * "PlaneRenewal,CustomerFees") to a JSON array (e.g.
 * '["PlaneRenewal","CustomerFees"]').
 *
 * Any row whose value already starts with '[' is already valid JSON and is
 * left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('lines')
            ->whereNotNull('breakdown')
            ->where('breakdown', 'not like', '[%')
            ->select('id', 'breakdown')
            ->get();

        foreach ($rows as $row) {
            $values = array_values(
                array_filter(explode(',', $row->breakdown), fn (string $v) => $v !== '')
            );

            DB::table('lines')
                ->where('id', $row->id)
                ->update(['breakdown' => json_encode($values)]);
        }
    }

    public function down(): void
    {
        $rows = DB::table('lines')
            ->whereNotNull('breakdown')
            ->where('breakdown', 'like', '[%')
            ->select('id', 'breakdown')
            ->get();

        foreach ($rows as $row) {
            $decoded = json_decode($row->breakdown, true);
            if (is_array($decoded)) {
                DB::table('lines')
                    ->where('id', $row->id)
                    ->update(['breakdown' => implode(',', $decoded)]);
            }
        }
    }
};
