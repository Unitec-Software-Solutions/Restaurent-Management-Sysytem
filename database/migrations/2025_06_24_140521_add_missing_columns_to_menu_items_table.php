<?php
// filepath: database/migrations/2025_06_24_add_missing_columns_to_menu_items.php

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
        Schema::table('menu_items', function (Blueprint $table) {
            // Add missing columns identified in diagnostic
            if (!Schema::hasColumn('menu_items', 'requires_preparation')) {
                $table->boolean('requires_preparation')->default(true)->after('is_active');
            }
            
            if (!Schema::hasColumn('menu_items', 'image_path')) {
                $table->string('image_path')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('menu_items', 'display_order')) {
                $table->integer('display_order')->default(0)->after('requires_preparation');
            }
            
            if (!Schema::hasColumn('menu_items', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
            
            if (!Schema::hasColumn('menu_items', 'kitchen_station_id')) {
                $table->foreignId('kitchen_station_id')->nullable()->constrained('kitchen_stations')->onDelete('set null')->after('category_id');
            }
            
            if (!Schema::hasColumn('menu_items', 'preparation_time')) {
                $table->integer('preparation_time')->nullable()->comment('Preparation time in minutes')->after('requires_preparation');
            }
            
            if (!Schema::hasColumn('menu_items', 'allergen_info')) {
                $table->json('allergen_info')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('menu_items', 'nutritional_info')) {
                $table->json('nutritional_info')->nullable()->after('allergen_info');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn([
                'requires_preparation',
                'image_path',
                'display_order',
                'is_featured',
                'kitchen_station_id',
                'preparation_time',
                'allergen_info',
                'nutritional_info'
            ]);
        });
    }
};
