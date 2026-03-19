<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add an `import_hash` column to the `lines` table.
 *
 * The hash is a SHA-256 of the raw source data that produced the row.
 * It is nullable so that rows imported before this migration (which have no
 * hash) are left untouched, and it carries a unique constraint so that any
 * attempt to insert a row whose hash already exists can be detected cheaply
 * before hitting the database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->string('import_hash', 64)->nullable()->unique()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->dropUnique(['import_hash']);
            $table->dropColumn('import_hash');
        });
    }
};