<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCreatedByUserIdForeignKeyInItemTransactions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['created_by_user_id']);
            // Add new foreign key constraint referencing admins table
            $table->foreign('created_by_user_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            // Drop the foreign key referencing admins
            $table->dropForeign(['created_by_user_id']);
            // Restore the original foreign key constraint referencing users table
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
}
