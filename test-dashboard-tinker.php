<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Http\Controllers\AdminController;

echo "🔧 TESTING SUPER ADMIN DASHBOARD ACCESS\n";
echo "=====================================\n";

// Test login
$admin = Admin::where('email', 'superadmin@rms.com')->first();

if (!$admin) {
    echo "❌ Super admin not found\n";
    return;
}

echo "✅ Super admin found: {$admin->email}\n";
echo "   - Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n";
echo "   - is_super_admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
echo "   - Has Super Admin Role: " . ($admin->hasRole('Super Admin') ? 'YES' : 'NO') . "\n";

// Manually authenticate the user
Auth::guard('admin')->login($admin);

if (Auth::guard('admin')->check()) {
    echo "✅ Authentication successful\n";
    
    // Test dashboard access
    echo "\n🎯 Testing dashboard logic:\n";
    
    $isSuperAdmin = $admin->is_super_admin || $admin->hasRole('Super Admin');
    echo "   - Super admin check: " . ($isSuperAdmin ? 'PASS' : 'FAIL') . "\n";
    
    if (!$isSuperAdmin && !$admin->organization_id) {
        echo "   - Would redirect due to missing organization\n";
    } else {
        echo "   - Dashboard access should work\n";
    }
    
    try {
        // Create controller instance and test dashboard
        $controller = new AdminController();
        
        // This will call the dashboard method
        echo "\n🔍 Calling dashboard method...\n";
        $response = $controller->dashboard();
        
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            echo "❌ Dashboard redirected:\n";
            echo "   - Target: {$response->getTargetUrl()}\n";
            $session = session();
            if ($session->has('error')) {
                echo "   - Error: {$session->get('error')}\n";
            }
        } else {
            echo "✅ Dashboard returned successfully!\n";
            echo "   - Response type: " . get_class($response) . "\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Exception: {$e->getMessage()}\n";
        echo "   - File: {$e->getFile()}:{$e->getLine()}\n";
    }
    
} else {
    echo "❌ Authentication failed\n";
}

echo "\n🏁 Test completed!\n";
