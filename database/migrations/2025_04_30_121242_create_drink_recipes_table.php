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
        Schema::create('drink_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drink_recipe_id')->constrained();
            $table->foreignId('inventory_item_id')->constrained();
            $table->decimal('quantity', 10, 3);
            $table->string('unit');
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
        Schema::dropIfExists('drink_recipes');
    }
};
