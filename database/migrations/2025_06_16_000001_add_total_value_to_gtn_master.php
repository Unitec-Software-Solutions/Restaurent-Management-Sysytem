<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gtn_master', function (Blueprint $table) {
            $table->decimal('total_value', 15, 2)->default(0)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('gtn_master', function (Blueprint $table) {
            $table->dropColumn('total_value');
        });
    }
};
