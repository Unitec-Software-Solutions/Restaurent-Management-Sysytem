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
        Schema::create('kitchen_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained();
            $table->foreignId('waiter_id')->references('id')->on('users');
            $table->enum('return_reason', ['quality_issue', 'wrong_item', 'customer_preference', 'other']);
            $table->text('description')->nullable();
            $table->boolean('requires_reprepare')->default(false);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'repreparing', 'completed'])->default('pending');
            $table->timestamps();
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // Adds deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_returns');
    }
};
