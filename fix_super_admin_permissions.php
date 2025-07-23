<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Super Admin Permissions ===\n\n";

try {
    \Illuminate\Support\Facades\DB::beginTransaction();

    // 1. Get the Super Admin
    $superAdmin = \App\Models\Admin::where('is_super_admin', true)->first();
    if (!$superAdmin) {
        echo "❌ No Super Admin found!\n";
        exit(1);
    }

    echo "1. Found Super Admin: {$superAdmin->name} (ID: {$superAdmin->id})\n";

    // 2. Create or get Super Admin role
    $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate([
        'name' => 'Super Administrator',
        'guard_name' => 'admin'
    ]);

    echo "2. Super Admin role: {$superAdminRole->name} (ID: {$superAdminRole->id})\n";

    // 3. Get all admin permissions
    $allPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->get();
    echo "3. Found {$allPermissions->count()} admin permissions\n";

    // 4. Assign all permissions to Super Admin role
    $superAdminRole->syncPermissions($allPermissions);
    echo "4. ✅ Assigned all permissions to Super Admin role\n";

    // 5. Assign Super Admin role to the admin user
    $superAdmin->syncRoles([$superAdminRole]);
    echo "5. ✅ Assigned Super Admin role to user\n";

    // 6. Clear permission cache
    app()['cache']->forget(config('permission.cache.key'));
    echo "6. ✅ Cleared permission cache\n";

    // 7. Verify the fix
    $superAdmin->refresh();
    $superAdmin->load('roles.permissions');
    
    $roles = $superAdmin->roles;
    $permissions = $superAdmin->getAllPermissions();
    
    echo "\n=== VERIFICATION ===\n";
    echo "Super Admin roles: " . $roles->pluck('name')->implode(', ') . "\n";
    echo "Total permissions: " . $permissions->count() . "\n";
    
    // Test specific permissions
    $testPermissions = ['organizations.view', 'branches.view', 'users.view', 'menus.view', 'orders.view'];
    echo "Testing permissions:\n";
    foreach ($testPermissions as $permission) {
        $hasPermission = $superAdmin->hasPermissionTo($permission, 'admin');
        echo "  {$permission}: " . ($hasPermission ? '✅ GRANTED' : '❌ DENIED') . "\n";
    }

    \Illuminate\Support\Facades\DB::commit();
    echo "\n✅ SUCCESS: Super Admin permissions fixed!\n";

} catch (\Exception $e) {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// 8. Also fix other admin users that might have permission issues
echo "\n=== Checking Other Admin Users ===\n";

try {
    $otherAdmins = \App\Models\Admin::where('is_super_admin', false)
        ->with('roles.permissions')
        ->get();

    foreach ($otherAdmins as $admin) {
        $roles = $admin->roles;
        $permissions = $admin->getAllPermissions();
        
        echo "Admin: {$admin->name} (ID: {$admin->id})\n";
        echo "  Roles: " . ($roles->count() > 0 ? $roles->pluck('name')->implode(', ') : 'None') . "\n";
        echo "  Permissions: " . $permissions->count() . "\n";
        
        // If admin has roles but no permissions, there might be an issue with role-permission sync
        if ($roles->count() > 0 && $permissions->count() == 0) {
            echo "  ⚠️  WARNING: Admin has roles but no permissions - role-permission sync issue\n";
            
            // Try to fix by re-syncing role permissions
            foreach ($roles as $role) {
                $rolePermissions = $role->permissions;
                echo "    Role '{$role->name}' has {$rolePermissions->count()} permissions\n";
            }
        }
    }

} catch (\Exception $e) {
    echo "Error checking other admins: " . $e->getMessage() . "\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Test login with Super Admin\n";
echo "2. Check if sidebar shows all menu items\n";
echo "3. Test accessing different admin sections\n";
echo "4. If issues persist, check middleware and policies\n";
