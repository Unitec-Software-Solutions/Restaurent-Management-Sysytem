<?php
// File: [timestamp]_create_supplier_payments_master_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('supp_payments_master', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->onDelete('set null');

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->onDelete('cascade');

            // Core payment info
            $table->string('payment_number')->unique();
            $table->date('payment_date');
            $table->decimal('total_amount', 16, 2);
            $table->decimal('allocated_amount', 16, 2)->default(0);
            $table->enum('currency', ['LKR'])->default('LKR');
            
            // Status tracking
            $table->enum('payment_status', [
                'draft',
                'approved',
                'partial',
                'completed',
                'reversed'
            ])->default('draft');

            // Audit fields
            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['supplier_id', 'payment_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('supp_payments_master');
    }
};