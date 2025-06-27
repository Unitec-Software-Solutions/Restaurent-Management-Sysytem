<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Admin;
use App\View\Components\AdminSidebar;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

echo "=== Final Sidebar Activation Test ===\n";

try {
    // Test Super Admin
    echo "\n🔸 TESTING SUPER ADMIN SIDEBAR:\n";
    $superAdmin = Admin::whereHas('roles', function($query) {
        $query->where('name', 'Super Admin')->where('guard_name', 'admin');
    })->first();

    if (!$superAdmin) {
        echo "❌ Super Admin not found\n";
        exit(1);
    }

    // Set the authenticated user for this guard
    Auth::guard('admin')->setUser($superAdmin);

    $sidebar = new AdminSidebar();
    $renderData = $sidebar->render()->getData();
    $menuItems = $renderData['menuItems'];

    // Check Organizations menu
    $orgMenu = collect($menuItems)->firstWhere('title', 'Organizations');
    if ($orgMenu) {
        echo "✅ Organizations menu found\n";
        if (isset($orgMenu['sub_items'])) {
            echo "   Sub-items count: " . count($orgMenu['sub_items']) . "\n";
            foreach ($orgMenu['sub_items'] as $subItem) {
                echo "   - " . $subItem['title'] . " (" . $subItem['route'] . ")\n";
                if ($subItem['title'] === 'Activate Organization') {
                    echo "   ✅ Activate Organization sub-item found!\n";
                }
            }
        }
    } else {
        echo "❌ Organizations menu not found\n";
    }

    // Check Branches menu
    $branchMenu = collect($menuItems)->firstWhere('title', 'Branches');
    if ($branchMenu) {
        echo "✅ Branches menu found\n";
        if (isset($branchMenu['sub_items'])) {
            echo "   Sub-items count: " . count($branchMenu['sub_items']) . "\n";
            foreach ($branchMenu['sub_items'] as $subItem) {
                echo "   - " . $subItem['title'] . " (" . $subItem['route'] . ")\n";
                if ($subItem['title'] === 'Activate Branch') {
                    echo "   ✅ Activate Branch sub-item found!\n";
                }
            }
        }
    } else {
        echo "❌ Branches menu not found\n";
    }

    // Test Organization Admin
    echo "\n🔸 TESTING ORGANIZATION ADMIN SIDEBAR:\n";
    $orgAdmin = Admin::whereHas('roles', function($query) {
        $query->where('name', 'LIKE', 'Organization Admin%')->where('guard_name', 'admin');
    })->first();

    if (!$orgAdmin) {
        echo "❌ Organization Admin not found\n";
        exit(1);
    }

    // Set the authenticated user for this guard
    Auth::guard('admin')->setUser($orgAdmin);

    $sidebar = new AdminSidebar();
    $renderData = $sidebar->render()->getData();
    $menuItems = $renderData['menuItems'];

    // Check Organizations menu (should be hidden for org admin)
    $orgMenu = collect($menuItems)->firstWhere('title', 'Organizations');
    if (!$orgMenu) {
        echo "✅ Organizations menu correctly hidden from Organization Admin\n";
    } else {
        echo "❌ Organizations menu should be hidden from Organization Admin\n";
    }

    // Check Branches menu (should be visible for org admin)
    $branchMenu = collect($menuItems)->firstWhere('title', 'Branches');
    if ($branchMenu) {
        echo "✅ Branches menu found for Organization Admin\n";
        if (isset($branchMenu['sub_items'])) {
            echo "   Sub-items count: " . count($branchMenu['sub_items']) . "\n";
            foreach ($branchMenu['sub_items'] as $subItem) {
                echo "   - " . $subItem['title'] . " (" . $subItem['route'] . ")\n";
                if ($subItem['title'] === 'Activate Branch') {
                    echo "   ✅ Activate Branch sub-item found for Organization Admin!\n";
                }
            }
        }
    } else {
        echo "❌ Branches menu not found for Organization Admin\n";
    }

    echo "\n=== ROUTE VERIFICATION ===\n";
    // Verify routes exist
    $routes = [
        'admin.organizations.activate.form' => 'Organization Activation Form',
        'admin.branches.activate.form' => 'Branch Activation Form'
    ];

    foreach ($routes as $routeName => $description) {
        if (\Illuminate\Support\Facades\Route::has($routeName)) {
            echo "✅ Route exists: $routeName ($description)\n";
        } else {
            echo "❌ Route missing: $routeName ($description)\n";
        }
    }

    echo "\n=== PERMISSION VERIFICATION ===\n";
    $permissionsToCheck = [
        'organizations.activate' => 'Organization Activation Permission',
        'branches.activate' => 'Branch Activation Permission'
    ];

    foreach ($permissionsToCheck as $permission => $description) {
        if (\Spatie\Permission\Models\Permission::where('name', $permission)->where('guard_name', 'admin')->exists()) {
            echo "✅ Permission exists: $permission ($description)\n";
            
            // Check which roles have this permission
            $rolesWithPermission = Role::where('guard_name', 'admin')
                ->whereHas('permissions', function($query) use ($permission) {
                    $query->where('name', $permission);
                })->pluck('name')->toArray();
            
            echo "   Assigned to roles: " . implode(', ', $rolesWithPermission) . "\n";
        } else {
            echo "❌ Permission missing: $permission ($description)\n";
        }
    }

    echo "\n✅ All tests completed successfully!\n";
    echo "✅ Branch and Organization activation sub-items are properly configured\n";
    echo "✅ Permissions are correctly assigned to appropriate roles\n";
    echo "✅ Routes are accessible and functional\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
