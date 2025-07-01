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
        // First, let's check what columns exist in the permissions table
        $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'permissions' ORDER BY ordinal_position");
        
        // Debug: Create a simple log of what we found
        Log::info('Permissions table columns:', array_column($columns, 'column_name'));
        
        // Get existing permissions to see the structure
        $existingPermissions = DB::table('permissions')->limit(5)->get();
        Log::info('Sample permissions:', $existingPermissions->toArray());
        
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
            // Check if it's using spatie/laravel-permission structure
            if (Schema::hasColumn('permissions', 'name') && Schema::hasColumn('permissions', 'guard_name')) {
                // Spatie structure
                DB::table('permissions')->insertOrIgnore([
                    'name' => $permission,
                    'guard_name' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Custom structure
                $data = [
                    'name' => ucwords(str_replace(['.', '_'], ' ', $permission)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Add optional columns if they exist
                if (Schema::hasColumn('permissions', 'description')) {
                    $data['description'] = 'Permission to ' . str_replace('.', ' ', $permission);
                }
                
                if (Schema::hasColumn('permissions', 'category')) {
                    $data['category'] = explode('.', $permission)[0];
                }
                
                DB::table('permissions')->insertOrIgnore($data);
            }
        }
        
        // Assign permissions to admin roles
        $this->assignPermissionsToAdminRoles();
    }
    
    private function assignPermissionsToAdminRoles()
    {
        // Try to assign permissions to admin users/roles
        try {
            // If using spatie/laravel-permission
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
                if ($adminRole) {
                    $permissions = [
                        'inventory.view', 'inventory.manage', 'inventory.create', 'inventory.edit', 'inventory.delete',
                        'suppliers.view', 'suppliers.manage', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
                        'grn.view', 'grn.manage', 'grn.create'
                    ];
                    
                    foreach ($permissions as $permission) {
                        $permissionModel = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
                        if ($permissionModel && !$adminRole->hasPermissionTo($permission)) {
                            $adminRole->givePermissionTo($permission);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not assign permissions to admin role: ' . $e->getMessage());
        }
    }

    public function down()
    {
        $permissionsToRemove = [
            'inventory.view', 'inventory.manage', 'inventory.create', 'inventory.edit', 'inventory.delete',
            'suppliers.view', 'suppliers.manage', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'grn.view', 'grn.manage', 'grn.create'
        ];
        
        DB::table('permissions')->whereIn('name', $permissionsToRemove)->delete();
    }
};
