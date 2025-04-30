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
        Schema::create('bar_inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('inventory_item_id')->constrained();
            $table->enum('transaction_type', ['receive', 'serve', 'wastage', 'transfer', 'reconciliation']);
            $table->decimal('bottles_count', 10, 2)->default(0);
            $table->decimal('liters_count', 10, 3)->default(0);
            $table->foreignId('user_id')->constrained(); // Who performed the transaction
            $table->foreignId('order_id')->nullable()->constrained(); // If related to an order
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
        Schema::dropIfExists('bar_inventory_transactions');
    }
};
