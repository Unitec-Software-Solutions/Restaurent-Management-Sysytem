<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add organization_id to tables table
        if (Schema::hasTable('tables') && !Schema::hasColumn('tables', 'organization_id')) {
            Schema::table('tables', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
            });
            
            // Update existing tables with their branch's organization_id
            DB::statement('UPDATE tables SET organization_id = (SELECT organization_id FROM branches WHERE branches.id = tables.branch_id) WHERE organization_id IS NULL');
            
            // Now make it non-nullable and add foreign key
            Schema::table('tables', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable(false)->change();
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            });
        }

        // Add phone_number to users table if it doesn't exist
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'phone_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone_number')->nullable()->after('email');
            });
        }

        // Remove the incorrect 'phone' column from users table if it exists and phone_number also exists
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone') && Schema::hasColumn('users', 'phone_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tables') && Schema::hasColumn('tables', 'organization_id')) {
            Schema::table('tables', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone_number');
            });
        }
    }
};
