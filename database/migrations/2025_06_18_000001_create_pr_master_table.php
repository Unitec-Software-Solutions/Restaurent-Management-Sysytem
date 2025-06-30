<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_requests_master', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('branch_id');
            $table->date('request_date');
            $table->date('required_date');
            $table->enum('status', ['draft', 'submitted', 'approved', 'in_production', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
            $table->index(['organization_id', 'branch_id', 'request_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_requests_master');
    }
};
