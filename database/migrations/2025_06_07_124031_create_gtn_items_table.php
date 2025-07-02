<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gtn_items', function (Blueprint $table) {
            $table->id('gtn_item_id');

            // Foreign Keys
            $table->foreignId('gtn_id')->constrained('gtn_master', 'gtn_id')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('item_masters')->cascadeOnDelete();

            // Item Details
            $table->string('item_code')->index();
            $table->string('item_name'); // Copy of ItemMaster::name at transfer time
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();

            // Quantities
            $table->decimal('transfer_quantity', 12, 2);
            $table->decimal('received_quantity', 12, 2)->nullable(); // Optional: can be filled upon receiving
            $table->decimal('damaged_quantity', 12, 2)->default(0);

            // Pricing (optional if needed for accounting)
            $table->decimal('transfer_price', 12, 4)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gtn_items');
    }
};

