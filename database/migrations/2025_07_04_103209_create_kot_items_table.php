<?php
// filepath: database/migrations/2025_07_04_103209_create_kot_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel + PostgreSQL + Tailwind CSS stack
     */
    public function up(): void
    {
        // Check if table already exists, if so update it instead of creating
        if (Schema::hasTable('kot_items')) {
            Log::info('kot_items table already exists, updating structure...');
            $this->updateExistingTable();
            return;
        }
        
        // Create fresh table if it doesn't exist
        Schema::create('kot_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('kot_id')->constrained('kots')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->foreignId('item_master_id')->nullable()->constrained('item_master')->onDelete('set null');
            
            // Item Details
            $table->string('item_name'); // Store name at time of KOT creation
            $table->text('item_description')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            
            // Preparation Details
            $table->text('special_instructions')->nullable();
            $table->json('customizations')->nullable(); // Size, extras, modifications
            $table->enum('status', ['pending', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('preparation_time')->nullable()->comment('Estimated prep time in minutes');
            
            // Staff Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // PostgreSQL Indexes
            $table->index(['kot_id', 'status']);
            $table->index(['menu_item_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index('assigned_to');
            
            // Ensure unique order items per KOT (from existing migration)
            $table->unique(['kot_id', 'order_item_id']);
        });
        
        Log::info('✅ KOT items table created successfully');
    }

    /**
     * Update existing table structure
     */
    private function updateExistingTable(): void
    {
        Schema::table('kot_items', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('kot_items');
            
            // Add missing columns only if they don't exist
            if (!in_array('item_master_id', $existingColumns)) {
                $table->foreignId('item_master_id')->nullable()->after('menu_item_id')
                      ->constrained('item_master')->onDelete('set null');
                Log::info('Added item_master_id column to kot_items');
            }
            
            if (!in_array('item_name', $existingColumns)) {
                $table->string('item_name')->after('item_master_id');
                Log::info('Added item_name column to kot_items');
            }
            
            if (!in_array('item_description', $existingColumns)) {
                $table->text('item_description')->nullable()->after('item_name');
                Log::info('Added item_description column to kot_items');
            }
            
            if (!in_array('unit_price', $existingColumns)) {
                $table->decimal('unit_price', 10, 2)->after('quantity');
                Log::info('Added unit_price column to kot_items');
            }
            
            if (!in_array('customizations', $existingColumns)) {
                $table->json('customizations')->nullable()->after('special_instructions');
                Log::info('Added customizations column to kot_items');
            }
            
            if (!in_array('priority', $existingColumns)) {
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('status');
                Log::info('Added priority column to kot_items');
            }
            
            if (!in_array('preparation_time', $existingColumns)) {
                $table->integer('preparation_time')->nullable()->after('completed_at');
                Log::info('Added preparation_time column to kot_items');
            }
            
            if (!in_array('assigned_to', $existingColumns)) {
                $table->foreignId('assigned_to')->nullable()->after('preparation_time')
                      ->constrained('users')->onDelete('set null');
                Log::info('Added assigned_to column to kot_items');
            }
            
            if (!in_array('completed_by', $existingColumns)) {
                $table->foreignId('completed_by')->nullable()->after('assigned_to')
                      ->constrained('users')->onDelete('set null');
                Log::info('Added completed_by column to kot_items');
            }
        });
        
        // Add indexes if they don't exist
        try {
            Schema::table('kot_items', function (Blueprint $table) {
                $table->index(['menu_item_id', 'status']);
                $table->index(['status', 'priority']);
                $table->index('assigned_to');
            });
            Log::info('Added missing indexes to kot_items table');
        } catch (\Exception $e) {
            Log::debug('Some indexes might already exist: ' . $e->getMessage());
        }
        
        Log::info('✅ KOT items table updated successfully');
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        // Use CASCADE for PostgreSQL compatibility
        DB::statement('DROP TABLE IF EXISTS kot_items CASCADE');
        Log::info('KOT items table dropped with CASCADE');
    }
};
