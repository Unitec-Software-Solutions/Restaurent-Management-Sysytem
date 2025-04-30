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
        Schema::create('inventory_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('inventory_item_id')->constrained();
            $table->decimal('current_quantity', 10, 3);
            $table->decimal('committed_quantity', 10, 3)->default(0); // Quantity allocated for kitchen
            $table->decimal('available_quantity', 10, 3)->default(0); // current_quantity - committed_quantity
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
        Schema::dropIfExists('inventory_stock');
    }
};
