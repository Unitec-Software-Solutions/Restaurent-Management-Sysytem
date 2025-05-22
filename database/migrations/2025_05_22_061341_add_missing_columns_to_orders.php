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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('takeaway_id')->nullable()->unique();
            $table->dateTime('order_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('takeaway_id');
            $table->dropColumn('order_time');
            $table->dropColumn('scheduled_time');
        });
    }
};
