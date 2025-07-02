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
            
            // Add menu_category_id if it doesn't exist
            if (!in_array('menu_category_id', $existingColumns)) {
                $table->foreignId('menu_category_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('menu_categories')
                      ->onDelete('cascade');
            }
            
            // Add organization and branch references for better data organization
            if (!in_array('organization_id', $existingColumns)) {
                $table->foreignId('organization_id')
                      ->nullable()
                      ->after('menu_category_id')
                      ->constrained('organizations')
                      ->onDelete('cascade');
            }
            
            if (!in_array('branch_id', $existingColumns)) {
                $table->foreignId('branch_id')
                      ->nullable()
                      ->after('organization_id')
                      ->constrained('branches')
                      ->onDelete('cascade');
            }
            
            // Add item_masters_id for inventory tracking
            if (!in_array('item_masters_id', $existingColumns)) {
                $table->foreignId('item_masters_id')
                      ->nullable()
                      ->after('branch_id')
                      ->constrained('item_masters')
                      ->onDelete('set null');
            }
            
            // Add display and ordering fields following UI/UX guidelines
            if (!in_array('display_order', $existingColumns)) {
                $table->integer('display_order')->default(0)->after('price');
            }
            
            if (!in_array('is_featured', $existingColumns)) {
                $table->boolean('is_featured')->default(false)->after('is_available');
            }
            
            if (!in_array('is_spicy', $existingColumns)) {
                $table->boolean('is_spicy')->default(false)->after('is_vegetarian');
            }
            
            // Add nutritional information for UI display
            if (!in_array('calories', $existingColumns)) {
                $table->integer('calories')->nullable()->after('allergens');
            }
            
            if (!in_array('ingredients', $existingColumns)) {
                $table->text('ingredients')->nullable()->after('calories');
            }
            
            // Add promotional fields
            if (!in_array('promotion_price', $existingColumns)) {
                $table->decimal('promotion_price', 10, 2)->nullable()->after('price');
            }
            
            if (!in_array('promotion_start', $existingColumns)) {
                $table->datetime('promotion_start')->nullable()->after('promotion_price');
            }
            
            if (!in_array('promotion_end', $existingColumns)) {
                $table->datetime('promotion_end')->nullable()->after('promotion_start');
            }
        });
        
        // Add indexes for performance following UI/UX query patterns
        Schema::table('menu_items', function (Blueprint $table) {
            try {
                $table->index(['organization_id', 'is_available'], 'menu_items_org_available_idx');
                $table->index(['branch_id', 'is_available'], 'menu_items_branch_available_idx');
                $table->index(['menu_category_id', 'display_order'], 'menu_items_category_order_idx');
                $table->index(['is_featured', 'is_available'], 'menu_items_featured_idx');
            } catch (\Exception $e) {
                // Silently continue if indexes already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Drop indexes first
            try {
                $table->dropIndex('menu_items_org_available_idx');
                $table->dropIndex('menu_items_branch_available_idx');
                $table->dropIndex('menu_items_category_order_idx');
                $table->dropIndex('menu_items_featured_idx');
            } catch (\Exception $e) {
                // Continue if indexes don't exist
            }
            
            // Drop foreign key constraints
            try {
                $table->dropForeign(['menu_category_id']);
                $table->dropForeign(['organization_id']);
                $table->dropForeign(['branch_id']);
                $table->dropForeign(['item_masters_id']);
            } catch (\Exception $e) {
                // Continue if constraints don't exist
            }
            
            // Drop columns
            $columnsToRemove = [
                'menu_category_id',
                'organization_id',
                'branch_id',
                'item_masters_id',
                'display_order',
                'is_featured',
                'is_spicy',
                'calories',
                'ingredients',
                'promotion_price',
                'promotion_start',
                'promotion_end'
            ];
            
            $existingColumns = Schema::getColumnListing('menu_items');
            
            foreach ($columnsToRemove as $column) {
                if (in_array($column, $existingColumns)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
