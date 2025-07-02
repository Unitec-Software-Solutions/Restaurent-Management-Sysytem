<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('reservations', 'reservation_date')) {
                $table->date('reservation_date')->nullable();
            }
            if (!Schema::hasColumn('reservations', 'device_info')) {
                $table->json('device_info')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['reservation_date', 'device_info']);
        });
    }
};
