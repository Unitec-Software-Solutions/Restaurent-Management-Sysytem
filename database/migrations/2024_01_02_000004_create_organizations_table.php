<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('address');
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('trading_name')->nullable();
            $table->string('registration_number')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('alternative_phone')->nullable();
            $table->string('activation_key')->unique();
            $table->boolean('is_active')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->json('business_hours')->nullable(); 
            $table->timestamps();
            $table->softDeletes();

            // PostgreSQL indexes for performance
            $table->index(['is_active']);
            $table->index(['registration_number']);
            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
