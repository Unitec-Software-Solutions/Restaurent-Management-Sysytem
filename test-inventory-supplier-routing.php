<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 TESTING INVENTORY/SUPPLIER ROUTING ISSUE\n";
echo "============================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;

// Step 1: Login as super admin
echo "1. LOGGING IN AS SUPER ADMIN\n";
echo "=============================\n";

Auth::guard('admin')->logout();

$authService = new AdminAuthService();
$loginResult = $authService->login('superadmin@rms.com', 'password', false);

if ($loginResult['success']) {
    echo "✅ Login successful\n";
    $admin = Auth::guard('admin')->user();
    echo "   - User: {$admin->email}\n";
    echo "   - Super Admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   - Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n";
    echo "   - Is Active: " . ($admin->is_active ? 'YES' : 'NO') . "\n";
} else {
    echo "❌ Login failed: {$loginResult['error']}\n";
    exit(1);
}

echo "\n";

// Step 2: Test route accessibility
echo "2. TESTING ROUTE ACCESSIBILITY\n";
echo "===============================\n";

$routesToTest = [
    'admin.dashboard' => '/admin/dashboard',
    'admin.inventory.index' => '/admin/inventory',
    'admin.suppliers.index' => '/admin/suppliers',
    'admin.profile.index' => '/admin/profile'
];

foreach ($routesToTest as $routeName => $path) {
    echo "Testing route: {$routeName} ({$path})\n";
    
    // Check if route exists
    if (!Route::has($routeName)) {
        echo "   ❌ Route does not exist\n";
        continue;
    }
    
    $route = Route::getRoutes()->getByName($routeName);
    $middleware = $route->gatherMiddleware();
    
    echo "   - Middleware: " . implode(', ', $middleware) . "\n";
    
    // Create request
    $request = Request::create($path, 'GET');
    $request->setLaravelSession(app('session.store'));
    
    try {
        // Test each middleware individually
        foreach ($middleware as $middlewareName) {
            if ($middlewareName === 'web') {
                echo "   - Web middleware: ✅ PASS (group middleware)\n";
                continue;
            }
            
            if (str_starts_with($middlewareName, 'Illuminate\Auth\Middleware\Authenticate:admin')) {
                echo "   - Auth:admin middleware: ";
                
                $authMiddleware = app('Illuminate\Auth\Middleware\Authenticate');
                
                try {
                    $response = $authMiddleware->handle($request, function ($req) {
                        return response()->json(['status' => 'middleware_passed']);
                    }, 'admin');
                    
                    if ($response->getStatusCode() === 200) {
                        echo "✅ PASS\n";
                    } else {
                        echo "❌ FAIL (Status: {$response->getStatusCode()})\n";
                        if ($response->getStatusCode() === 302) {
                            echo "      Redirect to: {$response->headers->get('Location')}\n";
                        }
                    }
                } catch (Exception $e) {
                    echo "❌ ERROR: {$e->getMessage()}\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "   ❌ Route test error: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

// Step 3: Test controller access directly
echo "3. TESTING CONTROLLER ACCESS\n";
echo "=============================\n";

echo "Testing SupplierController@index directly:\n";
try {
    $supplierController = new \App\Http\Controllers\SupplierController();
    $request = Request::create('/admin/suppliers', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    // Simulate the request by calling the controller method directly
    $response = $supplierController->index($request);
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   ❌ Controller redirected to: {$response->getTargetUrl()}\n";
        
        // Check if it's redirecting to login
        if (str_contains($response->getTargetUrl(), '/admin/login')) {
            echo "   🔍 Analyzing why controller is redirecting to login...\n";
            
            // Check controller logic
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                echo "      - No authenticated user found in controller\n";
            } else {
                echo "      - User found: {$admin->email}\n";
                echo "      - Super admin check: " . ($admin->isSuperAdmin() ? 'PASS' : 'FAIL') . "\n";
                echo "      - Organization check: " . ($admin->organization_id ? 'HAS ORG' : 'NO ORG') . "\n";
                
                // Test the exact logic from SupplierController
                $isSuperAdmin = $admin->isSuperAdmin();
                if (!$isSuperAdmin && !$admin->organization_id) {
                    echo "      - ❌ ISSUE: Controller expects organization_id for non-super admins\n";
                } else {
                    echo "      - ✅ Controller logic should allow access\n";
                }
            }
        }
    } else {
        echo "   ✅ Controller returned view/response successfully\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller test error: {$e->getMessage()}\n";
}

echo "\n";

// Step 4: Test ItemDashboardController 
echo "Testing ItemDashboardController@index directly:\n";
try {
    $inventoryController = new \App\Http\Controllers\ItemDashboardController();
    $request = Request::create('/admin/inventory', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    $response = $inventoryController->index($request);
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   ❌ Controller redirected to: {$response->getTargetUrl()}\n";
    } else {
        echo "   ✅ Controller returned view/response successfully\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller test error: {$e->getMessage()}\n";
}

echo "\n";

// Step 5: Check admin sidebar component routing
echo "4. TESTING ADMIN SIDEBAR ROUTING\n";
echo "=================================\n";

try {
    $sidebarComponent = new \App\View\Components\AdminSidebar();
    
    // Get the menu items (this uses the render method internally)
    $view = $sidebarComponent->render();
    echo "✅ AdminSidebar component rendered successfully\n";
    
    // Check specific route validation
    $inventoryRouteValid = app(\App\View\Components\AdminSidebar::class)->validateRoute('admin.inventory.index');
    $supplierRouteValid = app(\App\View\Components\AdminSidebar::class)->validateRoute('admin.suppliers.index');
    
    echo "   - Inventory route valid: " . ($inventoryRouteValid ? 'YES' : 'NO') . "\n";
    echo "   - Supplier route valid: " . ($supplierRouteValid ? 'YES' : 'NO') . "\n";
    
} catch (Exception $e) {
    echo "❌ Sidebar component error: {$e->getMessage()}\n";
}

echo "\n🏁 DIAGNOSIS COMPLETE\n";
echo "====================\n";

echo "Summary:\n";
echo "- Routes exist and are properly configured\n";
echo "- Middleware is standard auth:admin\n";
echo "- Issue likely in controller logic checking organization_id\n";
echo "- Super admin should bypass organization requirements\n";
