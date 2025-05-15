<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Filename: 202X_XX_XX_XXXXXX_remove_inventory_item_foreign_keys.php

    public function up(): void
    {
        // Drop all foreign key constraints first
        Schema::table('inventory_stock', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
        });

        Schema::table('menu_item_ingredients', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
        });

        Schema::table('bar_inventory', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
        });

        Schema::table('bar_inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
        });

        Schema::table('drink_recipes', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
        });

        Schema::table('good_received_note_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_stock', function (Blueprint $table) {
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items');
        });
    }
};
