<?php

require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

echo "🔧 TESTING SUPER ADMIN DASHBOARD ACCESS\n";
echo "=====================================\n";

echo "1. CHECKING SUPER ADMIN LOGIN\n";
echo "=============================\n";

// Attempt login
$loginResult = Auth::guard('admin')->attempt([
    'email' => 'superadmin@rms.com',
    'password' => 'password123'
]);

if ($loginResult) {
    echo "✅ Login successful\n";
    
    $admin = Auth::guard('admin')->user();
    echo "   - User: {$admin->email}\n";
    echo "   - Name: {$admin->name}\n";
    echo "   - Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n";
    echo "   - is_super_admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   - Has Super Admin Role: " . ($admin->hasRole('Super Admin') ? 'YES' : 'NO') . "\n";
    
    echo "\n2. TESTING DASHBOARD ACCESS LOGIC\n";
    echo "==================================\n";
    
    // Test the dashboard logic manually
    $isSuperAdmin = $admin->is_super_admin || $admin->hasRole('Super Admin');
    echo "   - Super admin check: " . ($isSuperAdmin ? 'PASS' : 'FAIL') . "\n";
    
    if (!$isSuperAdmin && !$admin->organization_id) {
        echo "   - Organization requirement: FAIL (would redirect)\n";
    } else {
        echo "   - Organization requirement: PASS (super admin or has org)\n";
    }
    
    echo "\n3. TESTING DASHBOARD CONTROLLER MANUALLY\n";
    echo "==========================================\n";
    
    try {
        // Simulate the dashboard method logic
        $app = app();
        $controller = new \App\Http\Controllers\AdminController();
        
        // Call dashboard method
        $response = $controller->dashboard();
        
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            echo "❌ Dashboard returned redirect:\n";
            echo "   - Target URL: {$response->getTargetUrl()}\n";
            echo "   - Session errors: " . json_encode(session()->get('errors')) . "\n";
            echo "   - Flash messages: " . json_encode(session()->all()) . "\n";
        } else {
            echo "✅ Dashboard returned view successfully\n";
            echo "   - Response type: " . get_class($response) . "\n";
            if (method_exists($response, 'getData')) {
                echo "   - Data keys: " . implode(', ', array_keys($response->getData())) . "\n";
            }
        }
    } catch (\Exception $e) {
        echo "❌ Dashboard controller error: {$e->getMessage()}\n";
        echo "   - File: {$e->getFile()}:{$e->getLine()}\n";
    }
    
} else {
    echo "❌ Login failed\n";
}

echo "\n4. CHECKING FOR OTHER MIDDLEWARE ISSUES\n";
echo "=======================================\n";

// Check route middleware
$route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.dashboard');
if ($route) {
    echo "✅ Dashboard route found\n";
    echo "   - URI: {$route->uri()}\n";
    echo "   - Methods: " . implode(', ', $route->methods()) . "\n";
    echo "   - Middleware: " . implode(', ', $route->middleware()) . "\n";
    echo "   - Action: {$route->getActionName()}\n";
} else {
    echo "❌ Dashboard route not found\n";
}

echo "\n🏁 Test completed!\n";
