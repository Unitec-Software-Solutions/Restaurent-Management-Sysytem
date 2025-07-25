<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Updating Missing Permissions ===\n\n";

// Get permissions from AdminSidebar component
$sidebarFile = file_get_contents('app/View/Components/AdminSidebar.php');

// Extract all permission references
preg_match_all("/'permission'\s*=>\s*['\"]([^'\"]+)['\"]/", $sidebarFile, $matches1);
preg_match_all("/hasPermission\([^,]+,\s*['\"]([^'\"]+)['\"]/", $sidebarFile, $matches2);

$sidebarPermissions = array_unique(array_merge($matches1[1], $matches2[1]));

// Get permissions from PermissionSystemService
$permissionService = app(\App\Services\PermissionSystemService::class);
$permissionDefinitions = $permissionService->getPermissionDefinitions();

$servicePermissions = [];
foreach ($permissionDefinitions as $category => $definition) {
    foreach ($definition['permissions'] as $permission => $description) {
        $servicePermissions[] = $permission;
    }
}

// Find missing permissions
$missingInService = array_diff($sidebarPermissions, $servicePermissions);

echo "1. SIDEBAR PERMISSIONS: " . count($sidebarPermissions) . "\n";
foreach ($sidebarPermissions as $perm) {
    echo "   - {$perm}\n";
}

echo "\n2. MISSING IN SERVICE: " . count($missingInService) . "\n";
foreach ($missingInService as $perm) {
    echo "   - {$perm}\n";
}

// Check what permissions are actually missing from database
echo "\n3. CHECKING DATABASE PERMISSIONS:\n";
$dbPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')
    ->pluck('name')
    ->toArray();

$missingInDb = array_diff($sidebarPermissions, $dbPermissions);
$extraInDb = array_diff($dbPermissions, $sidebarPermissions);

if (!empty($missingInDb)) {
    echo "   Permissions used in Sidebar but MISSING from database:\n";
    foreach ($missingInDb as $permission) {
        echo "     - {$permission}\n";
    }
    
    // Create missing permissions
    echo "\n   Creating missing permissions...\n";
    try {
        \Illuminate\Support\Facades\DB::beginTransaction();
        
        foreach ($missingInDb as $permissionName) {
            $permission = \Spatie\Permission\Models\Permission::create([
                'name' => $permissionName,
                'guard_name' => 'admin'
            ]);
            echo "     ✅ Created: {$permissionName} (ID: {$permission->id})\n";
        }
        
        \Illuminate\Support\Facades\DB::commit();
        echo "   ✅ All missing permissions created!\n";
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\DB::rollBack();
        echo "   ❌ Error creating permissions: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✅ All sidebar permissions exist in database\n";
}

// Update Super Admin with any new permissions
echo "\n4. UPDATING SUPER ADMIN WITH NEW PERMISSIONS:\n";
try {
    $superAdmin = \App\Models\Admin::where('is_super_admin', true)->first();
    $superAdminRole = \Spatie\Permission\Models\Role::where('name', 'Super Administrator')
        ->where('guard_name', 'admin')
        ->first();
    
    if ($superAdmin && $superAdminRole) {
        // Get all admin permissions again (including newly created ones)
        $allPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->get();
        
        // Sync all permissions to Super Admin role
        $superAdminRole->syncPermissions($allPermissions);
        
        // Clear cache
        app()['cache']->forget(config('permission.cache.key'));
        
        echo "   ✅ Super Admin role updated with {$allPermissions->count()} permissions\n";
        
        // Verify
        $superAdmin->refresh();
        $permissions = $superAdmin->getAllPermissions();
        echo "   ✅ Super Admin now has {$permissions->count()} permissions\n";
        
    } else {
        echo "   ❌ Super Admin or Super Administrator role not found\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Error updating Super Admin: " . $e->getMessage() . "\n";
}

// Check for common permission patterns that might be missing
echo "\n5. CHECKING FOR COMMON PERMISSION PATTERNS:\n";

$commonPatterns = [
    'settings.view', 'settings.edit', 'settings.manage',
    'settings.payments', 'settings.general',
    'permissions.view', 'permissions.assign',
    'roles.templates', 'modules.create', 'modules.configure',
    'modules.analytics', 'modules.stats',
    'dashboard.view', 'dashboard.manage'
];

$missingCommon = array_diff($commonPatterns, $dbPermissions);
if (!empty($missingCommon)) {
    echo "   Common permissions that might be missing:\n";
    foreach ($missingCommon as $perm) {
        echo "     - {$perm}\n";
    }
    
    // Create these common permissions
    echo "\n   Creating common permissions...\n";
    try {
        \Illuminate\Support\Facades\DB::beginTransaction();
        
        foreach ($missingCommon as $permissionName) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'admin'
            ]);
            echo "     ✅ Created/Found: {$permissionName} (ID: {$permission->id})\n";
        }
        
        \Illuminate\Support\Facades\DB::commit();
        echo "   ✅ Common permissions ensured!\n";
        
        // Update Super Admin role again
        if ($superAdminRole) {
            $allPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->get();
            $superAdminRole->syncPermissions($allPermissions);
            app()['cache']->forget(config('permission.cache.key'));
            echo "   ✅ Super Admin role updated again with {$allPermissions->count()} permissions\n";
        }
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\DB::rollBack();
        echo "   ❌ Error creating common permissions: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✅ All common permissions exist\n";
}

echo "\n=== FINAL VERIFICATION ===\n";
$finalDbCount = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->count();
echo "Total admin permissions in database: {$finalDbCount}\n";

if ($superAdmin) {
    $superAdmin->refresh();
    $finalPermissions = $superAdmin->getAllPermissions();
    echo "Super Admin permissions: {$finalPermissions->count()}\n";
    
    // Test key permissions
    $keyPermissions = ['organizations.view', 'branches.view', 'users.view', 'menus.view', 'settings.view'];
    echo "Testing key permissions:\n";
    foreach ($keyPermissions as $permission) {
        $hasPermission = $superAdmin->hasPermissionTo($permission, 'admin');
        echo "  {$permission}: " . ($hasPermission ? '✅ GRANTED' : '❌ DENIED') . "\n";
    }
}

echo "\n✅ Permission update completed!\n";
