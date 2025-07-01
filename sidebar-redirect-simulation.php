<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 ADMIN SIDEBAR REDIRECT SIMULATION TEST\n";
echo "==========================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Services\AdminAuthService;
use Illuminate\Http\Request;

// Login as super admin
Auth::guard('admin')->logout();
$authService = new AdminAuthService();
$loginResult = $authService->login('superadmin@rms.com', 'password', false);

if (!$loginResult['success']) {
    echo "❌ Login failed: {$loginResult['error']}\n";
    exit(1);
}

$admin = Auth::guard('admin')->user();
echo "✅ Authentication successful: {$admin->email}\n";
echo "   - Super Admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
echo "   - Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n\n";

// STEP 1: Simulate clicking on sidebar links
echo "1. SIMULATING SIDEBAR LINK CLICKS\n";
echo "==================================\n";

$sidebarLinks = [
    'Dashboard' => 'admin.dashboard',
    'Inventory' => 'admin.inventory.index',
    'Suppliers' => 'admin.suppliers.index',
    'GRN' => 'admin.grn.index',
    'Items' => 'admin.inventory.items.index',
    'Stock' => 'admin.inventory.stock.index'
];

foreach ($sidebarLinks as $linkName => $routeName) {
    echo "🔗 Testing sidebar link: {$linkName} -> {$routeName}\n";
    
    if (!Route::has($routeName)) {
        echo "   ❌ Route does not exist\n\n";
        continue;
    }
    
    $route = Route::getRoutes()->getByName($routeName);
    $uri = $route->uri();
    
    echo "   - URI: /{$uri}\n";
    echo "   - Controller: {$route->getActionName()}\n";
    
    // Simulate HTTP request to this route
    try {
        $request = Request::create("/{$uri}", 'GET');
        $request->setLaravelSession(app('session.store'));
        
        // Apply middleware stack
        $middleware = $route->gatherMiddleware();
        echo "   - Middleware: " . implode(', ', $middleware) . "\n";
        
        // Test auth:admin middleware specifically
        if (in_array('auth:admin', $middleware)) {
            $authMiddleware = app('Illuminate\Auth\Middleware\Authenticate');
            
            try {
                $authResponse = $authMiddleware->handle($request, function ($req) {
                    return response()->json(['status' => 'auth_passed']);
                }, 'admin');
                
                if ($authResponse->getStatusCode() === 200) {
                    echo "   - Auth middleware: ✅ PASS\n";
                } else {
                    echo "   - Auth middleware: ❌ FAIL (Status: {$authResponse->getStatusCode()})\n";
                    if ($authResponse instanceof \Illuminate\Http\RedirectResponse) {
                        echo "     Redirect to: {$authResponse->getTargetUrl()}\n";
                    }
                }
            } catch (Exception $e) {
                echo "   - Auth middleware: ❌ ERROR: {$e->getMessage()}\n";
            }
        }
        
        // Test controller method directly
        $controllerClass = explode('@', $route->getActionName())[0];
        $controllerMethod = explode('@', $route->getActionName())[1];
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            
            try {
                $controllerResponse = $controller->{$controllerMethod}($request);
                
                if ($controllerResponse instanceof \Illuminate\Http\RedirectResponse) {
                    $redirectUrl = $controllerResponse->getTargetUrl();
                    echo "   - Controller: ❌ REDIRECTS TO: {$redirectUrl}\n";
                    
                    // Check if redirecting to login (auth issue)
                    if (str_contains($redirectUrl, '/admin/login')) {
                        echo "     🚨 AUTHENTICATION REDIRECT DETECTED!\n";
                        echo "     This suggests the controller is not recognizing the authenticated user.\n";
                    }
                    
                    // Check if redirecting to dashboard (permission issue)
                    if (str_contains($redirectUrl, '/admin/dashboard')) {
                        echo "     ⚠️  PERMISSION/ORGANIZATION REDIRECT DETECTED!\n";
                        echo "     This suggests the controller is enforcing organization requirements.\n";
                    }
                    
                } elseif ($controllerResponse instanceof \Illuminate\View\View) {
                    echo "   - Controller: ✅ RENDERS VIEW: {$controllerResponse->getName()}\n";
                } else {
                    echo "   - Controller: ✅ RETURNS RESPONSE: " . get_class($controllerResponse) . "\n";
                }
                
            } catch (Exception $e) {
                echo "   - Controller: ❌ ERROR: {$e->getMessage()}\n";
                echo "     File: {$e->getFile()}:{$e->getLine()}\n";
            }
        } else {
            echo "   - Controller: ❌ CLASS NOT FOUND: {$controllerClass}\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Request simulation error: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

// STEP 2: Test specific problematic scenarios
echo "2. TESTING SPECIFIC PROBLEMATIC SCENARIOS\n";
echo "==========================================\n";

// Test organization_id requirements
echo "🔍 Testing organization_id validation in controllers:\n\n";

$controllersWithOrgValidation = [
    'SupplierController' => \App\Http\Controllers\SupplierController::class,
    'ItemDashboardController' => \App\Http\Controllers\ItemDashboardController::class,
    'Admin\InventoryController' => \App\Http\Controllers\Admin\InventoryController::class
];

foreach ($controllersWithOrgValidation as $name => $class) {
    echo "Testing {$name}:\n";
    
    try {
        $controller = new $class();
        $request = Request::create('/test', 'GET');
        $request->setLaravelSession(app('session.store'));
        
        // Call index method
        if (method_exists($controller, 'index')) {
            $response = $controller->index($request);
            
            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                $redirectUrl = $response->getTargetUrl();
                echo "   - Redirects to: {$redirectUrl}\n";
                
                // Analyze redirect reason
                if (str_contains($redirectUrl, '/admin/login')) {
                    echo "   - Reason: Authentication failure\n";
                } elseif (str_contains($redirectUrl, '/admin/dashboard')) {
                    echo "   - Reason: Organization/permission check\n";
                } else {
                    echo "   - Reason: Unknown redirect\n";
                }
            } else {
                echo "   - Returns view/response successfully\n";
            }
        } else {
            echo "   - No index method found\n";
        }
        
    } catch (Exception $e) {
        echo "   - Error: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

// STEP 3: Check for authentication session issues
echo "3. AUTHENTICATION SESSION VERIFICATION\n";
echo "=======================================\n";

echo "Current auth status:\n";
echo "   - Auth::guard('admin')->check(): " . (Auth::guard('admin')->check() ? 'TRUE' : 'FALSE') . "\n";
echo "   - Auth::guard('admin')->user(): " . (Auth::guard('admin')->user() ? 'EXISTS' : 'NULL') . "\n";

if (Auth::guard('admin')->user()) {
    $user = Auth::guard('admin')->user();
    echo "   - User ID: {$user->id}\n";
    echo "   - User Email: {$user->email}\n";
    echo "   - Super Admin: " . ($user->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   - Is Active: " . ($user->is_active ? 'YES' : 'NO') . "\n";
    echo "   - Organization ID: " . ($user->organization_id ?? 'NULL') . "\n";
    echo "   - Branch ID: " . ($user->branch_id ?? 'NULL') . "\n";
}

echo "\n🎯 SIDEBAR REDIRECT SIMULATION COMPLETE\n";
echo "========================================\n";
echo "If controllers are redirecting unexpectedly, the issue is likely in:\n";
echo "1. Controller logic that doesn't properly handle super admin status\n";
echo "2. Session/authentication state not being properly maintained\n";
echo "3. Middleware conflicts or ordering issues\n";
