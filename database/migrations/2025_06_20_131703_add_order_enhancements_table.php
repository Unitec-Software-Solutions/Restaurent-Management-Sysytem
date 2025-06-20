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
        // Add new columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('steward_id')->nullable()->after('branch_id');
            $table->timestamp('order_date')->nullable()->after('customer_phone');
            $table->boolean('kot_generated')->default(false)->after('total');
            $table->boolean('bill_generated')->default(false)->after('kot_generated');
            $table->boolean('stock_deducted')->default(false)->after('bill_generated');
            $table->text('notes')->nullable()->after('stock_deducted');
            $table->timestamp('preparation_started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->foreign('steward_id')->references('id')->on('employees')->onDelete('set null');
        });

        // Add new columns to employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->string('position')->nullable()->after('role');
            $table->decimal('salary', 10, 2)->nullable()->after('position');
            $table->text('notes')->nullable()->after('emergency_contact');
        });

        // Create bills table
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('bill_number')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('organization_id');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('service_charge', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['organization_id', 'generated_at']);
            $table->index(['branch_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['steward_id']);
            $table->dropColumn([
                'steward_id',
                'order_date',
                'kot_generated',
                'bill_generated',
                'stock_deducted',
                'notes',
                'preparation_started_at',
                'ready_at',
                'completed_at',
                'cancelled_at',
                'cancellation_reason'
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['position', 'salary', 'notes']);
        });
    }
};
