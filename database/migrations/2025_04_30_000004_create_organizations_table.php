<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
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
            $table->json('business_hours')->nullable();
            $table->enum('business_type', ['restaurant', 'cafe', 'bar', 'food_truck', 'catering', 'other'])->default('restaurant');
            $table->enum('status', ['pending', 'active', 'suspended', 'inactive'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
<<<<<<< HEAD
            $table->unique('name');
=======
            $table->softDeletes();
>>>>>>> d6cd5ae3ac1bcbf08acf12b5c693b04502ea10be
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizations');
    }
}; 