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
            $table->foreignId('gtn_id')->constrained('gtn_master', 'gtn_id')->cascadeOnDelete()->nullable();
            $table->foreignId('item_id')->constrained('item_master')->cascadeOnDelete()->nullable();

            // Item Details
            $table->string('item_code')->index()->nullable();
            $table->string('item_name')->nullable(); // Copy of ItemMaster::name at transfer time
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();

            // Quantities
            $table->decimal('transfer_quantity', 12, 2)->nullable();

            // Add quantity tracking fields
            $table->decimal('quantity_accepted', 10, 2)->nullable()->after('transfer_quantity');
            $table->decimal('quantity_rejected', 10, 2)->default(0)->after('quantity_accepted');

            $table->decimal('received_quantity', 12, 2)->nullable(); // Optional: can be filled upon receiving
            $table->decimal('damaged_quantity', 12, 2)->default(0)->nullable();

            // Pricing (optional if needed for accounting)
            $table->decimal('transfer_price', 12, 4)->default(0)->nullable();
            $table->decimal('line_total', 15, 2)->default(0)->nullable();

            $table->text('notes')->nullable();

            // Add item-level rejection tracking
            $table->text('item_rejection_reason')->nullable()->after('notes');
            $table->enum('item_status', ['pending', 'accepted', 'rejected', 'partially_accepted'])
                  ->default('pending')
                  ->after('item_rejection_reason');

            // Add quality check fields
            $table->json('quality_notes')->nullable()->after('item_status');
            $table->integer('inspected_by')->nullable()->after('quality_notes');
            $table->timestamp('inspected_at')->nullable()->after('inspected_by');

            $table->timestamps();
            $table->softDeletes(); // For soft delete functionality
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gtn_items');
    }
};

