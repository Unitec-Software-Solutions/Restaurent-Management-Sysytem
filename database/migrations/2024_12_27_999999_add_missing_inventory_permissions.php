<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel with PostgreSQL and Tailwind CSS stack
     */
    public function up(): void
    {
        // Check if permissions table exists first
        if (!Schema::hasTable('permissions')) {
            Log::info('Permissions table does not exist yet. Skipping inventory permissions migration.');
            return;
        }

        try {
            // Get actual table columns to work with existing structure
            $columns = Schema::getColumnListing('permissions');
            Log::info('Permissions table columns found:', $columns);

            // Check if this is Spatie Permission structure (has guard_name)
            $isSpatieStructure = in_array('guard_name', $columns);
            
            // Define permissions with descriptions
            $permissionsData = [
                'inventory.view' => 'View inventory items and stock levels',
                'inventory.manage' => 'Manage inventory items and stock',
                'inventory.create' => 'Create new inventory items',
                'inventory.edit' => 'Edit inventory items',
                'inventory.delete' => 'Delete inventory items',
                'suppliers.view' => 'View suppliers',
                'suppliers.manage' => 'Manage suppliers',
                'suppliers.create' => 'Create new suppliers',
                'suppliers.edit' => 'Edit suppliers',
                'suppliers.delete' => 'Delete suppliers',
                'grn.view' => 'View Goods Receipt Notes',
                'grn.manage' => 'Manage Goods Receipt Notes',
                'grn.create' => 'Create new GRNs',
            ];

            Log::info('Processing permissions', [
                'is_spatie_structure' => $isSpatieStructure,
                'permissions_count' => count($permissionsData)
            ]);

            foreach ($permissionsData as $slug => $description) {
                $this->createPermissionSafely($slug, $description, $columns, $isSpatieStructure);
            }

            Log::info('Successfully processed inventory permissions');

        } catch (\Exception $e) {
            Log::error('Error adding inventory permissions: ' . $e->getMessage());
            // Don't throw to prevent migration failure
        }
    }

    /**
     * Create permission safely based on actual table structure
     */
    private function createPermissionSafely(string $slug, string $description, array $columns, bool $isSpatieStructure): void
    {
        try {
            if ($isSpatieStructure) {
                // Spatie Permission structure - use slug as name
                $exists = DB::table('permissions')
                    ->where('name', $slug)
                    ->where('guard_name', 'admin')
                    ->exists();

                if (!$exists) {
                    $data = [
                        'name' => $slug,
                        'guard_name' => 'admin',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    DB::table('permissions')->insert($data);
                    Log::info("Created Spatie permission: {$slug}");
                }
            } else {
                // Custom permission structure - adapt to existing columns
                $permissionName = ucwords(str_replace(['.', '_'], ' ', $slug));
                
                $exists = DB::table('permissions')
                    ->where('name', $permissionName)
                    ->exists();

                if (!$exists) {
                    // Build data array based on available columns
                    $data = [
                        'name' => $permissionName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Only add columns that actually exist in the table
                    if (in_array('slug', $columns)) {
                        $data['slug'] = $slug;
                    }

                    if (in_array('description', $columns)) {
                        $data['description'] = $description;
                    }

                    if (in_array('category', $columns)) {
                        $data['category'] = explode('.', $slug)[0];
                    }

                    if (in_array('module_slug', $columns)) {
                        $data['module_slug'] = explode('.', $slug)[0];
                    }

                    if (in_array('is_active', $columns)) {
                        $data['is_active'] = true;
                    }

                    if (in_array('sort_order', $columns)) {
                        $data['sort_order'] = 0;
                    }

                    DB::table('permissions')->insert($data);
                    Log::info("Created custom permission: {$permissionName} (slug: {$slug})");
                }
            }

        } catch (\Exception $e) {
            Log::warning("Failed to create permission {$slug}: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migration for Laravel with PostgreSQL
     */
    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        try {
            $permissionSlugs = [
                'inventory.view', 'inventory.manage', 'inventory.create', 
                'inventory.edit', 'inventory.delete',
                'suppliers.view', 'suppliers.manage', 'suppliers.create', 
                'suppliers.edit', 'suppliers.delete',
                'grn.view', 'grn.manage', 'grn.create'
            ];

            // Check table structure
            $columns = Schema::getColumnListing('permissions');
            $isSpatieStructure = in_array('guard_name', $columns);
            
            if ($isSpatieStructure) {
                // Spatie structure - delete by slug name
                $deleted = DB::table('permissions')
                    ->whereIn('name', $permissionSlugs)
                    ->where('guard_name', 'admin')
                    ->delete();
                    
                Log::info("Removed {$deleted} Spatie permissions");
            } else {
                // Custom structure - delete by formatted names
                $permissionNames = array_map(function($slug) {
                    return ucwords(str_replace(['.', '_'], ' ', $slug));
                }, $permissionSlugs);
                
                $deleted = DB::table('permissions')
                    ->whereIn('name', $permissionNames)
                    ->delete();
                    
                Log::info("Removed {$deleted} custom permissions");
            }

        } catch (\Exception $e) {
            Log::error('Error removing inventory permissions: ' . $e->getMessage());
        }
    }
};
