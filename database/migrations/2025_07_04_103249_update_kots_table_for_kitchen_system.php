<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        if (Schema::hasTable('kots')) {
            Schema::table('kots', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('kots');
                
                // Add organization_id if missing
                if (!in_array('organization_id', $existingColumns)) {
                    $table->foreignId('organization_id')->nullable()->after('id')
                          ->constrained('organizations')->onDelete('cascade');
                }
                
                // Add branch_id if missing
                if (!in_array('branch_id', $existingColumns)) {
                    $table->foreignId('branch_id')->nullable()->after('organization_id')
                          ->constrained('branches')->onDelete('cascade');
                }
                
                // Add estimated_completion_time if missing
                if (!in_array('estimated_completion_time', $existingColumns)) {
                    $table->timestamp('estimated_completion_time')->nullable()->after('prepared_at');
                }
                
                // Add kitchen station assignment if not using separate foreign key
                if (!in_array('assigned_station_id', $existingColumns) && in_array('kitchen_station_id', $existingColumns)) {
                    // kitchen_station_id already exists from earlier migration
                } elseif (!in_array('kitchen_station_id', $existingColumns)) {
                    $table->foreignId('kitchen_station_id')->nullable()->after('order_id')
                          ->constrained('kitchen_stations')->onDelete('set null');
                }
                
                // Add soft deletes if missing
                if (!in_array('deleted_at', $existingColumns)) {
                    $table->softDeletes();
                }
            });
            
            // Update existing KOTs to have organization_id from their orders
            if (Schema::hasColumn('kots', 'organization_id')) {
                DB::statement('
                    UPDATE kots 
                    SET organization_id = (
                        SELECT o.organization_id 
                        FROM orders o 
                        WHERE o.id = kots.order_id
                    ) 
                    WHERE organization_id IS NULL
                ');
            }
            
            // Update existing KOTs to have branch_id from their orders
            if (Schema::hasColumn('kots', 'branch_id')) {
                DB::statement('
                    UPDATE kots 
                    SET branch_id = (
                        SELECT o.branch_id 
                        FROM orders o 
                        WHERE o.id = kots.order_id
                    ) 
                    WHERE branch_id IS NULL
                ');
            }
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        if (Schema::hasTable('kots')) {
            Schema::table('kots', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('kots');
                
                if (in_array('organization_id', $existingColumns)) {
                    $table->dropForeign(['organization_id']);
                    $table->dropColumn('organization_id');
                }
                
                if (in_array('branch_id', $existingColumns)) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                
                if (in_array('estimated_completion_time', $existingColumns)) {
                    $table->dropColumn('estimated_completion_time');
                }
                
                if (in_array('deleted_at', $existingColumns)) {
                    $table->dropColumn('deleted_at');
                }
            });
        }
    }
};
