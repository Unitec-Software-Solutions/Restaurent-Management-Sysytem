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
        Schema::table('inventory_stock', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });

        Schema::table('good_received_note_items', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });
        Schema::table('menu_recipes', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });
        Schema::table('menu_item_ingredients', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });
        Schema::table('bar_inventory', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });
        Schema::table('bar_inventory_transactions', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });
        Schema::table('drink_recipes', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained('item_master');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_master_foreign_keys');
    }
};
