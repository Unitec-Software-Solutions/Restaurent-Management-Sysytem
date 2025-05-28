<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->string('customer_name')->nullable()->change();
        $table->string('customer_phone')->nullable()->change();
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->string('customer_name')->nullable(false)->change();
        $table->string('customer_phone')->nullable(false)->change();
    });
}
};
