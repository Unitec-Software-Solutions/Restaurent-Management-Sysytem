<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('good_received_notes', function (Blueprint $table) {
            $table->date('received_date')->nullable()->change();
            $table->time('received_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('good_received_notes', function (Blueprint $table) {
            $table->date('received_date')->nullable(false)->change();
            $table->time('received_time')->nullable(false)->change();
        });
    }
};