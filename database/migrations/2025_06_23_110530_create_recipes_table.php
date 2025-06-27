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
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->foreignId('ingredient_item_id')->constrained('item_master')->onDelete('cascade');
            $table->decimal('quantity_needed', 10, 3)->default(0); // Amount of ingredient needed per portion
            $table->string('unit', 20)->default('g'); // Unit of measurement (g, ml, piece, etc.)
            $table->decimal('waste_percentage', 5, 2)->default(0); // Waste factor (5% = 5.00)
            $table->text('notes')->nullable(); // Preparation notes or instructions
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index(['menu_item_id', 'is_active']);
            $table->index(['ingredient_item_id', 'is_active']);
            
            // Unique constraint to prevent duplicate ingredients per menu item
            $table->unique(['menu_item_id', 'ingredient_item_id'], 'unique_menu_ingredient');
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
