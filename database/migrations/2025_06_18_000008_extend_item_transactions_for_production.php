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
        Schema::table('item_transactions', function (Blueprint $table) {
            // Add new transaction types to enum if not already present
            // Note: In production, you'd want to alter the enum more carefully
            $table->unsignedBigInteger('production_session_id')->nullable()->after('gtn_id');
            $table->unsignedBigInteger('production_order_id')->nullable()->after('production_session_id');
            $table->decimal('waste_quantity', 10, 2)->default(0)->after('damaged_quantity');
            $table->string('waste_reason')->nullable()->after('waste_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            $table->dropColumn(['production_session_id', 'production_order_id', 'waste_quantity', 'waste_reason']);
        });
    }
};
