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
        Schema::table('suppliers', function (Blueprint $table) {
            // Unique supplier code
            $table->string('supplier_id')->unique()->after('id');
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_inactive')->default(false);    
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // Adds deleted_at column
            $table->timestamps();

            // Do they have a VAT reg. no?
            $table->boolean('has_vat_registration')
                  ->default(false)
                  ->after('address');

            // VAT registration number, only when has_vat_registration = true
            $table->string('vat_registration_no')
                  ->nullable()
                  ->after('has_vat_registration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['supplier_id', 'vat_registration_no', 'has_vat_registration']);
        });
    }
};
