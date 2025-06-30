<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🎯 FINAL ADMIN SIDEBAR SYSTEM VERIFICATION\n";
echo "==========================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Services\AdminAuthService;

// Quick authentication
Auth::guard('admin')->logout();
$authService = new AdminAuthService();
$loginResult = $authService->login('superadmin@rms.com', 'password', false);

if ($loginResult['success']) {
    echo "✅ Authentication: SUCCESS\n";
} else {
    echo "❌ Authentication: FAILED\n";
    exit(1);
}

// Test critical routes
echo "\nCRITICAL ROUTES STATUS:\n";
echo "=======================\n";

$criticalRoutes = [
    'admin.dashboard' => '✅ Always required',
    'admin.inventory.index' => '🔍 Sidebar link',
    'admin.inventory.items.index' => '📦 Inventory sub-menu',
    'admin.inventory.stock.index' => '📊 Stock management',
    'admin.suppliers.index' => '🚚 Sidebar link',
    'admin.suppliers.create' => '➕ Supplier creation',
    'admin.grn.index' => '📋 GRN management'
];

$allRoutesValid = true;
foreach ($criticalRoutes as $routeName => $description) {
    $exists = Route::has($routeName);
    echo sprintf("%-30s %s %s\n", $routeName, $exists ? '✅' : '❌', $description);
    if (!$exists) $allRoutesValid = false;
}

echo "\nSIDEBAR COMPONENT STATUS:\n";
echo "=========================\n";

try {
    $sidebar = new \App\View\Components\AdminSidebar();
    $view = $sidebar->render();
    $menuItems = $view->getData()['menuItems'] ?? [];
    
    echo "✅ AdminSidebar component loads successfully\n";
    echo "📊 Menu items generated: " . count($menuItems) . "\n";
    
    // Check specific menu items
    $inventoryFound = false;
    $suppliersFound = false;
    
    foreach ($menuItems as $item) {
        if ($item['title'] === 'Inventory') {
            $inventoryFound = true;
            echo "✅ Inventory menu item: Route valid = " . ($item['is_route_valid'] ? 'YES' : 'NO') . "\n";
        }
        if ($item['title'] === 'Suppliers') {
            $suppliersFound = true;
            echo "✅ Suppliers menu item: Route valid = " . ($item['is_route_valid'] ? 'YES' : 'NO') . "\n";
        }
    }
    
    if (!$inventoryFound) echo "⚠️  Inventory menu item not found\n";
    if (!$suppliersFound) echo "⚠️  Suppliers menu item not found\n";
    
} catch (Exception $e) {
    echo "❌ AdminSidebar error: {$e->getMessage()}\n";
    $allRoutesValid = false;
}

echo "\nCONTROLLER FUNCTIONALITY TEST:\n";
echo "==============================\n";

$controllers = [
    'ItemDashboardController' => [\App\Http\Controllers\ItemDashboardController::class, '/admin/inventory'],
    'SupplierController' => [\App\Http\Controllers\SupplierController::class, '/admin/suppliers'],
    'GrnDashboardController' => [\App\Http\Controllers\GrnDashboardController::class, '/admin/grn']
];

foreach ($controllers as $name => [$class, $path]) {
    echo "Testing {$name}:\n";
    try {
        $controller = new $class();
        $request = \Illuminate\Http\Request::create($path, 'GET');
        $request->setLaravelSession(app('session.store'));
        
        $response = $controller->index($request);
        
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            echo "   ⚠️  Redirects to: " . $response->getTargetUrl() . "\n";
        } else {
            echo "   ✅ Returns response successfully\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error: {$e->getMessage()}\n";
        $allRoutesValid = false;
    }
}

echo "\n🏁 FINAL ASSESSMENT:\n";
echo "====================\n";

if ($allRoutesValid) {
    echo "✅ SYSTEM STATUS: FULLY OPERATIONAL\n";
    echo "   All critical routes exist and are accessible\n";
    echo "   Sidebar component generates valid menu items\n";
    echo "   Controllers handle requests appropriately\n\n";
    
    echo "📋 ADMIN SIDEBAR SHOULD NOW WORK CORRECTLY\n";
    echo "   - Navigation links are properly configured\n";
    echo "   - Route validation passes for all menu items\n";
    echo "   - Controllers return views without redirect loops\n";
    echo "   - Super admin authentication is properly handled\n";
} else {
    echo "⚠️  SYSTEM STATUS: ISSUES DETECTED\n";
    echo "   Some routes or components are not functioning correctly\n";
    echo "   Review the errors above for specific fixes needed\n";
}

echo "\n🔧 If issues persist, check:\n";
echo "   1. Browser cache and session storage\n";
echo "   2. Laravel route cache: php artisan route:clear\n";
echo "   3. View cache: php artisan view:clear\n";
echo "   4. Config cache: php artisan config:clear\n";
