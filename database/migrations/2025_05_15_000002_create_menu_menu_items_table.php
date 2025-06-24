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
        Schema::create('menu_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->boolean('is_available')->default(true);
            $table->decimal('special_price', 10, 2)->nullable(); // Override price for this menu
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate menu-item pairs
            $table->unique(['menu_id', 'menu_item_id']);
            
            // Indexes for performance
            $table->index(['menu_id', 'is_available']);
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_menu_items');
    }
};
