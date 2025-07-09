<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_requests_master', function (Blueprint $table) {
            $table->unsignedBigInteger('production_order_id')->nullable()->after('approved_at');
            $table->foreign('production_order_id')->references('id')->on('production_orders')->nullOnDelete()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('production_requests_master', function (Blueprint $table) {
            $table->dropForeign(['production_order_id']);
            $table->dropColumn('production_order_id');
        });
    }
};
