<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_release_note_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('srn_id')->nullable()->constrained('stock_release_note_master', 'srn_id')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('item_master')->cascadeOnDelete();

            // Item identification
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable(); 

            // Quantities
            $table->decimal('release_quantity', 12, 4)->default(0)->nullable(); // Quantity of the item released
            $table->string('unit_of_measurement')->nullable();  // Unit of measurement for the item

            $table->decimal('release_price', 12, 4)->default(0)->nullable(); // Price per unit at the time of release
            $table->decimal('line_total', 15, 2)->default(0)->nullable();   // Total price for the released quantity

            // Batch and dates
            $table->string('batch_no')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_release_note_items');
    }
};
