<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 QUICK ADMIN SIDEBAR ISSUE DETECTOR\n";
echo "=====================================\n\n";

use Illuminate\Support\Facades\Auth;
use App\Services\AdminAuthService;

// Login test
Auth::guard('admin')->logout();
$authService = new AdminAuthService();
$loginResult = $authService->login('superadmin@rms.com', 'password', false);

if (!$loginResult['success']) {
    echo "❌ Login failed: {$loginResult['error']}\n";
    exit(1);
}

$admin = Auth::guard('admin')->user();
echo "✅ Logged in: {$admin->email}\n";
echo "   Super Admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
echo "   Organization: " . ($admin->organization_id ?? 'NONE') . "\n\n";

// Test key controllers directly
echo "TESTING KEY CONTROLLERS:\n";
echo "========================\n";

// Test SupplierController
echo "1. SupplierController:\n";
try {
    $controller = new \App\Http\Controllers\SupplierController();
    $request = \Illuminate\Http\Request::create('/admin/suppliers', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    $response = $controller->index($request);
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   ❌ REDIRECTS TO: " . $response->getTargetUrl() . "\n";
        
        // Check the redirect reason by examining the controller logic
        if (str_contains($response->getTargetUrl(), '/admin/login')) {
            echo "   Reason: Auth check failed\n";
        } elseif (str_contains($response->getTargetUrl(), '/admin/dashboard')) {
            echo "   Reason: Organization check failed\n";
        }
    } else {
        echo "   ✅ SUCCESS: Returns view\n";
    }
} catch (Exception $e) {
    echo "   ❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Test ItemDashboardController
echo "2. ItemDashboardController:\n";
try {
    $controller = new \App\Http\Controllers\ItemDashboardController();
    $request = \Illuminate\Http\Request::create('/admin/inventory', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    $response = $controller->index($request);
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   ❌ REDIRECTS TO: " . $response->getTargetUrl() . "\n";
    } else {
        echo "   ✅ SUCCESS: Returns view\n";
    }
} catch (Exception $e) {
    echo "   ❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Test Admin\InventoryController
echo "3. Admin\\InventoryController:\n";
try {
    $controller = new \App\Http\Controllers\Admin\InventoryController();
    $request = \Illuminate\Http\Request::create('/admin/inventory', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    $response = $controller->index();
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   ❌ REDIRECTS TO: " . $response->getTargetUrl() . "\n";
    } else {
        echo "   ✅ SUCCESS: Returns view\n";
    }
} catch (Exception $e) {
    echo "   ❌ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Quick authentication double-check
echo "AUTH DOUBLE-CHECK:\n";
echo "==================\n";
echo "Auth::guard('admin')->check(): " . (Auth::guard('admin')->check() ? 'TRUE' : 'FALSE') . "\n";
$currentUser = Auth::guard('admin')->user();
if ($currentUser) {
    echo "Current user still authenticated: {$currentUser->email}\n";
    echo "isSuperAdmin() method: " . ($currentUser->isSuperAdmin() ? 'TRUE' : 'FALSE') . "\n";
} else {
    echo "❌ No authenticated user found\n";
}

echo "\n🔍 QUICK TEST COMPLETE\n";
