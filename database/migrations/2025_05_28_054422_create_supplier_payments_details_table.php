<?php
// File: [timestamp]_create_supplier_payments_details_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('supp_payments_details', function (Blueprint $table) {
            $table->id();
            
            // Master payment relationship
            $table->foreignId('payment_master_id')
                ->constrained('supp_payments_master')
                ->onDelete('cascade');

            // Payment method details
            $table->enum('method_type', [
                'cash',
                'cheque', 
                'bank_transfer',
                'bank_deposit',
                'credit_card',
                'digital_wallet',
                'other'
            ])->default('cash');

            $table->decimal('amount', 16, 2);
            $table->string('reference_number')->nullable()->unique();
            $table->date('value_date')->nullable();
            
            // Cheque details
            $table->string('cheque_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->date('cheque_date')->nullable();
            
            // Bank transfer details
            $table->string('transaction_id')->nullable();
            $table->string('bank_reference')->nullable();
            
            // Installment tracking
            $table->unsignedSmallInteger('installment_number')->nullable();
            $table->date('due_date')->nullable();

            // Additional metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['payment_master_id', 'method_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('supp_payments_details');
    }
};