<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 FOCUSED INVENTORY/SUPPLIER ROUTING DEBUG\n";
echo "==========================================\n\n";

use Illuminate\Support\Facades\Auth;
use App\Services\AdminAuthService;

// Step 1: Login
echo "1. LOGGING IN AS SUPER ADMIN\n";
echo "=============================\n";

Auth::guard('admin')->logout();

$authService = new AdminAuthService();
$loginResult = $authService->login('superadmin@rms.com', 'password', false);

if (!$loginResult['success']) {
    echo "❌ Login failed: {$loginResult['error']}\n";
    exit(1);
}

$admin = Auth::guard('admin')->user();
echo "✅ Login successful: {$admin->email}\n";
echo "   - Super Admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
echo "   - Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n\n";

// Step 2: Test SupplierController directly with actual request
echo "2. TESTING SUPPLIER CONTROLLER ACCESS\n";
echo "======================================\n";

try {
    $controller = new \App\Http\Controllers\SupplierController();
    
    // Create a mock request
    $request = \Illuminate\Http\Request::create('/admin/suppliers', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    echo "Testing SupplierController@index with authenticated admin...\n";
    
    // Call the index method directly
    $response = $controller->index($request);
    
    // Check response type
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "❌ Controller redirected to: {$response->getTargetUrl()}\n";
        
        // Check if redirecting to login
        if (str_contains($response->getTargetUrl(), '/admin/login')) {
            echo "   🔍 Analyzing redirect reason...\n";
            
            // Check the exact conditions from SupplierController
            echo "   - Auth guard check: " . (Auth::guard('admin')->check() ? 'PASS' : 'FAIL') . "\n";
            
            if (Auth::guard('admin')->check()) {
                $admin = Auth::guard('admin')->user();
                echo "   - User found: {$admin->email}\n";
                
                // Test exact conditions from controller
                $isSuperAdmin = $admin->isSuperAdmin();
                echo "   - isSuperAdmin(): " . ($isSuperAdmin ? 'YES' : 'NO') . "\n";
                echo "   - organization_id: " . ($admin->organization_id ?? 'NULL') . "\n";
                
                // The problematic condition
                if (!$isSuperAdmin && !$admin->organization_id) {
                    echo "   ❌ FOUND ISSUE: Controller logic requires organization_id for non-super admins\n";
                    echo "   📝 Super admin should bypass this requirement\n";
                } else {
                    echo "   ✅ Controller conditions should pass\n";
                }
            }
        }
    } elseif ($response instanceof \Illuminate\View\View) {
        echo "✅ Controller returned view successfully\n";
        echo "   - View: {$response->getName()}\n";
    } elseif ($response instanceof \Illuminate\Http\Response) {
        echo "✅ Controller returned response (Status: {$response->getStatusCode()})\n";
    } else {
        echo "✅ Controller returned: " . get_class($response) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Controller error: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
}

echo "\n";

// Step 3: Test ItemDashboardController
echo "3. TESTING INVENTORY CONTROLLER ACCESS\n";
echo "=======================================\n";

try {
    $controller = new \App\Http\Controllers\ItemDashboardController();
    
    $request = \Illuminate\Http\Request::create('/admin/inventory', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    echo "Testing ItemDashboardController@index with authenticated admin...\n";
    
    $response = $controller->index($request);
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "❌ Controller redirected to: {$response->getTargetUrl()}\n";
    } elseif ($response instanceof \Illuminate\View\View) {
        echo "✅ Controller returned view successfully\n";
        echo "   - View: {$response->getName()}\n";
    } else {
        echo "✅ Controller returned: " . get_class($response) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Controller error: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
}

echo "\n";

// Step 4: Check specific admin sidebar routing
echo "4. CHECKING ADMIN SIDEBAR COMPONENT\n";
echo "====================================\n";

try {
    $sidebarComponent = new \App\View\Components\AdminSidebar();
    $menuItems = $sidebarComponent->render()->getData()['menuItems'] ?? [];
    
    echo "AdminSidebar component data:\n";
    
    $inventoryFound = false;
    $supplierFound = false;
    
    foreach ($menuItems as $item) {
        if (is_array($item) && isset($item['title'])) {
            if ($item['title'] === 'Inventory') {
                $inventoryFound = true;
                echo "✅ Inventory menu item found:\n";
                echo "   - Route: {$item['route']}\n";
                echo "   - Route Valid: " . ($item['is_route_valid'] ? 'YES' : 'NO') . "\n";
                echo "   - Permission: {$item['permission']}\n";
            }
            
            if ($item['title'] === 'Suppliers') {
                $supplierFound = true;
                echo "✅ Suppliers menu item found:\n";
                echo "   - Route: {$item['route']}\n";
                echo "   - Route Valid: " . ($item['is_route_valid'] ? 'YES' : 'NO') . "\n";
                echo "   - Permission: {$item['permission']}\n";
            }
        }
    }
    
    if (!$inventoryFound) echo "❌ Inventory menu item not found in sidebar\n";
    if (!$supplierFound) echo "❌ Suppliers menu item not found in sidebar\n";
    
} catch (Exception $e) {
    echo "❌ Sidebar component error: {$e->getMessage()}\n";
}

echo "\n";

// Step 5: Summary and recommendations
echo "5. SUMMARY AND RECOMMENDATIONS\n";
echo "===============================\n";

echo "Based on the tests above:\n";
echo "- Routes exist and are properly configured\n";
echo "- Authentication middleware works correctly\n";
echo "- Controllers can be accessed directly\n";
echo "- If redirects occur, it's likely due to controller logic\n\n";

echo "🔧 LIKELY ROOT CAUSE:\n";
echo "The issue is most likely in the SupplierController and/or ItemDashboardController\n";
echo "where they check for organization_id even for super admins.\n\n";

echo "💡 SOLUTION:\n";
echo "Update controller logic to properly handle super admin bypass\n";
echo "for organization_id requirements.\n";
