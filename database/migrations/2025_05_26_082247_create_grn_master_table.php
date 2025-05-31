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
            $table->string('grn_number')->unique();
            $table->foreignId('po_id')->nullable()->constrained('po_master', 'po_id');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->foreignId('received_by_user_id')->constrained('users');
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users');
            $table->date('received_date');
            $table->string('delivery_note_number')->nullable();
            $table->string('invoice_number')->nullable();
           // $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('status', 50)->default('Pending'); // Pending, Verified, Rejected
            $table->string('payment_status', 50)->default('Pending')->comment('Payment status: Pending, Partial, Paid'); // Pending, Verified, Rejected
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->nullable(); // User who created the GRN (Staff)
            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_master');
    }
};
