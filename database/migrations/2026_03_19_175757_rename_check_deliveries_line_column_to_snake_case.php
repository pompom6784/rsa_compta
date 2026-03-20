<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RENAMES = [
        'checkNumber' => 'check_number',
        'checkDelivery_id' => 'check_delivery_id',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('check_deliveries_line', function (Blueprint $table) {
            foreach (self::RENAMES as $from => $to) {
                $table->renameColumn($from, $to);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_deliveries_line', function (Blueprint $table) {
            foreach (array_flip(self::RENAMES) as $from => $to) {
                $table->renameColumn($from, $to);
            }
        });
    }
};
