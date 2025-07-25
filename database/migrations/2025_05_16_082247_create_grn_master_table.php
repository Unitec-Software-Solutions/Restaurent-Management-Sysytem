<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_master', function (Blueprint $table) {
            $table->id('grn_id');
            $table->string('grn_number')->nullable();
            $table->foreignId('po_id')->nullable()->constrained('po_master', 'po_id');
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->foreignId('organization_id')->nullable()->constrained('organizations');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->foreignId('received_by_user_id')->nullable();
            $table->foreignId('verified_by_user_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->date('received_date')->nullable();
            $table->string('delivery_note_number')->nullable();
            $table->string('invoice_number')->nullable();
           // $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00)->nullable();
            $table->decimal('grand_discount', 15, 2)->default(0)->after('total_amount')->nullable();
            $table->string('status', 50)->default('Pending')->nullable(); // Pending, Verified, Rejected
            $table->string('payment_status', 50)->default('Pending')->comment('Payment status: Pending, Partial, Paid')->nullable(); // Pending, Verified, Rejected
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->foreignId('created_by')->nullable(); // User who created the GRN (Staff)
            $table->timestamps();
            $table->softDeletes(); // For soft delete functionality
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_master');
    }
};
