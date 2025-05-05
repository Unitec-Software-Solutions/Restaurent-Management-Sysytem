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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trading_name')->nullable();
            $table->string('registration_number')->unique()->nullable();
            $table->string('tax_number')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('alternative_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner_image')->nullable();
            $table->json('social_media_links')->nullable();
            $table->json('business_hours')->nullable();
            $table->enum('business_type', ['restaurant', 'cafe', 'bar', 'food_truck', 'catering', 'other'])->default('restaurant');
            $table->enum('status', ['pending', 'active', 'suspended', 'inactive'])->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('currency')->default('USD');
            $table->string('language')->default('en');
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create company_settings table for additional configuration
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            
            $table->unique(['company_id', 'key']);
        });

        // Create company_documents table for storing company-related documents
        Schema::create('company_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('document_type');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->text('description')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_documents');
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('companies');
    }
};
