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
        Schema::create('food_items', function (Blueprint $table) {
            $table->id('item_id'); // Primary key
            $table->string('name'); // Name of the food item
            $table->decimal('price', 8, 2); // Price (8 digits total, 2 decimal places)
            $table->decimal('cost', 8, 2); // Cost (8 digits total, 2 decimal places)
            $table->text('ingredients')->nullable(); // Ingredients (optional)
            $table->string('image_url')->nullable(); // Image URL (optional)
            $table->integer('prep_time'); // Preparation time in minutes
            $table->boolean('is_active')->default(true); // Active status
            $table->enum('portion_size', ['half', 'full']); // Portion size
            $table->boolean('display_in_menu')->default(true); // Display in menu
            $table->time('available_from')->nullable(); // Available from time
            $table->time('available_to')->nullable(); // Available to time
            $table->string('days_available')->nullable(); // Days available (e.g., "Mon,Tue,Wed")
            $table->boolean('promotions')->default(false); // Promotions status
            $table->decimal('discounts', 8, 2)->nullable(); // Discounts (optional)
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};