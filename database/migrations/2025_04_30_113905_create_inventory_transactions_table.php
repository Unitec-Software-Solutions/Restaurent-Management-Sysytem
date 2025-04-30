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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('inventory_item_id')->constrained();
            $table->enum('transaction_type', ['purchase', 'transfer_in', 'transfer_out', 'usage', 'adjustment', 'return', 'wastage']);
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->foreignId('source_id')->nullable(); // For reference (order_id, purchase_id, etc.)
            $table->string('source_type')->nullable(); // For reference (Order, Purchase, etc.)
            $table->foreignId('user_id')->constrained(); // Who performed the transaction
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('inventory_transactions');
    }
};
