<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 DEBUGGING INVENTORY/SUPPLIER AUTHENTICATION ISSUE\n";
echo "====================================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;
use App\Http\Middleware\EnhancedAdminAuth;
use Illuminate\Support\Facades\Route;

// Test 1: Login with our service
echo "1. TESTING LOGIN AND INITIAL STATE\n";
echo "===================================\n";

Auth::guard('admin')->logout();
session()->flush();

$authService = new AdminAuthService();
$result = $authService->login('superadmin@rms.com', 'password', false);

if ($result['success']) {
    echo "✅ Login successful\n";
    $admin = $result['admin'];
    echo "   - User: {$admin->email}\n";
    echo "   - is_super_admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   - Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n";
    echo "   - is_active: " . ($admin->is_active ? 'YES' : 'NO') . "\n";
    
    // Check if user is properly authenticated
    $authCheck = Auth::guard('admin')->check();
    echo "   - Auth guard status: " . ($authCheck ? 'AUTHENTICATED' : 'NOT AUTHENTICATED') . "\n";
    
    if ($authCheck) {
        $user = Auth::guard('admin')->user();
        echo "   - Same user instance: " . ($user->id === $admin->id ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "❌ Login failed: {$result['error']}\n";
    exit(1);
}

echo "\n";

// Test 2: Check specific routes exist
echo "2. CHECKING ROUTE EXISTENCE\n";
echo "===========================\n";

$routesToTest = [
    'admin.dashboard',
    'admin.inventory.index', 
    'admin.suppliers.index',
    'admin.grn.index',
    'admin.orders.index',
    'admin.profile.index'
];

foreach ($routesToTest as $routeName) {
    $exists = Route::has($routeName);
    echo "   {$routeName}: " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "\n";
    
    if ($exists) {
        $route = Route::getRoutes()->getByName($routeName);
        echo "      - URI: /{$route->uri()}\n";
        echo "      - Middleware: " . implode(', ', $route->gatherMiddleware()) . "\n";
    }
}

echo "\n";

// Test 3: Test middleware for each problematic route
echo "3. TESTING MIDDLEWARE FOR EACH ROUTE\n";
echo "====================================\n";

$problematicRoutes = [
    'admin.dashboard' => '/admin/dashboard',
    'admin.inventory.index' => '/admin/inventory', 
    'admin.suppliers.index' => '/admin/suppliers',
    'admin.grn.index' => '/admin/grn'
];

$middleware = new EnhancedAdminAuth();

foreach ($problematicRoutes as $routeName => $uri) {
    echo "Testing route: {$routeName} ({$uri})\n";
    
    // Create a request for this route
    $request = Request::create($uri, 'GET');
    $request->setLaravelSession(app('session.store'));
    
    // Add route name to request for debugging
    if (Route::has($routeName)) {
        $request->route()->action['as'] = $routeName;
    }
    
    try {
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['status' => 'middleware_passed', 'route' => $req->route()->getName()]);
        });
        
        if ($response->getStatusCode() === 200) {
            echo "   ✅ Middleware PASSED\n";
        } else {
            echo "   ❌ Middleware FAILED - Status: {$response->getStatusCode()}\n";
            if ($response->getStatusCode() === 302) {
                echo "      Redirect to: {$response->headers->get('Location')}\n";
            }
        }
    } catch (Exception $e) {
        echo "   ❌ Middleware ERROR: {$e->getMessage()}\n";
        echo "      File: {$e->getFile()}:{$e->getLine()}\n";
    }
    
    echo "\n";
}

// Test 4: Check session and guard state between route tests
echo "4. CHECKING SESSION PERSISTENCE\n";
echo "===============================\n";

echo "Auth state check:\n";
echo "   - Auth::guard('admin')->check(): " . (Auth::guard('admin')->check() ? 'TRUE' : 'FALSE') . "\n";

if (Auth::guard('admin')->check()) {
    $user = Auth::guard('admin')->user();
    echo "   - User ID: {$user->id}\n";
    echo "   - User email: {$user->email}\n";
    echo "   - Session ID: " . session()->getId() . "\n";
}

$sessionKey = 'login_admin_59ba36addc2b2f9401580f014c7f58ea4e30989d';
echo "   - Session has auth key: " . (session()->has($sessionKey) ? 'YES' : 'NO') . "\n";

echo "\n";

// Test 5: Compare working vs non-working routes by testing actual HTTP simulation
echo "5. TESTING ACTUAL HTTP REQUEST SIMULATION\n";
echo "=========================================\n";

$testRoutes = [
    'Dashboard (WORKING)' => '/admin/dashboard',
    'Inventory (FAILING)' => '/admin/inventory',
    'Suppliers (FAILING)' => '/admin/suppliers',
    'Profile (WORKING)' => '/admin/profile'
];

foreach ($testRoutes as $label => $uri) {
    echo "Testing {$label}: {$uri}\n";
    
    try {
        $request = Request::create($uri, 'GET');
        $request->setLaravelSession(app('session.store'));
        
        // Set headers that a real browser would send
        $request->headers->set('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        $request->headers->set('User-Agent', 'Mozilla/5.0');
        
        $response = app()->handle($request);
        
        echo "   - Status: {$response->getStatusCode()}\n";
        
        if ($response->getStatusCode() === 302) {
            echo "   - Redirect to: {$response->headers->get('Location')}\n";
        } elseif ($response->getStatusCode() === 200) {
            echo "   - ✅ Success - Page accessible\n";
        } else {
            echo "   - ❌ Unexpected status\n";
        }
        
    } catch (Exception $e) {
        echo "   - ❌ Exception: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

// Test 6: Check if specific controllers exist and are accessible
echo "6. CHECKING CONTROLLER CLASSES\n";
echo "==============================\n";

$controllers = [
    'AdminController' => \App\Http\Controllers\AdminController::class,
    'ItemDashboardController' => \App\Http\Controllers\ItemDashboardController::class,
    'SupplierController' => \App\Http\Controllers\SupplierController::class,
    'GrnDashboardController' => \App\Http\Controllers\GrnDashboardController::class
];

foreach ($controllers as $name => $class) {
    echo "   {$name}: ";
    if (class_exists($class)) {
        echo "✅ EXISTS\n";
        
        // Check if key methods exist
        $methods = ['index'];
        foreach ($methods as $method) {
            if (method_exists($class, $method)) {
                echo "      - {$method}(): ✅\n";
            } else {
                echo "      - {$method}(): ❌\n";
            }
        }
    } else {
        echo "❌ MISSING\n";
    }
}

echo "\n🎯 DEBUGGING SUMMARY\n";
echo "====================\n";

// Check if the issue is route-specific or middleware-specific
$authStatus = Auth::guard('admin')->check();
echo "Final auth status: " . ($authStatus ? 'AUTHENTICATED' : 'NOT AUTHENTICATED') . "\n";

if (!$authStatus) {
    echo "❌ ISSUE: Authentication is being lost during testing\n";
} else {
    echo "✅ Authentication maintained throughout testing\n";
    echo "   This suggests the issue may be route-specific or controller-specific\n";
}

echo "\n";
