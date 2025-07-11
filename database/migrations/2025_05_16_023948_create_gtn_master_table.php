<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gtn_master', function (Blueprint $table) {
            $table->id('gtn_id');

            // References
            $table->string('gtn_number')->unique();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches');
            $table->foreignId('to_branch_id')->nullable()->constrained('branches');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained('organizations');

            // Dates & Status
            $table->date('transfer_date');
            $table->string('status', 50)->nullable()->default('Pending'); // Pending, Confirmed, Approved, Verified, Completed, Cancelled

            // Dual status fields
            $table->enum('origin_status', ['draft', 'confirmed', 'in_delivery', 'delivered'])
                  ->default('draft')
                  ->after('status');
            $table->enum('receiver_status', ['pending', 'received', 'verified', 'accepted', 'rejected', 'partially_accepted'])
                  ->default('pending')
                  ->after('origin_status');

            // Workflow tracking
            $table->timestamp('confirmed_at')->nullable()->after('receiver_status');
            $table->timestamp('delivered_at')->nullable()->after('confirmed_at');
            $table->timestamp('received_at')->nullable()->after('delivered_at');
            $table->timestamp('verified_at')->nullable()->after('received_at');
            $table->timestamp('accepted_at')->nullable()->after('verified_at');

            // Rejection tracking
            $table->text('rejection_reason')->nullable()->after('accepted_at');
            $table->integer('rejected_by')->nullable()->after('rejection_reason');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');

            // Verification user
            $table->integer('verified_by')->nullable()->after('rejected_at');
            $table->integer('received_by')->nullable()->after('verified_by');

            // Notes
            $table->text('notes')->nullable();
            $table->decimal('total_value', 15, 2)->nullable()->default(0);
            $table->boolean('is_active')->nullable()->default(true);

            $table->timestamps();
            $table->softDeletes(); // For soft delete functionality
        });

        // Add comment to clarify status options using PostgreSQL-compatible syntax
        if (config('database.default') === 'pgsql') {
            DB::statement("COMMENT ON COLUMN gtn_master.status IS 'Pending, Confirmed, Approved, Verified, Completed, Cancelled'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gtn_master');
    }
};
