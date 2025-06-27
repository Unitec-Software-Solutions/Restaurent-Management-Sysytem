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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            
            // Item details at time of order
            $table->string('item_name'); // Store name at time of order
            $table->text('item_description')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            
            // Customizations
            $table->json('customizations')->nullable(); // Size, extras, modifications
            $table->text('special_instructions')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('served_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'status']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
