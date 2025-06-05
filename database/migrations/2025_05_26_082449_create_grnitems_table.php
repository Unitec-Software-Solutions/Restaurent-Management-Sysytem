<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_items', function (Blueprint $table) {
            $table->id('grn_item_id');
            $table->foreignId('grn_id')->constrained('grn_master', 'grn_id')->cascadeOnDelete();
            $table->foreignId('po_detail_id')->nullable()->constrained('po_details', 'po_detail_id');
            $table->foreignId('item_id')->constrained('item_master', 'id')->cascadeOnDelete();
            $table->string('item_code');
            $table->string('item_name');
            $table->string('batch_no')->nullable();
            $table->decimal('ordered_quantity', 12, 2)->default(0);
            $table->decimal('received_quantity', 12, 2);
            $table->decimal('accepted_quantity', 12, 2);
            $table->decimal('rejected_quantity', 12, 2)->default(0);
            $table->decimal('buying_price', 12, 4);
            $table->decimal('line_total', 15, 2);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Add index for item_code 
            // $table->foreign('item_code')->references('item_code')->on('item_master');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};