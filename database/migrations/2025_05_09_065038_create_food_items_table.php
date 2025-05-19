<?php


// database/migrations/xxxx_xx_xx_create_food_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodItemsTable extends Migration
{
    public function up()
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->id('item_id'); // Primary key
            $table->string('name');
            $table->decimal('price', 8, 2); // Price with 8 digits total and 2 decimal places
            $table->decimal('cost', 8, 2); // Cost with 8 digits total and 2 decimal places
            $table->text('ingredients')->nullable(); // Ingredients can be nullable
            $table->string('img')->nullable(); // Image path can be nullable
            $table->boolean('is_active')->default(true); // Default to active
            $table->integer('pre_time')->nullable(); // Preparation time in minutes
            $table->enum('portion_size', ['single', 'double', 'family']); // Portion size options
            $table->boolean('display_in_menu')->default(true); // Default to display in menu
            $table->time('available_from')->nullable(); // Available from time
            $table->time('available_to')->nullable(); // Available to time
            $table->string('available_days')->nullable(); // Available days (e.g., "Mon,Tue,Wed")
            $table->boolean('promotions')->default(false); // Default to no promotions
            $table->decimal('discounts', 8, 2)->nullable(); // Discounts can be nullable
            $table->timestamps(); // created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('food_items');
    }
}