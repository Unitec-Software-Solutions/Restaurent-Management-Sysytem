<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('trading_name');
            $table->string('registration_number');
            $table->string('phone');
            $table->string('alternative_phone')->nullable();
            $table->json('business_hours');
            $table->string('business_type');
            $table->string('status');
            $table->unique('registration_number');
        });
    }

    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'trading_name',
                'description',
                'business_hours',
                'business_type',
                'status',
                'is_active'
            ]);
        });
    }
};