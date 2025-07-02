<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_items', function (Blueprint $table) {
            // Primary key
            $table->id('grn_item_id');

            // Foreign keys
            $table->foreignId('grn_id')->constrained('grn_master', 'grn_id')->cascadeOnDelete();
            $table->foreignId('po_detail_id')->nullable()->constrained('po_details', 'po_detail_id');
            $table->foreignId('item_id')->constrained('item_masters', 'id')->cascadeOnDelete();

            // Item identification
            $table->string('item_code');
            $table->string('item_name');

            // Quantities
            $table->decimal('ordered_quantity', 12, 2)->default(0);
            $table->decimal('received_quantity', 12, 2);
            $table->decimal('free_received_quantity', 12, 2)->default(0);
            $table->decimal('rejected_quantity', 12, 2)->default(0);
            $table->decimal('accepted_quantity', 12, 2)->default(0);



            // Pricing and discount
            $table->decimal('buying_price', 12, 4);
            $table->decimal('line_total', 15, 2);
            $table->decimal('discount_received', 10, 2)->default(0);

            // Batch and dates
            $table->string('batch_no')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Rejection and notes
            $table->string('rejection_reason')->nullable();
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Add index for item_code
            // $table->foreign('item_code')->references('item_code')->on('item_masters');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};
