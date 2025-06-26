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
        Schema::table('menu_menu_items', function (Blueprint $table) {
            // Rename special_price to override_price for consistency
            $table->renameColumn('special_price', 'override_price');
            
            // Rename display_order to sort_order for consistency
            $table->renameColumn('display_order', 'sort_order');
            
            // Add missing columns
            $table->text('special_notes')->nullable()->after('sort_order');
            $table->time('available_from')->nullable()->after('special_notes');
            $table->time('available_until')->nullable()->after('available_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_menu_items', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn(['special_notes', 'available_from', 'available_until']);
            
            // Revert column renames
            $table->renameColumn('override_price', 'special_price');
            $table->renameColumn('sort_order', 'display_order');
        });
    }
};
