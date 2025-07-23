<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Permission Analysis ===\n\n";

// 1. Get permissions from PermissionSystemService
$permissionService = app(\App\Services\PermissionSystemService::class);
$permissionDefinitions = $permissionService->getPermissionDefinitions();

echo "1. PERMISSIONS FROM PermissionSystemService:\n";
$servicePermissions = [];
foreach ($permissionDefinitions as $category => $definition) {
    echo "   Category: {$category}\n";
    foreach ($definition['permissions'] as $permission => $description) {
        $servicePermissions[] = $permission;
        echo "     - {$permission}\n";
    }
}
echo "   Total: " . count($servicePermissions) . " permissions\n\n";

// 2. Get permissions from AdminSidebar component
echo "2. PERMISSIONS FROM AdminSidebar Component:\n";
$sidebarFile = file_get_contents('app/View/Components/AdminSidebar.php');

// Extract permissions from 'permission' => 'permission.name' patterns
preg_match_all("/'permission'\s*=>\s*['\"]([^'\"]+)['\"]/", $sidebarFile, $matches);
$sidebarPermissions = array_unique($matches[1]);

// Extract permissions from hasPermission() calls
preg_match_all("/hasPermission\([^,]+,\s*['\"]([^'\"]+)['\"]/", $sidebarFile, $matches2);
$hasPermissionCalls = array_unique($matches2[1]);

$allSidebarPermissions = array_unique(array_merge($sidebarPermissions, $hasPermissionCalls));

foreach ($allSidebarPermissions as $permission) {
    echo "   - {$permission}\n";
}
echo "   Total: " . count($allSidebarPermissions) . " permissions\n\n";

// 3. Find mismatches
echo "3. PERMISSION MISMATCHES:\n";

$missingInService = array_diff($allSidebarPermissions, $servicePermissions);
$missingInSidebar = array_diff($servicePermissions, $allSidebarPermissions);

if (!empty($missingInService)) {
    echo "   Permissions used in Sidebar but NOT defined in Service:\n";
    foreach ($missingInService as $permission) {
        echo "     - {$permission}\n";
    }
} else {
    echo "   ✅ All sidebar permissions are defined in service\n";
}

if (!empty($missingInSidebar)) {
    echo "   Permissions defined in Service but NOT used in Sidebar:\n";
    foreach ($missingInSidebar as $permission) {
        echo "     - {$permission}\n";
    }
} else {
    echo "   ✅ All service permissions are used in sidebar\n";
}

// 4. Check database permissions
echo "\n4. PERMISSIONS IN DATABASE:\n";
try {
    $dbPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')
        ->pluck('name')
        ->toArray();
    
    echo "   Total in database: " . count($dbPermissions) . " permissions\n";
    
    $missingInDb = array_diff($servicePermissions, $dbPermissions);
    $extraInDb = array_diff($dbPermissions, $servicePermissions);
    
    if (!empty($missingInDb)) {
        echo "   Permissions defined in Service but NOT in database:\n";
        foreach ($missingInDb as $permission) {
            echo "     - {$permission}\n";
        }
    }
    
    if (!empty($extraInDb)) {
        echo "   Permissions in database but NOT defined in Service:\n";
        foreach ($extraInDb as $permission) {
            echo "     - {$permission}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "   Error checking database: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Update PermissionSystemService to include missing permissions\n";
echo "2. Update AdminSidebar to use consistent permission names\n";
echo "3. Re-run SystemPermissionsSeeder to sync database\n";
echo "4. Test permission filtering in sidebar\n";
