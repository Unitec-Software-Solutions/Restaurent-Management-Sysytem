<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel + PostgreSQL + Tailwind CSS
     */
    public function up(): void
    {
        Schema::create('item_transactions', function (Blueprint $table) {
            $table->id();

            // Organization and branch relationships
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();

            // Item reference - FIXED to use correct table name
            $table->unsignedBigInteger('inventory_item_id')->nullable();
            $table->unsignedBigInteger('item_master_id')->nullable(); // Alternative reference

            // Transaction details
            $table->string('transaction_type')->nullable(); // in, out, adjustment, transfer
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('unit_price', 10, 4)->default(0)->nullable();
            $table->decimal('total_amount', 12, 2)->default(0)->nullable();

            // Reference documents
            $table->string('reference_type')->nullable(); // order, purchase, adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable();

            // Add GTN reference if not exists
            if (!Schema::hasColumn('item_transactions', 'gtn_id')) {
                $table->unsignedBigInteger('gtn_id')->nullable()->after('reference_type');
                $table->foreign('gtn_id')->references('gtn_id')->on('gtn_master')->onDelete('set null');
            }

            // Transaction metadata
            $table->text('notes')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            // PostgreSQL JSON for Tailwind CSS UI
            $table->json('metadata')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();

            // Add verification reference
            if (!Schema::hasColumn('item_transactions', 'verified_by')) {
                $table->integer('verified_by')->nullable()->after('created_by_user_id');
            }

            $table->timestamps();

            // PostgreSQL optimized indexes
            $table->index(['organization_id', 'branch_id']);
            $table->index(['inventory_item_id', 'transaction_type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['created_at']);

            $table->softDeletes(); // For soft delete functionality
        });

        // Add foreign key constraints - FIXED table names
        Schema::table('item_transactions', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');

            // FIXED: Use correct table name (plural)
            $table->foreign('inventory_item_id')->references('id')->on('item_master')->onDelete('cascade');
            $table->foreign('item_master_id')->references('id')->on('item_master')->onDelete('set null');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('item_transactions');
    }
};
