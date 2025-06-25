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
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Add missing columns that the controller expects
            $table->integer('max_branches')->nullable()->after('description')->comment('Maximum branches allowed (null = unlimited)');
            $table->integer('max_employees')->nullable()->after('max_branches')->comment('Maximum employees allowed (null = unlimited)');
            $table->json('features')->nullable()->after('max_employees')->comment('Additional features list');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['max_branches', 'max_employees', 'features']);
        });
    }
};
