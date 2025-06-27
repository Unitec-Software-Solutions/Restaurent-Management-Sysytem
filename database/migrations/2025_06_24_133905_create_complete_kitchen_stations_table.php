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
        // Check if table exists and update structure instead of recreating
        if (Schema::hasTable('kitchen_stations')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('kitchen_stations');
                
                // Add missing columns that are expected by models and seeders
                if (!in_array('code', $existingColumns)) {
                    $table->string('code')->unique()->after('name');
                }
                
                if (!in_array('max_capacity', $existingColumns)) {
                    $table->decimal('max_capacity', 8, 2)->nullable()->after('notes');
                }
                
                // Ensure proper indexes exist
                try {
                    // Check if indexes don't exist before adding
                    $indexExists = collect(Schema::getConnection()
                        ->getDoctrineSchemaManager()
                        ->listTableIndexes('kitchen_stations'))
                        ->has('kitchen_stations_branch_id_name_unique');
                        
                    if (!$indexExists) {
                        $table->unique(['branch_id', 'name'], 'kitchen_stations_branch_id_name_unique');
                    }
                } catch (\Exception $e) {
                    // Index might already exist or have different name
                }
                
                // Add performance indexes
                $table->index(['branch_id', 'is_active'], 'kitchen_stations_branch_active_idx');
                $table->index('type', 'kitchen_stations_type_idx');
            });
        } else {
            // Create table if it doesn't exist
            Schema::create('kitchen_stations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->string('name');
                $table->string('code')->unique();
                $table->enum('type', ['cooking', 'prep', 'beverage', 'dessert', 'grill', 'fry', 'bar'])->default('cooking');
                $table->boolean('is_active')->default(true);
                $table->integer('order_priority')->default(1);
                $table->json('printer_config')->nullable();
                $table->text('notes')->nullable();
                $table->decimal('max_capacity', 8, 2)->nullable();
                $table->timestamps();
                
                // Indexes
                $table->unique(['branch_id', 'name']);
                $table->index(['branch_id', 'is_active']);
                $table->index('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kitchen_stations')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('kitchen_stations');
                
                // Remove added columns
                if (in_array('code', $existingColumns)) {
                    $table->dropColumn('code');
                }
                
                if (in_array('max_capacity', $existingColumns)) {
                    $table->dropColumn('max_capacity');
                }
                
                // Drop indexes
                try {
                    $table->dropIndex('kitchen_stations_branch_active_idx');
                    $table->dropIndex('kitchen_stations_type_idx');
                } catch (\Exception $e) {
                    // Indexes might not exist
                }
            });
        }
    }
};
