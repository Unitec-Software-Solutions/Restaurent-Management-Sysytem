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
        Schema::create('good_received_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('good_received_note_id')->constrained();
            $table->foreignId('purchase_order_item_id')->constrained();
            $table->foreignId('inventory_item_id')->constrained();
            $table->string('item_code');
            $table->string('item_name');
            $table->integer('quantity');
            $table->decimal('expected_quantity', 10, 3);
            $table->decimal('received_quantity', 10, 3);
            $table->decimal('accepted_quantity', 10, 3);
            $table->decimal('rejected_quantity', 10, 3)->default(0);
            $table->integer('free_quantity')->default(0);
            $table->string('rejection_reason')->nullable();
            $table->decimal('cost_price', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->boolean('quality_checked')->default(false);
            $table->text('quality_check_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_received_note_items');
    }
};