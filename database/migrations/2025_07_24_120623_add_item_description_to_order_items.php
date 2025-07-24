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
        Schema::table('order_items', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('order_items');
            
            // Add item_description column if it doesn't exist
            if (!in_array('item_description', $existingColumns)) {
                $table->text('item_description')->nullable()->after('item_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('order_items');
            
            if (in_array('item_description', $existingColumns)) {
                $table->dropColumn('item_description');
            }
        });
    }
};
