<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('po_details', function (Blueprint $table) {
            $table->id('po_detail_id');
            $table->foreignId('po_id')->constrained('po_master', 'po_id')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('item_master'); // Changed from item_code to item_id
            $table->string('batch_no')->nullable();
            $table->decimal('buying_price', 12, 4); // Current buying price at time of PO
            $table->decimal('quantity', 12, 2);
            $table->decimal('line_total', 15, 2);
            $table->string('po_status', 50)->default('Pending');
            $table->timestamps();

            // Optional: Add index for better performance
            $table->index(['item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_details');
    }
};