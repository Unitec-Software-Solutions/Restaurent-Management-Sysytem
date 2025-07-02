<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            $table->rememberToken();
            $table->timestamps();
            
            // PostgreSQL indexes for performance
            $table->index(['organization_id', 'branch_id']);
            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
