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
        Schema::create('production_order_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->unsignedBigInteger('ingredient_item_id'); // Raw material/ingredient
            $table->decimal('planned_quantity', 10, 3);
            $table->decimal('issued_quantity', 10, 3)->default(0);
            $table->decimal('consumed_quantity', 10, 3)->default(0);
            $table->decimal('returned_quantity', 10, 3)->default(0);
            $table->string('unit_of_measurement')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_manually_added')->default(false);
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('production_orders');
            $table->foreign('ingredient_item_id')->references('id')->on('item_masters');
            $table->index(['production_order_id', 'ingredient_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_order_ingredients');
    }
};