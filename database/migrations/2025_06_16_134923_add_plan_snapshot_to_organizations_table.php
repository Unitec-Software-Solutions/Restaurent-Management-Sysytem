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
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('plan_name')->nullable();
            $table->decimal('plan_price', 10, 2)->nullable();
            $table->string('plan_currency', 10)->nullable();
            $table->json('plan_modules')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['plan_name', 'plan_price', 'plan_currency', 'plan_modules']);
        });
    }
};
