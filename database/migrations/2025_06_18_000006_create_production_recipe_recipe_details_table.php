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
        Schema::create('production_recipe_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id');
            $table->unsignedBigInteger('raw_material_item_id'); // FK to item master for the ingredient/raw material
            $table->decimal('quantity_required', 10, 3); // Quantity needed per yield_quantity of the recipe
            $table->string('unit_of_measurement')->nullable();
            $table->text('preparation_notes')->nullable();
            $table->timestamps();

            $table->foreign('recipe_id')->references('id')->on('production_recipes');
            $table->foreign('raw_material_item_id')->references('id')->on('item_master');

            $table->index(['recipe_id', 'raw_material_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_recipe_details');
    }
};
