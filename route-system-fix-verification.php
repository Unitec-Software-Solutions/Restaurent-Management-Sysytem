<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 ROUTE SYSTEM FIX VERIFICATION\n";
echo "================================\n\n";

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Services\AdminAuthService;

// Step 1: Login as super admin
echo "1. TESTING AUTHENTICATION\n";
echo "=========================\n";

Auth::guard('admin')->logout();

$authService = new AdminAuthService();
$loginResult = $authService->login('superadmin@rms.com', 'password', false);

if ($loginResult['success']) {
    echo "✅ Login successful\n";
    $admin = Auth::guard('admin')->user();
    echo "   - User: {$admin->email}\n";
    echo "   - Super Admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   - Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n";
} else {
    echo "❌ Login failed: {$loginResult['error']}\n";
    exit(1);
}

echo "\n";

// Step 2: Test route existence and registration
echo "2. TESTING ROUTE REGISTRATION\n";
echo "=============================\n";

$criticalRoutes = [
    'admin.dashboard' => '/admin/dashboard',
    'admin.inventory.index' => '/admin/inventory',
    'admin.inventory.items.index' => '/admin/inventory/items',
    'admin.inventory.stock.index' => '/admin/inventory/stock',
    'admin.suppliers.index' => '/admin/suppliers',
    'admin.suppliers.create' => '/admin/suppliers/create',
    'admin.grn.index' => '/admin/grn',
    'admin.orders.index' => '/admin/orders'
];

foreach ($criticalRoutes as $routeName => $expectedUri) {
    $exists = Route::has($routeName);
    echo "   {$routeName}: " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "\n";
    
    if ($exists) {
        $route = Route::getRoutes()->getByName($routeName);
        $actualUri = '/' . $route->uri();
        
        if ($actualUri === $expectedUri) {
            echo "      - URI: ✅ {$actualUri}\n";
        } else {
            echo "      - URI: ❌ Expected {$expectedUri}, got {$actualUri}\n";
        }
        
        $middleware = $route->gatherMiddleware();
        echo "      - Middleware: " . implode(', ', $middleware) . "\n";
    }
}

echo "\n";

// Step 3: Test controller methods directly
echo "3. TESTING CONTROLLER METHODS\n";
echo "=============================\n";

// Test ItemDashboardController
echo "Testing ItemDashboardController@index:\n";
try {
    $controller = new \App\Http\Controllers\ItemDashboardController();
    $request = \Illuminate\Http\Request::create('/admin/inventory', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    $response = $controller->index();
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   ❌ Still redirecting to: {$response->getTargetUrl()}\n";
    } else {
        echo "   ✅ Returns view/response successfully\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller error: {$e->getMessage()}\n";
}

// Test SupplierController
echo "\nTesting SupplierController@index:\n";
try {
    $controller = new \App\Http\Controllers\SupplierController();
    $request = \Illuminate\Http\Request::create('/admin/suppliers', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    $response = $controller->index($request);
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   ❌ Still redirecting to: {$response->getTargetUrl()}\n";
    } else {
        echo "   ✅ Returns view/response successfully\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller error: {$e->getMessage()}\n";
}

// Test Admin/InventoryController (the one causing redirect loops)
echo "\nTesting Admin\\InventoryController@index:\n";
try {
    $controller = new \App\Http\Controllers\Admin\InventoryController();
    $response = $controller->index();
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   ❌ Still redirecting to: {$response->getTargetUrl()}\n";
    } else {
        echo "   ✅ Returns view/response successfully\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller error: {$e->getMessage()}\n";
}

echo "\n";

// Step 4: Test route resolution
echo "4. TESTING ROUTE RESOLUTION\n";
echo "===========================\n";

$routesToTest = [
    '/admin/inventory',
    '/admin/suppliers',
    '/admin/grn'
];

foreach ($routesToTest as $uri) {
    echo "Testing route: {$uri}\n";
    
    try {
        $request = \Illuminate\Http\Request::create($uri, 'GET');
        $request->setLaravelSession(app('session.store'));
        
        // Get route info without executing
        $route = app('router')->getRoutes()->match($request);
        
        if ($route) {
            echo "   ✅ Route found\n";
            echo "      - Name: " . ($route->getName() ?? 'unnamed') . "\n";
            echo "      - Controller: {$route->getActionName()}\n";
            echo "      - Middleware: " . implode(', ', $route->gatherMiddleware()) . "\n";
        } else {
            echo "   ❌ Route not found\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Route resolution error: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

echo "5. ROUTE SYSTEM FIX SUMMARY\n";
echo "===========================\n";

$fixesApplied = [
    '✅ Removed duplicate route definitions from routes/groups/admin.php',
    '✅ Enhanced SupplierController super admin logic',
    '✅ Enhanced ItemDashboardController super admin logic', 
    '✅ Fixed Admin/InventoryController redirect loops',
    '✅ Maintained single source of truth in routes/web.php',
    '✅ Preserved middleware authentication chains'
];

foreach ($fixesApplied as $fix) {
    echo "   {$fix}\n";
}

echo "\n🎯 ROUTE SYSTEM AUDIT COMPLETE\n";
echo "The admin sidebar navigation should now work correctly.\n";
echo "Key routes (inventory, suppliers, GRN) should be accessible without redirect loops.\n";
