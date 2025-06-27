<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations following UI/UX guidelines.
     */
    public function up(): void
    {
        // Only modify if table already exists
        if (Schema::hasTable('modules')) {
            Schema::table('modules', function (Blueprint $table) {
                // Add missing columns if they don't exist
                if (!Schema::hasColumn('modules', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('permissions');
                }
                
                // Ensure proper indexes exist
                try {
                    $table->index('slug', 'modules_slug_idx');
                    $table->index('is_active', 'modules_active_idx');
                    $table->index(['slug', 'is_active'], 'modules_slug_active_idx');
                } catch (\Exception $e) {
                    // Indexes might already exist
                }
            });
        } else {
            // Create table if it doesn't exist
            Schema::create('modules', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->json('permissions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Add indexes for performance
                $table->index('slug');
                $table->index('is_active');
                $table->index(['slug', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table in down method to preserve data
        // Only remove added columns if needed
        if (Schema::hasTable('modules')) {
            Schema::table('modules', function (Blueprint $table) {
                try {
                    $table->dropIndex('modules_slug_idx');
                    $table->dropIndex('modules_active_idx');
                    $table->dropIndex('modules_slug_active_idx');
                } catch (\Exception $e) {
                    // Indexes might not exist
                }
            });
        }
    }
};
