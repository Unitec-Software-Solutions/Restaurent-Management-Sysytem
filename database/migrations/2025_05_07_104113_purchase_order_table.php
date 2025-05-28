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
        Schema::create('po_master', function (Blueprint $table) {
            $table->id('po_id');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->string('manual_supplier_name')->nullable(); // Optional manual supplier entry
            $table->foreignId('user_id')->constrained('users');
            $table->string('po_number')->unique();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->string('status', 50)->default('Pending'); // Suggest linking to lookup table in the future
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->string('payment_method', 50)->nullable(); // Suggest linking to payment method table in future
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // For soft delete functionality
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_master');
    }
};