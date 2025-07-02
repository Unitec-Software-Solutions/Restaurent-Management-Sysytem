<?php

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
        try {
            // Check if menu_categories table exists
            if (!Schema::hasTable('menu_categories')) {
                Log::warning('menu_categories table does not exist');
                return;
            }

            Schema::table('menu_categories', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('menu_categories');
                Log::info('Current menu_categories columns:', $existingColumns);
                
                // Add branch_id column as nullable first (we'll make it required later)
                if (!in_array('branch_id', $existingColumns)) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                    Log::info('Added branch_id column (nullable) to menu_categories table');
                }
                
                // Add organization_id column if it doesn't exist (for better data organization)
                if (!in_array('organization_id', $existingColumns)) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('branch_id');
                    Log::info('Added organization_id column to menu_categories table');
                }
                
                // Add sort_order column if it doesn't exist but display_order does
                if (!in_array('sort_order', $existingColumns)) {
                    $table->integer('sort_order')->default(1)->after('description');
                    Log::info('Added sort_order column to menu_categories table');
                }
                
                // Add unicode_name column if it doesn't exist (for multi-language support)
                if (!in_array('unicode_name', $existingColumns)) {
                    $table->string('unicode_name')->nullable()->after('name');
                    Log::info('Added unicode_name column to menu_categories table');
                }
                
                // Add image_url column if it doesn't exist
                if (!in_array('image_url', $existingColumns)) {
                    $table->string('image_url')->nullable()->after('unicode_name');
                    Log::info('Added image_url column to menu_categories table');
                }
                
                // Add settings column if it doesn't exist (PostgreSQL JSON)
                if (!in_array('settings', $existingColumns)) {
                    $table->json('settings')->nullable()->after('image_url');
                    Log::info('Added settings column to menu_categories table');
                }
                
                // Add availability_schedule column if it doesn't exist (PostgreSQL JSON)
                if (!in_array('availability_schedule', $existingColumns)) {
                    $table->json('availability_schedule')->nullable()->after('settings');
                    Log::info('Added availability_schedule column to menu_categories table');
                }
                
                // Add is_featured column if it doesn't exist
                if (!in_array('is_featured', $existingColumns)) {
                    $table->boolean('is_featured')->default(false)->after('is_active');
                    Log::info('Added is_featured column to menu_categories table');
                }
                
                // Add notes column if it doesn't exist
                if (!in_array('notes', $existingColumns)) {
                    $table->text('notes')->nullable()->after('availability_schedule');
                    Log::info('Added notes column to menu_categories table');
                }
                
                // Add soft deletes if it doesn't exist
                if (!in_array('deleted_at', $existingColumns)) {
                    $table->softDeletes();
                    Log::info('Added deleted_at column to menu_categories table');
                }
            });
            
            // Update existing menu categories with required data
            $this->updateExistingMenuCategories();
            
            // Now add constraints and indexes after data is populated
            $this->addConstraintsAndIndexes();
            
        } catch (\Exception $e) {
            Log::error('Error adding missing menu categories columns: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update existing menu categories with default values for PostgreSQL
     */
    private function updateExistingMenuCategories(): void
    {
        try {
            // Get the first branch to assign to existing categories (if any exist)
            $firstBranch = DB::table('branches')->first();
            
            if ($firstBranch) {
                // Update categories without branch_id
                $updated = DB::table('menu_categories')
                    ->whereNull('branch_id')
                    ->update([
                        'branch_id' => $firstBranch->id,
                        'organization_id' => $firstBranch->organization_id
                    ]);
                
                if ($updated > 0) {
                    Log::info("✅ Updated {$updated} menu categories with branch and organization references");
                }
            }
            
            // Copy display_order to sort_order if both columns exist
            $existingColumns = Schema::getColumnListing('menu_categories');
            if (in_array('display_order', $existingColumns) && in_array('sort_order', $existingColumns)) {
                DB::statement('UPDATE menu_categories SET sort_order = display_order WHERE sort_order = 1 AND display_order IS NOT NULL');
                Log::info('✅ Copied display_order values to sort_order');
            }
            
            // Set unicode_name same as name if null
            DB::table('menu_categories')
                ->whereNull('unicode_name')
                ->update(['unicode_name' => DB::raw('name')]);
            
            // Set default settings for PostgreSQL JSON
            $defaultSettings = json_encode([
                'show_in_menu' => true,
                'allow_customization' => false,
                'require_age_verification' => false,
                'tax_applicable' => true,
                'service_charge_applicable' => true
            ]);
            
            DB::table('menu_categories')
                ->whereNull('settings')
                ->update(['settings' => $defaultSettings]);
            
            // Set default availability schedule (24/7 by default)
            $defaultSchedule = json_encode([
                'monday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'tuesday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'wednesday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'thursday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'friday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'saturday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'sunday' => ['open' => '00:00', 'close' => '23:59', 'available' => true]
            ]);
            
            DB::table('menu_categories')
                ->whereNull('availability_schedule')
                ->update(['availability_schedule' => $defaultSchedule]);
            
            Log::info('✅ Updated existing menu categories with default values');
            
        } catch (\Exception $e) {
            Log::warning('Could not update existing menu categories: ' . $e->getMessage());
        }
    }

    /**
     * Add foreign key constraints and indexes after data is populated
     */
    private function addConstraintsAndIndexes(): void
    {
        try {
            Schema::table('menu_categories', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('menu_categories');
                
                // Make branch_id required and add foreign key
                if (in_array('branch_id', $existingColumns)) {
                    // First make sure all records have branch_id
                    $nullBranchCount = DB::table('menu_categories')->whereNull('branch_id')->count();
                    if ($nullBranchCount == 0) {
                        // Change column to NOT NULL
                        DB::statement('ALTER TABLE menu_categories ALTER COLUMN branch_id SET NOT NULL');
                        
                        // Add foreign key constraint
                        $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                        Log::info('✅ Added foreign key constraint for branch_id');
                    } else {
                        Log::warning("⚠️ Cannot make branch_id NOT NULL - {$nullBranchCount} records still have NULL values");
                    }
                }
                
                // Add foreign key for organization_id
                if (in_array('organization_id', $existingColumns)) {
                    $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                    Log::info('✅ Added foreign key constraint for organization_id');
                }
                
                // Add indexes for PostgreSQL performance optimization
                try {
                    $table->index(['branch_id', 'is_active'], 'menu_categories_branch_active_idx');
                    $table->index(['organization_id'], 'menu_categories_org_idx');
                    $table->index(['sort_order'], 'menu_categories_sort_idx');
                    $table->index(['is_featured'], 'menu_categories_featured_idx');
                    Log::info('✅ Added performance indexes to menu_categories table');
                } catch (\Exception $e) {
                    Log::warning('Some indexes already exist or could not be created: ' . $e->getMessage());
                }
            });
            
        } catch (\Exception $e) {
            Log::warning('Could not add constraints and indexes: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        if (Schema::hasTable('menu_categories')) {
            Schema::table('menu_categories', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('menu_categories');
                
                // Drop indexes first
                try {
                    $table->dropIndex('menu_categories_branch_active_idx');
                    $table->dropIndex('menu_categories_org_idx');
                    $table->dropIndex('menu_categories_sort_idx');
                    $table->dropIndex('menu_categories_featured_idx');
                } catch (\Exception $e) {
                    Log::warning('Some indexes could not be dropped: ' . $e->getMessage());
                }
                
                // Drop foreign keys
                try {
                    $table->dropForeign(['branch_id']);
                    $table->dropForeign(['organization_id']);
                } catch (\Exception $e) {
                    Log::warning('Some foreign keys could not be dropped: ' . $e->getMessage());
                }
                
                $columnsToRemove = [
                    'branch_id', 'organization_id', 'sort_order', 'unicode_name', 
                    'image_url', 'settings', 'availability_schedule', 'is_featured', 
                    'notes', 'deleted_at'
                ];
                
                foreach ($columnsToRemove as $column) {
                    if (in_array($column, $existingColumns)) {
                        if ($column === 'deleted_at') {
                            $table->dropSoftDeletes();
                        } else {
                            $table->dropColumn($column);
                        }
                    }
                }
            });
        }
    }
};