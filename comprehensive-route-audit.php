<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 COMPREHENSIVE ROUTE SYSTEM AUDIT\n";
echo "====================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Services\AdminAuthService;

// Login as super admin
Auth::guard('admin')->logout();
$authService = new AdminAuthService();
$loginResult = $authService->login('superadmin@rms.com', 'password', false);

if (!$loginResult['success']) {
    echo "❌ Login failed: {$loginResult['error']}\n";
    exit(1);
}

echo "✅ Authentication successful\n\n";

// STEP 1: Route Conflicts Detection
echo "1. ROUTE CONFLICTS DETECTION\n";
echo "=============================\n";

$criticalRoutes = [
    'admin.inventory.index',
    'admin.inventory.items.index',
    'admin.inventory.stock.index',
    'admin.suppliers.index',
    'admin.suppliers.create',
    'admin.grn.index'
];

$routeConflicts = [];
foreach ($criticalRoutes as $routeName) {
    if (Route::has($routeName)) {
        $route = Route::getRoutes()->getByName($routeName);
        $uri = $route->uri();
        $controller = $route->getActionName();
        
        echo "✅ Route '{$routeName}' exists:\n";
        echo "   - URI: /{$uri}\n";
        echo "   - Controller: {$controller}\n";
        echo "   - Middleware: " . implode(', ', $route->gatherMiddleware()) . "\n";
        
        // Check for duplicates by scanning route collection
        $allRoutes = Route::getRoutes()->getRoutes();
        $duplicates = [];
        foreach ($allRoutes as $r) {
            if ($r->getName() === $routeName && $r !== $route) {
                $duplicates[] = $r;
            }
        }
        
        if (count($duplicates) > 0) {
            echo "   ⚠️  DUPLICATE DETECTED: " . count($duplicates) . " additional routes with same name\n";
            $routeConflicts[] = $routeName;
        }
    } else {
        echo "❌ Route '{$routeName}' does not exist\n";
        $routeConflicts[] = $routeName;
    }
    echo "\n";
}

// STEP 2: Controller Redirect Loop Detection
echo "2. CONTROLLER REDIRECT LOOP DETECTION\n";
echo "======================================\n";

$controllerTests = [
    'InventoryController (Admin)' => [
        'class' => \App\Http\Controllers\Admin\InventoryController::class,
        'method' => 'index',
        'expected_route' => 'admin.inventory.index'
    ],
    'SupplierController' => [
        'class' => \App\Http\Controllers\SupplierController::class,
        'method' => 'index',
        'expected_route' => 'admin.suppliers.index'
    ],
    'ItemDashboardController' => [
        'class' => \App\Http\Controllers\ItemDashboardController::class,
        'method' => 'index',
        'expected_route' => 'admin.inventory.index'
    ]
];

$redirectLoops = [];
foreach ($controllerTests as $name => $test) {
    echo "Testing {$name}:\n";
    
    try {
        $controller = new $test['class']();
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->setLaravelSession(app('session.store'));
        
        $response = $controller->{$test['method']}($request);
        
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $redirectUrl = $response->getTargetUrl();
            echo "   ❌ REDIRECTS TO: {$redirectUrl}\n";
            
            // Check if it's a redirect loop (redirecting to itself)
            if (str_contains($redirectUrl, $test['expected_route'])) {
                echo "   🔄 REDIRECT LOOP DETECTED!\n";
                $redirectLoops[] = $name;
            }
        } else {
            echo "   ✅ Returns view/response directly\n";
        }
    } catch (Exception $e) {
        echo "   ❌ ERROR: {$e->getMessage()}\n";
        $redirectLoops[] = $name;
    }
    echo "\n";
}

// STEP 3: Sidebar Component Route Validation
echo "3. SIDEBAR COMPONENT ROUTE VALIDATION\n";
echo "======================================\n";

try {
    $sidebar = new \App\View\Components\AdminSidebar();
    $view = $sidebar->render();
    $menuItems = $view->getData()['menuItems'] ?? [];
    
    echo "AdminSidebar component loaded successfully\n";
    echo "Menu items found: " . count($menuItems) . "\n\n";
    
    $sidebarIssues = [];
    foreach ($menuItems as $item) {
        if (isset($item['route'])) {
            $routeName = $item['route'];
            $routeExists = Route::has($routeName);
            $routeValid = $item['is_route_valid'] ?? false;
            
            echo "Menu item '{$item['title']}':\n";
            echo "   - Route: {$routeName}\n";
            echo "   - Route exists: " . ($routeExists ? 'YES' : 'NO') . "\n";
            echo "   - Route valid: " . ($routeValid ? 'YES' : 'NO') . "\n";
            
            if (!$routeExists || !$routeValid) {
                echo "   ❌ SIDEBAR ISSUE DETECTED\n";
                $sidebarIssues[] = $item['title'];
            }
            echo "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Sidebar component error: {$e->getMessage()}\n";
    $sidebarIssues = ['Sidebar Component'];
}

// STEP 4: Root Cause Analysis
echo "4. ROOT CAUSE ANALYSIS\n";
echo "=======================\n";

$rootCauses = [];

if (count($routeConflicts) > 0) {
    $rootCauses[] = "Route Conflicts: " . implode(', ', $routeConflicts);
}

if (count($redirectLoops) > 0) {
    $rootCauses[] = "Controller Redirect Loops: " . implode(', ', $redirectLoops);
}

if (count($sidebarIssues) > 0) {
    $rootCauses[] = "Sidebar Route Issues: " . implode(', ', $sidebarIssues);
}

if (count($rootCauses) > 0) {
    echo "🚨 CRITICAL ISSUES IDENTIFIED:\n";
    foreach ($rootCauses as $cause) {
        echo "   - {$cause}\n";
    }
} else {
    echo "✅ No critical issues detected\n";
}

echo "\n";

// STEP 5: Generate Fix Recommendations
echo "5. FIX RECOMMENDATIONS\n";
echo "=======================\n";

if (count($routeConflicts) > 0) {
    echo "📋 Route Conflicts Fix:\n";
    echo "   - Remove duplicate route definitions from routes/groups/admin.php\n";
    echo "   - Keep only routes/web.php definitions for admin routes\n";
    echo "   - Ensure proper route naming conventions\n\n";
}

if (count($redirectLoops) > 0) {
    echo "📋 Controller Redirect Loops Fix:\n";
    echo "   - Update Admin/InventoryController to return views directly\n";
    echo "   - Improve super admin logic in SupplierController\n";
    echo "   - Ensure ItemDashboardController doesn't redirect to itself\n\n";
}

if (count($sidebarIssues) > 0) {
    echo "📋 Sidebar Route Issues Fix:\n";
    echo "   - Update AdminSidebar component route validation\n";
    echo "   - Ensure all menu items have valid routes\n";
    echo "   - Fix route() helper usage in sidebar views\n\n";
}

echo "🎯 AUDIT COMPLETE\n";
echo "==================\n";
echo "Total issues found: " . (count($routeConflicts) + count($redirectLoops) + count($sidebarIssues)) . "\n";
echo "Ready to implement fixes.\n";
