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
     Schema::create('po_details', function (Blueprint $table) {
            $table->id('po_detail_id');
            $table->foreignId('po_id')->constrained('po_master', 'po_id')->onDelete('cascade');
            $table->string('item_code');
            $table->string('batch_no')->nullable();
            $table->decimal('buying_price', 12, 4);
            $table->decimal('quantity', 12, 2);
            $table->decimal('line_total', 15, 2);
            $table->string('po_status', 50)->default('Pending'); // Suggest linking to lookup table in the future
            $table->timestamps();

            // Optional foreign key linking item_code to item_master (by item_code)
            // If item_code is guaranteed to match exactly with items_master.item_code
            // You can uncomment the below line:
            // $table->foreign('item_code')->references('item_code')->on('items_master');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('po_details');
    }
};
