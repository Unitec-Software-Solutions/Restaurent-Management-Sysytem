<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{

    public function up(): void
    {
        // Use CASCADE to properly drop the table with all dependencies
        DB::statement('DROP TABLE IF EXISTS kitchen_stations CASCADE');
        
        Log::info('Dropped kitchen_stations table with CASCADE');
        
        // Now create the table fresh
        Schema::create('kitchen_stations', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('station_code')->unique();
            
            // Station Type with enum matching controller validation
            $table->enum('station_type', [
                'hot_kitchen', 
                'cold_kitchen', 
                'grill', 
                'prep', 
                'dessert', 
                'serving', 
                'other'
            ])->nullable();
            
            // Foreign Keys
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            
            // Location and Physical Details
            $table->string('location')->nullable();
            $table->text('equipment')->nullable()->comment('Equipment available at this station');
            
            // Capacity and Operations
            $table->integer('capacity')->nullable()->comment('Number of staff/orders this station can handle');
            $table->integer('priority_order')->default(1)->comment('Lower numbers get higher priority');
            $table->decimal('max_capacity', 8, 2)->nullable()->comment('Maximum operational capacity');
            $table->integer('max_concurrent_orders')->default(5)->comment('Max orders this station can handle simultaneously');
            
            // Status and Settings
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_assign_kots')->default(false)->comment('Automatically assign KOTs to this station');
            
            // Legacy Support (from existing migrations)
            $table->enum('type', ['cooking', 'prep', 'beverage', 'dessert', 'grill', 'fry', 'bar'])->default('cooking')->nullable();
            $table->integer('order_priority')->default(1)->nullable();
            $table->string('code')->nullable(); // Alternative to station_code for backward compatibility
            
            // Configuration and Metadata
            $table->json('printer_config')->nullable()->comment('Printer configuration for KOTs');
            $table->json('settings')->nullable()->comment('Additional station settings');
            $table->json('ui_metadata')->nullable()->comment('UI specific configuration data');
            $table->text('notes')->nullable();
            
            // Timestamps and Soft Deletes
            $table->timestamps();
            $table->softDeletes();
            
            // PostgreSQL Indexes for Performance
            $table->index(['branch_id', 'is_active']);
            $table->index(['organization_id', 'is_active']);
            $table->index(['priority_order', 'order_priority']);
            $table->index(['station_type', 'is_active']);
            $table->index('station_code');
            
            // Unique constraints
            $table->unique(['branch_id', 'name']);
        });
        
        Log::info('Kitchen stations table created successfully with all required columns');
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        // Use CASCADE for safe dropping
        DB::statement('DROP TABLE IF EXISTS kitchen_stations CASCADE');
        Log::info('Kitchen stations table dropped with CASCADE');
    }
};
