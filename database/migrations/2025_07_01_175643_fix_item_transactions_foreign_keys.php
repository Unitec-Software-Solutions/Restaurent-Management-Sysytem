<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations to fix foreign key references for Laravel + PostgreSQL + Tailwind CSS
     */
    public function up(): void
    {
        // First, check what table actually exists
        $itemMasterTableExists = Schema::hasTable('item_masters');
        $itemMastersTableExists = Schema::hasTable('item_masters');
        
        if (!$itemMasterTableExists && !$itemMastersTableExists) {
            throw new \Exception('Neither item_masters nor item_masters table exists. Please run item master migration first.');
        }
        
        // Determine which table name to use
        $correctTableName = $itemMastersTableExists ? 'item_masters' : 'item_masters';
        
        // Drop existing foreign key constraints if they exist
        Schema::table('item_transactions', function (Blueprint $table) {
            try {
                $table->dropForeign(['inventory_item_id']);
            } catch (Exception $e) {
                // Foreign key might not exist yet
            }
            
            try {
                $table->dropForeign(['item_masters_id']);
            } catch (Exception $e) {
                // Foreign key might not exist yet
            }
        });
        
        // Add corrected foreign key constraints
        Schema::table('item_transactions', function (Blueprint $table) use ($correctTableName) {
            // Add item_masters_id column if it doesn't exist
            if (!Schema::hasColumn('item_transactions', 'item_masters_id')) {
                $table->unsignedBigInteger('item_masters_id')->nullable()->after('inventory_item_id');
            }
            
            // Add foreign key constraints with correct table name
            $table->foreign('inventory_item_id')->references('id')->on($correctTableName)->onDelete('cascade');
            $table->foreign('item_masters_id')->references('id')->on($correctTableName)->onDelete('set null');
        });
        
        // Update ItemMaster model table reference if needed
        if ($correctTableName === 'item_masters') {
            // Log that we're using singular table name
            Log::info("Using singular table name 'item_masters' for foreign key references");
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->dropForeign(['item_masters_id']);
        });
    }
};
