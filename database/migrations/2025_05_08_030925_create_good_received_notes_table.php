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
        Schema::create('good_received_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->date('grn_date')->after('grn_number');
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('purchase_order_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->string('supplier_code')->after('supplier_id');
            $table->foreignId('received_by')->references('id')->on('users');
            $table->foreignId('checked_by')->nullable()->references('id')->on('users');
            $table->date('received_date');
            $table->time('received_time');
            $table->string('delivery_note_number')->nullable();
            $table->string('supplier_invoice_number')->nullable();
            $table->string('supplier_invoice_no')->nullable()->after('supplier_invoice_number');
            $table->string('description')->nullable()->after('supplier_invoice_number');
            $table->enum('status', ['pending', 'received', 'checked', 'completed', 'discrepancy', 'partially_checked', 'rejected'])->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('payable_amount', 10, 2)->default(0)->after('tax_amount');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('payable_amount');
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable()->after('notes');
            $table->text('rejection_reason')->nullable();
            $table->boolean('has_discrepancy')->default(false);
            $table->text('discrepancy_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->after('is_active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_received_notes');
    }
};