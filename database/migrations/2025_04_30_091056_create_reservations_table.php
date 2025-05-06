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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained(); // Can be null for unregistered users
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->foreignId('branch_id')->constrained();
            $table->dateTime('reservation_datetime');
            $table->integer('party_size');
            $table->enum('status', ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no-show'])->default('pending');
            $table->enum('reservation_type', ['online', 'in-call', 'walk-in'])->default('online');
            $table->decimal('reservation_fee', 10, 2)->default(0);
            $table->decimal('cancellation_fee', 10, 2)->default(0);
            $table->text('special_requests')->nullable();
            $table->boolean('is_waitlist')->default(false);
            $table->boolean('notify_when_available')->default(false);
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
        Schema::dropIfExists('reservations');
    }
};
