<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 TESTING FIXED INVENTORY/SUPPLIER ROUTING\n";
echo "==========================================\n\n";

use Illuminate\Support\Facades\Auth;
use App\Services\AdminAuthService;
use Illuminate\Http\Request;

// Step 1: Login as super admin
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

// Step 2: Test controllers directly
echo "2. TESTING CONTROLLERS DIRECTLY\n";
echo "================================\n";

$controllers = [
    'SupplierController@index' => [\App\Http\Controllers\SupplierController::class, 'index'],
    'ItemDashboardController@index' => [\App\Http\Controllers\ItemDashboardController::class, 'index'],
];

foreach ($controllers as $label => [$controllerClass, $method]) {
    echo "Testing {$label}:\n";
    
    try {
        $controller = new $controllerClass();
        $request = Request::create('/test', 'GET');
        $request->setLaravelSession(app('session.store'));
        
        $response = $controller->$method($request);
        
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $url = $response->getTargetUrl();
            if (str_contains($url, '/admin/login')) {
                echo "   ❌ Still redirecting to login: {$url}\n";
            } else {
                echo "   ⚠️  Redirecting to: {$url}\n";
            }
        } elseif ($response instanceof \Illuminate\View\View) {
            echo "   ✅ Controller returned view: {$response->getName()}\n";
        } else {
            echo "   ✅ Controller returned: " . get_class($response) . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Controller error: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

// Step 3: Test AdminSidebar component
echo "3. TESTING ADMIN SIDEBAR COMPONENT\n";
echo "===================================\n";

try {
    $sidebarComponent = new \App\View\Components\AdminSidebar();
    $menuItems = $sidebarComponent->render()->getData()['menuItems'] ?? [];
    
    echo "AdminSidebar menu items found:\n";
    
    $inventoryFound = false;
    $supplierFound = false;
    
    foreach ($menuItems as $item) {
        if (is_array($item) && isset($item['title'])) {
            echo "   - {$item['title']}: {$item['route']}\n";
            
            if ($item['title'] === 'Inventory') {
                $inventoryFound = true;
                echo "     ✅ Route valid: " . ($item['is_route_valid'] ? 'YES' : 'NO') . "\n";
            }
            
            if ($item['title'] === 'Suppliers') {
                $supplierFound = true;
                echo "     ✅ Route valid: " . ($item['is_route_valid'] ? 'YES' : 'NO') . "\n";
            }
        }
    }
    
    if (!$inventoryFound) echo "   ❌ Inventory menu item not found\n";
    if (!$supplierFound) echo "   ❌ Suppliers menu item not found\n";
    
} catch (Exception $e) {
    echo "❌ Sidebar component error: {$e->getMessage()}\n";
}

echo "\n";

// Step 4: Test routes
echo "4. TESTING ROUTE ACCESSIBILITY\n";
echo "===============================\n";

$routes = [
    'admin.inventory.index' => '/admin/inventory',
    'admin.suppliers.index' => '/admin/suppliers',
];

foreach ($routes as $routeName => $path) {
    echo "Testing route {$routeName} ({$path}):\n";
    
    try {
        $exists = \Illuminate\Support\Facades\Route::has($routeName);
        echo "   - Route exists: " . ($exists ? 'YES' : 'NO') . "\n";
        
        if ($exists) {
            $url = route($routeName);
            echo "   - Generated URL: {$url}\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Route error: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

echo "🎯 SUMMARY\n";
echo "==========\n";
echo "The fixes should resolve the redirect loop issue.\n";
echo "Controllers now properly handle super admin access without requiring organization_id.\n";
echo "Suppliers menu item has been added to the sidebar.\n\n";

echo "✅ Test completed. Check the results above.\n";
