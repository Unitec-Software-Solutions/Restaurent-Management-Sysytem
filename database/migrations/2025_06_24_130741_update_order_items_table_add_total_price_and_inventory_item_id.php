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
            
            // Add total_price column if it doesn't exist
            if (!in_array('total_price', $existingColumns)) {
                $table->decimal('total_price', 10, 2)->nullable()->after('unit_price');
            }
            
            // Add inventory_item_id if missing (referenced in OrderItem model)
            if (!in_array('inventory_item_id', $existingColumns)) {
                $table->foreignId('inventory_item_id')->nullable()->after('menu_item_id')
                      ->constrained('item_master')->onDelete('set null');
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
            
            if (in_array('total_price', $existingColumns)) {
                $table->dropColumn('total_price');
            }
            
            if (in_array('inventory_item_id', $existingColumns)) {
                $table->dropForeign(['inventory_item_id']);
                $table->dropColumn('inventory_item_id');
            }
        });
    }
};
