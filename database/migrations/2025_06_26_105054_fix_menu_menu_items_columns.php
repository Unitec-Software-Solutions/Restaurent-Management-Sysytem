<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check if columns exist and fix them manually for PostgreSQL
        $columns = Schema::getColumnListing('menu_menu_items');
        
        if (in_array('special_price', $columns) && !in_array('override_price', $columns)) {
            // If special_price exists but override_price doesn't, rename it
            DB::statement('ALTER TABLE menu_menu_items RENAME COLUMN special_price TO override_price');
        }
        
        if (in_array('display_order', $columns) && !in_array('sort_order', $columns)) {
            // If display_order exists but sort_order doesn't, rename it
            DB::statement('ALTER TABLE menu_menu_items RENAME COLUMN display_order TO sort_order');
        }
        
        // Add missing columns if they don't exist
        Schema::table('menu_menu_items', function (Blueprint $table) use ($columns) {
            if (!in_array('override_price', $columns) && !in_array('special_price', $columns)) {
                $table->decimal('override_price', 10, 2)->nullable();
            }
            
            if (!in_array('sort_order', $columns) && !in_array('display_order', $columns)) {
                $table->integer('sort_order')->default(0);
            }
            
            if (!in_array('special_notes', $columns)) {
                $table->text('special_notes')->nullable();
            }
            
            if (!in_array('available_from', $columns)) {
                $table->time('available_from')->nullable();
            }
            
            if (!in_array('available_until', $columns)) {
                $table->time('available_until')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_menu_items', function (Blueprint $table) {
            $table->dropColumn(['special_notes', 'available_from', 'available_until']);
        });
        
        // Revert column names
        $columns = Schema::getColumnListing('menu_menu_items');
        
        if (in_array('override_price', $columns)) {
            DB::statement('ALTER TABLE menu_menu_items RENAME COLUMN override_price TO special_price');
        }
        
        if (in_array('sort_order', $columns)) {
            DB::statement('ALTER TABLE menu_menu_items RENAME COLUMN sort_order TO display_order');
        }
    }
};
