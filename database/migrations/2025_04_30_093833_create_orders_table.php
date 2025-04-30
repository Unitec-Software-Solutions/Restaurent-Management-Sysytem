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
            $table->foreignId('user_id')->nullable()->constrained(); // Can be null for unregistered users
            $table->foreignId('reservation_id')->nullable()->constrained(); // Nullable for takeaway orders
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->enum('order_type', [
                'takeaway_in_call_scheduled',
                'takeaway_online_scheduled',
                'takeaway_walk_in_scheduled',
                'takeaway_walk_in_demand',
                'dine_in_online_scheduled',
                'dine_in_in_call_scheduled',
                'dine_in_walk_in_scheduled',
                'dine_in_walk_in_demand'
            ]);
            $table->enum('status', ['active', 'preparing', 'ready', 'served', 'completed', 'cancelled'])->default('active');
            $table->dateTime('scheduled_time')->nullable();
            $table->integer('estimated_preparing_time')->default(0); // In minutes
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('waiter_id')->nullable()->references('id')->on('users');
            $table->foreignId('cashier_id')->nullable()->references('id')->on('users');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
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
