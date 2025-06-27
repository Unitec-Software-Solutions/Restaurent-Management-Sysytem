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
        Schema::create('restaurant_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            
            // Fee Configuration
            $table->decimal('default_reservation_fee', 8, 2)->default(0);
            $table->decimal('online_reservation_fee', 8, 2)->default(0);
            $table->decimal('phone_reservation_fee', 8, 2)->default(0);
            $table->decimal('walkin_reservation_fee', 8, 2)->default(0);
            
            // Cancellation Fee Rules (JSON format)
            $table->json('cancellation_fee_rules')->nullable();
            
            // Waitlist Settings (for future use if needed)
            $table->integer('max_waitlist_size')->default(50);
            $table->boolean('waitlist_enabled')->default(false);
            
            // Business Rules
            $table->integer('reservation_advance_days')->default(30);
            $table->integer('cancellation_hours_limit')->default(24);
            $table->boolean('require_payment_for_reservation')->default(false);
            
            $table->timestamps();
            
            // Ensure one config per branch (or organization-wide if branch_id is null)
            $table->unique(['organization_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_configs');
    }
};
