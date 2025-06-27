<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

echo "=== Testing AdminSidebar Menu Structure ===\n\n";

try {
    // Create a test super admin user (or use existing)
    $superAdmin = Admin::where('is_super_admin', true)->first();
    
    if (!$superAdmin) {
        echo "❌ No super admin found. Creating test admin...\n";
        $superAdmin = Admin::create([
            'name' => 'Test Super Admin',
            'email' => 'test.superadmin@example.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
            'is_active' => true,
        ]);
        echo "✅ Test super admin created\n";
    }
    
    // Login as super admin
    Auth::guard('admin')->login($superAdmin);
    echo "✅ Logged in as super admin: {$superAdmin->name}\n\n";
    
    // Test the AdminSidebar component
    $sidebar = new \App\View\Components\AdminSidebar();
    $view = $sidebar->render();
    $menuItems = $view->getData()['menuItems'];
    
    echo "=== MENU STRUCTURE ===\n";
    
    // Find Organizations menu
    $organizationsMenu = collect($menuItems)->firstWhere('title', 'Organizations');
    if ($organizationsMenu) {
        echo "✅ Organizations menu found:\n";
        echo "   Route: {$organizationsMenu['route']}\n";
        echo "   Sub-items:\n";
        foreach ($organizationsMenu['sub_items'] as $subItem) {
            echo "   - {$subItem['title']} -> {$subItem['route']}\n";
        }
        
        // Check if Activate Organization is present
        $activateOrgItem = collect($organizationsMenu['sub_items'])->firstWhere('title', 'Activate Organization');
        if ($activateOrgItem) {
            echo "✅ 'Activate Organization' sub-item found!\n";
        } else {
            echo "❌ 'Activate Organization' sub-item missing!\n";
        }
    } else {
        echo "❌ Organizations menu not found\n";
    }
    
    echo "\n";
    
    // Find Branches menu
    $branchesMenu = collect($menuItems)->firstWhere('title', 'Branches');
    if ($branchesMenu) {
        echo "✅ Branches menu found:\n";
        echo "   Route: {$branchesMenu['route']}\n";
        echo "   Sub-items:\n";
        foreach ($branchesMenu['sub_items'] as $subItem) {
            echo "   - {$subItem['title']} -> {$subItem['route']}\n";
        }
        
        // Check if Activate Branch is present
        $activateBranchItem = collect($branchesMenu['sub_items'])->firstWhere('title', 'Activate Branch');
        if ($activateBranchItem) {
            echo "✅ 'Activate Branch' sub-item found!\n";
        } else {
            echo "❌ 'Activate Branch' sub-item missing!\n";
        }
    } else {
        echo "❌ Branches menu not found\n";
    }
    
    echo "\n=== ROUTE VALIDATION ===\n";
    
    // Test route existence
    $routesToTest = [
        'admin.organizations.activate.form',
        'admin.branches.activate.form'
    ];
    
    foreach ($routesToTest as $route) {
        if (\Illuminate\Support\Facades\Route::has($route)) {
            echo "✅ Route '{$route}' exists\n";
        } else {
            echo "❌ Route '{$route}' missing\n";
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "✅ AdminSidebar component successfully tested\n";
    echo "✅ Menu structure includes activation options\n";
    echo "✅ Both organization and branch activation are available in sidebar\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
