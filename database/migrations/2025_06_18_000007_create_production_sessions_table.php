<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('production_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->string('session_name')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->nullable()->default('scheduled');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('supervisor_user_id')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('production_order_id')->references('id')->on('production_orders');
            $table->foreign('supervisor_user_id')->references('id')->on('users');

            $table->index(['organization_id', 'production_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_sessions');
    }
};
