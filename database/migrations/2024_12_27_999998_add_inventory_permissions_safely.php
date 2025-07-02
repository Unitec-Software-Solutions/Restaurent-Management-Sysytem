<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        // Check if permissions table exists first
        if (!Schema::hasTable('permissions')) {
            Log::info('Permissions table does not exist yet. Skipping inventory permissions migration.');
            return;
        }

        try {
            // Check what columns exist in the permissions table
            $tableExists = Schema::hasTable('permissions');
            
            if (!$tableExists) {
                Log::info('Permissions table not found. This migration will be skipped.');
                return;
            }

            // Check if this is Spatie Permission structure
            $isSpatieStructure = Schema::hasColumn('permissions', 'guard_name');
            
            Log::info('Adding inventory permissions', [
                'table_exists' => $tableExists,
                'is_spatie_structure' => $isSpatieStructure
            ]);

            // Add basic inventory permissions using the actual table structure
            $permissionsToAdd = [
                'inventory.view',
                'inventory.manage', 
                'inventory.create',
                'inventory.edit',
                'inventory.delete',
                'suppliers.view',
                'suppliers.manage',
                'suppliers.create', 
                'suppliers.edit',
                'suppliers.delete',
                'grn.view',
                'grn.manage',
                'grn.create'
            ];
            
            foreach ($permissionsToAdd as $permission) {
                $this->createPermissionSafely($permission, $isSpatieStructure);
            }
            
            // Assign permissions to admin roles if possible
            $this->assignPermissionsToAdminRoles();
            
            Log::info('Successfully added inventory permissions');

        } catch (\Exception $e) {
            Log::error('Error adding inventory permissions: ' . $e->getMessage());
            // Don't throw the exception to prevent migration failure
        }
    }

    /**
     * Create permission safely based on table structure
     */
    private function createPermissionSafely(string $permission, bool $isSpatieStructure): void
    {
        try {
            if ($isSpatieStructure) {
                // Spatie Permission structure
                $exists = DB::table('permissions')
                    ->where('name', $permission)
                    ->where('guard_name', 'admin')
                    ->exists();

                if (!$exists) {
                    DB::table('permissions')->insert([
                        'name' => $permission,
                        'guard_name' => 'admin',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    Log::info("Created Spatie permission: {$permission}");
                }
            } else {
                // Custom permission structure
                $exists = DB::table('permissions')
                    ->where('name', ucwords(str_replace(['.', '_'], ' ', $permission)))
                    ->exists();

                if (!$exists) {
                    $data = [
                        'name' => ucwords(str_replace(['.', '_'], ' ', $permission)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    // Add optional columns if they exist
                    if (Schema::hasColumn('permissions', 'slug')) {
                        $data['slug'] = $permission;
                    }
                    
                    if (Schema::hasColumn('permissions', 'description')) {
                        $data['description'] = 'Permission to ' . str_replace(['.', '_'], ' ', $permission);
                    }
                    
                    if (Schema::hasColumn('permissions', 'category')) {
                        $data['category'] = explode('.', $permission)[0];
                    }

                    if (Schema::hasColumn('permissions', 'module_slug')) {
                        $data['module_slug'] = explode('.', $permission)[0];
                    }
                    
                    DB::table('permissions')->insert($data);
                    
                    Log::info("Created custom permission: {$permission}");
                }
            }
        } catch (\Exception $e) {
            Log::warning("Failed to create permission {$permission}: " . $e->getMessage());
        }
    }
    
    /**
     * Assign permissions to admin roles safely
     */
    private function assignPermissionsToAdminRoles(): void
    {
        try {
            // Check if we're using Spatie Permission package
            if (!class_exists(\Spatie\Permission\Models\Permission::class)) {
                Log::info('Spatie Permission package not available');
                return;
            }

            // Check if roles table exists
            if (!Schema::hasTable('roles')) {
                Log::info('Roles table not found');
                return;
            }

            $permissions = [
                'inventory.view', 'inventory.manage', 'inventory.create', 
                'inventory.edit', 'inventory.delete',
                'suppliers.view', 'suppliers.manage', 'suppliers.create', 
                'suppliers.edit', 'suppliers.delete',
                'grn.view', 'grn.manage', 'grn.create'
            ];
            
            // Find admin roles and assign permissions
            $adminRoles = \Spatie\Permission\Models\Role::where('guard_name', 'admin')
                ->whereIn('name', ['Super Admin', 'Admin', 'Organization Admin'])
                ->get();

            foreach ($adminRoles as $role) {
                foreach ($permissions as $permissionName) {
                    try {
                        $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)
                            ->where('guard_name', 'admin')
                            ->first();
                        
                        if ($permission && !$role->hasPermissionTo($permissionName)) {
                            $role->givePermissionTo($permissionName);
                            Log::info("Assigned {$permissionName} to {$role->name}");
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to assign {$permissionName} to {$role->name}: " . $e->getMessage());
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::warning('Could not assign permissions to admin roles: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        try {
            $permissionsToRemove = [
                'inventory.view', 'inventory.manage', 'inventory.create', 
                'inventory.edit', 'inventory.delete',
                'suppliers.view', 'suppliers.manage', 'suppliers.create', 
                'suppliers.edit', 'suppliers.delete',
                'grn.view', 'grn.manage', 'grn.create'
            ];
            
            // Check if Spatie structure
            if (Schema::hasColumn('permissions', 'guard_name')) {
                DB::table('permissions')
                    ->whereIn('name', $permissionsToRemove)
                    ->where('guard_name', 'admin')
                    ->delete();
            } else {
                // Custom structure - remove by name
                $customNames = array_map(function($perm) {
                    return ucwords(str_replace(['.', '_'], ' ', $perm));
                }, $permissionsToRemove);
                
                DB::table('permissions')
                    ->whereIn('name', $customNames)
                    ->delete();
            }
            
            Log::info('Removed inventory permissions');
            
        } catch (\Exception $e) {
            Log::error('Error removing inventory permissions: ' . $e->getMessage());
        }
    }
};
