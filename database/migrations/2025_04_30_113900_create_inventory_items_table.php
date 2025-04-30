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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_category_id')->constrained();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('unit_of_measurement');
            $table->decimal('reorder_level', 10, 3);
            $table->boolean('is_perishable')->default(false);
            $table->integer('shelf_life_days')->nullable(); // For perishable items
            $table->boolean('is_active')->default(true);
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // Adds deleted_at column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
