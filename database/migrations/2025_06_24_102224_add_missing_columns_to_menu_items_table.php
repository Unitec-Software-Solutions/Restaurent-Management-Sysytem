<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations following UI/UX guidelines.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('menu_items');
            
            // Add missing columns that the seeder expects
            if (!in_array('requires_preparation', $existingColumns)) {
                $table->boolean('requires_preparation')->default(true)->after('is_featured');
            }
            
            if (!in_array('station', $existingColumns)) {
                $table->string('station')->default('kitchen')->after('preparation_time');
            }
            
            if (!in_array('is_vegetarian', $existingColumns)) {
                $table->boolean('is_vegetarian')->default(false)->after('station');
            }
            
            if (!in_array('contains_alcohol', $existingColumns)) {
                $table->boolean('contains_alcohol')->default(false)->after('is_vegetarian');
            }
            
            if (!in_array('image_path', $existingColumns)) {
                $table->string('image_path')->nullable()->after('contains_alcohol');
            }
            
            if (!in_array('is_active', $existingColumns)) {
                $table->boolean('is_active')->default(true)->after('ingredients');
            }
            
            // Update existing columns to match expected data types
            if (in_array('allergens', $existingColumns)) {
                // Convert allergens to JSON if it's not already
                $table->json('allergens')->nullable()->change();
            }
            
            // Add indexes for better performance
            $table->index(['is_active', 'is_available'], 'menu_items_active_available_idx');
            $table->index(['organization_id', 'is_active'], 'menu_items_org_active_idx');
            $table->index(['branch_id', 'is_active'], 'menu_items_branch_active_idx');
            $table->index(['requires_preparation', 'station'], 'menu_items_prep_station_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('menu_items');
            
            // Remove added columns
            $columnsToRemove = [
                'requires_preparation',
                'station', 
                'is_vegetarian',
                'contains_alcohol',
                'image_path',
                'is_active'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (in_array($column, $existingColumns)) {
                    $table->dropColumn($column);
                }
            }
            
            // Drop indexes
            try {
                $table->dropIndex('menu_items_active_available_idx');
                $table->dropIndex('menu_items_org_active_idx');
                $table->dropIndex('menu_items_branch_active_idx');
                $table->dropIndex('menu_items_prep_station_idx');
            } catch (\Exception $e) {
                // Indexes might not exist
            }
        });
    }
};
