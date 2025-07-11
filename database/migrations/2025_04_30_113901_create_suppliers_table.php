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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            // Organization FK
            $table->foreignId('organization_id')
                  ->nullable()
                  ->constrained('organizations')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            // Supplier-specific columns
            $table->string('supplier_id')->nullable();
            $table->string('name')->nullable();

            // Company details
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_contact_no')->nullable();
            $table->string('company_secondary_no')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_website')->nullable();

            // Contact person details
            $table->string('contact_person')->nullable();
            $table->string('contact_person_no')->nullable();
            $table->string('contact_person_secondary_no')->nullable();
            $table->string('contact_person_email')->nullable();

            // Supplier-contact information (legacy, for backward compatibility)
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();

            // VAT fields
            $table->boolean('has_vat_registration')->default(false);
            $table->string('vat_registration_no')->nullable();

            // Status flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_inactive')->default(false);

            // Timestamps & soft deletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
