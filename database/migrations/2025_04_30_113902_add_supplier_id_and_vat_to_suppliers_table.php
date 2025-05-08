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
            // Check if columns exist before adding them
            if (!Schema::hasColumn('suppliers', 'supplier_id')) {
                $table->string('supplier_id')->unique()->after('id');
            }
            
            if (!Schema::hasColumn('suppliers', 'name')) {
                $table->string('name');
            }
            
            if (!Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable();
            }
            
            if (!Schema::hasColumn('suppliers', 'phone')) {
                $table->string('phone');
            }
            
            if (!Schema::hasColumn('suppliers', 'email')) {
                $table->string('email')->nullable();
            }
            
            if (!Schema::hasColumn('suppliers', 'address')) {
                $table->string('address')->nullable();
            }
            
            if (!Schema::hasColumn('suppliers', 'is_inactive')) {
                $table->boolean('is_inactive')->default(false);
            }
            
            if (!Schema::hasColumn('suppliers', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            
            if (!Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->softDeletes();
            }
            
            if (!Schema::hasColumn('suppliers', 'created_at') || !Schema::hasColumn('suppliers', 'updated_at')) {
                $table->timestamps();
            }
            
            if (!Schema::hasColumn('suppliers', 'has_vat_registration')) {
                $table->boolean('has_vat_registration')
                      ->default(false)
                      ->after('address');
            }
            
            if (!Schema::hasColumn('suppliers', 'vat_registration_no')) {
                $table->string('vat_registration_no')
                      ->nullable()
                      ->after('has_vat_registration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Only drop columns if they exist
            $columnsToDrop = ['supplier_id', 'vat_registration_no', 'has_vat_registration'];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};