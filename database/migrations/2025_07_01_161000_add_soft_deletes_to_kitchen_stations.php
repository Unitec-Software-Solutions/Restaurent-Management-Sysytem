<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
   
    public function up(): void
    {
        if (Schema::hasTable('kitchen_stations')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('kitchen_stations');
                
                // Add soft deletes column if it doesn't exist
                if (!in_array('deleted_at', $existingColumns)) {
                    $table->softDeletes();
                    Log::info('Added deleted_at column to kitchen_stations table for soft deletes');
                }
                
                // Add any other missing columns for PostgreSQL
                if (!in_array('max_capacity', $existingColumns)) {
                    $table->decimal('max_capacity', 8, 2)->nullable()->after('order_priority');
                    Log::info('Added max_capacity column to kitchen_stations table');
                }
                
                if (!in_array('printer_config', $existingColumns)) {
                    $table->json('printer_config')->nullable()->after('max_capacity');
                    Log::info('Added printer_config column to kitchen_stations table');
                }
                
                if (!in_array('settings', $existingColumns)) {
                    $table->json('settings')->nullable()->after('printer_config');
                    Log::info('Added settings column to kitchen_stations table');
                }
                
                if (!in_array('description', $existingColumns)) {
                    $table->text('description')->nullable()->after('type');
                    Log::info('Added description column to kitchen_stations table');
                }
            });
        }
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        if (Schema::hasTable('kitchen_stations')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('kitchen_stations');
                
                if (in_array('deleted_at', $existingColumns)) {
                    $table->dropSoftDeletes();
                }
                
                $columnsToRemove = ['max_capacity', 'printer_config', 'settings', 'description'];
                foreach ($columnsToRemove as $column) {
                    if (in_array($column, $existingColumns)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};