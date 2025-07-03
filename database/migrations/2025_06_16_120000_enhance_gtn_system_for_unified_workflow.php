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
        // Update gtn_master table for dual-status tracking
        Schema::table('gtn_master', function (Blueprint $table) {
            // Add dual status fields
            $table->enum('origin_status', ['draft', 'confirmed', 'in_delivery', 'delivered'])
                  ->default('draft')
                  ->after('status');
            $table->enum('receiver_status', ['pending', 'received', 'verified', 'accepted', 'rejected', 'partially_accepted'])
                  ->default('pending')
                  ->after('origin_status');

            // Add workflow tracking
            $table->timestamp('confirmed_at')->nullable()->after('receiver_status');
            $table->timestamp('delivered_at')->nullable()->after('confirmed_at');
            $table->timestamp('received_at')->nullable()->after('delivered_at');
            $table->timestamp('verified_at')->nullable()->after('received_at');
            $table->timestamp('accepted_at')->nullable()->after('verified_at');

            // Add rejection tracking
            $table->text('rejection_reason')->nullable()->after('accepted_at');
            $table->integer('rejected_by')->nullable()->after('rejection_reason');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');

            // Add verification user
            $table->integer('verified_by')->nullable()->after('rejected_at');
            $table->integer('received_by')->nullable()->after('verified_by');
        });

        // Update gtn_items table for acceptance/rejection tracking
        Schema::table('gtn_items', function (Blueprint $table) {
            // Add quantity tracking fields
            $table->decimal('quantity_accepted', 10, 2)->nullable()->after('transfer_quantity');
            $table->decimal('quantity_rejected', 10, 2)->default(0)->after('quantity_accepted');

            // Add item-level rejection tracking
            $table->text('item_rejection_reason')->nullable()->after('notes');
            $table->enum('item_status', ['pending', 'accepted', 'rejected', 'partially_accepted'])
                  ->default('pending')
                  ->after('item_rejection_reason');

            // Add quality check fields
            $table->json('quality_notes')->nullable()->after('item_status');
            $table->integer('inspected_by')->nullable()->after('quality_notes');
            $table->timestamp('inspected_at')->nullable()->after('inspected_by');
        });

        // Ensure item_transactions table has GTN-specific transaction types
        Schema::table('item_transactions', function (Blueprint $table) {
            // Add GTN reference if not exists
            if (!Schema::hasColumn('item_transactions', 'gtn_id')) {
                $table->unsignedBigInteger('gtn_id')->nullable()->after('reference_type');
                $table->foreign('gtn_id')->references('gtn_id')->on('gtn_master')->onDelete('set null');
            }

            // Add verification reference
            if (!Schema::hasColumn('item_transactions', 'verified_by')) {
                $table->integer('verified_by')->nullable()->after('created_by_user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gtn_master', function (Blueprint $table) {
            $table->dropColumn([
                'origin_status',
                'receiver_status',
                'confirmed_at',
                'delivered_at',
                'received_at',
                'verified_at',
                'accepted_at',
                'rejection_reason',
                'rejected_by',
                'rejected_at',
                'verified_by',
                'received_by'
            ]);
        });

        Schema::table('gtn_items', function (Blueprint $table) {
            $table->dropColumn([
                'quantity_accepted',
                'quantity_rejected',
                'item_rejection_reason',
                'item_status',
                'quality_notes',
                'inspected_by',
                'inspected_at'
            ]);
        });

        Schema::table('item_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('item_transactions', 'gtn_id')) {
                $table->dropForeign(['gtn_id']);
                $table->dropColumn('gtn_id');
            }
            if (Schema::hasColumn('item_transactions', 'verified_by')) {
                $table->dropColumn('verified_by');
            }
        });
    }
};
