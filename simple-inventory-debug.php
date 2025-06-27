<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 SIMPLE INVENTORY/SUPPLIER AUTH TEST\n";
echo "=====================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;
use App\Http\Middleware\EnhancedAdminAuth;

// Test 1: Login
echo "1. LOGGING IN\n";
echo "=============\n";

Auth::guard('admin')->logout();

$authService = new AdminAuthService();
$result = $authService->login('superadmin@rms.com', 'password', false);

if ($result['success']) {
    echo "✅ Login successful\n";
    $admin = $result['admin'];
    echo "   - User: {$admin->email}\n";
    echo "   - is_super_admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
} else {
    echo "❌ Login failed\n";
    exit(1);
}

echo "\n";

// Test 2: Test middleware for different routes
echo "2. TESTING MIDDLEWARE ON DIFFERENT ROUTES\n";
echo "==========================================\n";

$routes = [
    'Dashboard (working)' => '/admin/dashboard',
    'Inventory (failing)' => '/admin/inventory',
    'Suppliers (failing)' => '/admin/suppliers',
    'Profile (working)' => '/admin/profile'
];

$middleware = new EnhancedAdminAuth();

foreach ($routes as $label => $uri) {
    echo "Testing {$label}: {$uri}\n";
    
    $request = Request::create($uri, 'GET');
    $request->setLaravelSession(app('session.store'));
    
    try {
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['status' => 'middleware_passed']);
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
    }
    
    echo "\n";
}

// Test 3: Test actual application handling
echo "3. TESTING FULL APPLICATION REQUEST HANDLING\n";
echo "============================================\n";

foreach ($routes as $label => $uri) {
    echo "Testing {$label}: {$uri}\n";
    
    try {
        $request = Request::create($uri, 'GET');
        $request->setLaravelSession(app('session.store'));
        $request->headers->set('Accept', 'text/html');
        
        $response = app()->handle($request);
        
        echo "   - Status: {$response->getStatusCode()}\n";
        
        if ($response->getStatusCode() === 302) {
            echo "   - Redirect to: {$response->headers->get('Location')}\n";
        } elseif ($response->getStatusCode() === 200) {
            echo "   - ✅ Success\n";
        }
        
    } catch (Exception $e) {
        echo "   - ❌ Exception: {$e->getMessage()}\n";
        echo "     File: {$e->getFile()}:{$e->getLine()}\n";
    }
    
    echo "\n";
}

// Test 4: Check current auth state
echo "4. FINAL AUTH STATE CHECK\n";
echo "=========================\n";

echo "Auth::guard('admin')->check(): " . (Auth::guard('admin')->check() ? 'TRUE' : 'FALSE') . "\n";

if (Auth::guard('admin')->check()) {
    $user = Auth::guard('admin')->user();
    echo "User: {$user->email} (ID: {$user->id})\n";
    echo "Super Admin: " . ($user->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "Active: " . ($user->is_active ? 'YES' : 'NO') . "\n";
    echo "Organization: " . ($user->organization_id ?? 'NULL') . "\n";
}

echo "\n";
