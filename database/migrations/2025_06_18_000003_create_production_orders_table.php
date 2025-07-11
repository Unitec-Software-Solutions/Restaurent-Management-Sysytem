<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('production_requests_master_id')->nullable();
            $table->string('production_order_number')->unique()->nullable();
            $table->date('production_date')->nullable();
            $table->enum('status', ['draft', 'approved', 'in_progress', 'completed', 'cancelled'])->nullable()->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('production_requests_master_id')->references('id')->on('production_requests_master')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
            $table->index(['organization_id', 'production_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
