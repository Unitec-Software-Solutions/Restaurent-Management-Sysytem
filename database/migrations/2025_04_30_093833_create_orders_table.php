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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            
            // Customer Information
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            
            // Order Details
            $table->enum('order_type', ['dine_in', 'takeaway', 'delivery', 'reservation'])->default('dine_in');
            $table->enum('status', [
                'pending', 'confirmed', 'preparing', 'ready', 
                'completed', 'cancelled', 'refunded'
            ])->default('pending');
            
            // Pricing
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            
            // Payment
            $table->enum('payment_status', ['pending', 'paid', 'partially_paid', 'refunded'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'mobile', 'bank_transfer', 'other'])->nullable();
            $table->string('payment_reference')->nullable();
            
            // Location & Timing
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->nullable()->constrained()->onDelete('set null');
            
            // Order Timing
            $table->timestamp('order_date');
            $table->timestamp('requested_time')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Delivery Information
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->text('delivery_instructions')->nullable();
            
            // Additional Info
            $table->text('special_instructions')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // For additional flexible data
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['organization_id', 'status']);
            $table->index(['branch_id', 'order_date']);
            $table->index(['user_id', 'status']);
            $table->index(['payment_status', 'order_date']);
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
