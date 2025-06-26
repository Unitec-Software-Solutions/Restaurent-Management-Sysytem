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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('production_item_id'); // FK to item master
            $table->string('recipe_name');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->decimal('yield_quantity', 10, 2)->default(1); // How many units this recipe produces
            $table->integer('preparation_time')->default(0);
            $table->integer('cooking_time')->default(0);
            $table->integer('total_time')->default(0);
            $table->string('difficulty_level')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('production_item_id')->references('id')->on('item_master');

            $table->index(['organization_id', 'production_item_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
