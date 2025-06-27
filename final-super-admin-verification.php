<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Http\Controllers\AdminController;

// Create a minimal Laravel app context
$app = new Application(realpath(__DIR__));
$app->singleton('app', Application::class);

echo "🔧 FINAL SUPER ADMIN VERIFICATION\n";
echo "=================================\n";

echo "1. DATABASE CHECK\n";
echo "=================\n";

try {
    // Check super admin in database
    $admin = Admin::where('email', 'superadmin@rms.com')->first();
    
    if (!$admin) {
        echo "❌ Super admin not found in database\n";
        exit(1);
    }
    
    echo "✅ Super admin found:\n";
    echo "   - Email: {$admin->email}\n";
    echo "   - Name: {$admin->name}\n";
    echo "   - Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n";
    echo "   - is_super_admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   - is_active: " . ($admin->is_active ? 'YES' : 'NO') . "\n";
    echo "   - Password Hash Length: " . strlen($admin->password) . "\n";
    
    // Check roles
    if (method_exists($admin, 'hasRole')) {
        echo "   - Has Super Admin Role: " . ($admin->hasRole('Super Admin') ? 'YES' : 'NO') . "\n";
        if ($admin->roles->count() > 0) {
            echo "   - All Roles: " . $admin->roles->pluck('name')->implode(', ') . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Database error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n2. PASSWORD VERIFICATION\n";
echo "========================\n";

if (\Illuminate\Support\Facades\Hash::check('password123', $admin->password)) {
    echo "✅ Password 'password123' is correct\n";
} else {
    echo "❌ Password 'password123' is incorrect\n";
    echo "   - Trying to update password...\n";
    
    $admin->password = \Illuminate\Support\Facades\Hash::make('password123');
    $admin->save();
    echo "   - Password updated\n";
}

echo "\n3. DASHBOARD LOGIC VERIFICATION\n";
echo "===============================\n";

// Test the exact logic from AdminController::dashboard
$isSuperAdmin = $admin->is_super_admin || (method_exists($admin, 'hasRole') && $admin->hasRole('Super Admin'));

echo "Super admin check result: " . ($isSuperAdmin ? 'PASS' : 'FAIL') . "\n";

if (!$isSuperAdmin && !$admin->organization_id) {
    echo "❌ Would be redirected: missing organization and not super admin\n";
} else {
    echo "✅ Dashboard access: ALLOWED\n";
    echo "   - Reason: " . ($isSuperAdmin ? 'Is super admin' : 'Has organization') . "\n";
}

echo "\n4. ROUTE VERIFICATION\n";
echo "=====================\n";

// Check if routes are properly registered
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $loginRoute = $routes->getByName('admin.login');
    $dashboardRoute = $routes->getByName('admin.dashboard');
    
    if ($loginRoute) {
        echo "✅ Admin login route exists: " . $loginRoute->uri() . "\n";
    } else {
        echo "❌ Admin login route missing\n";
    }
    
    if ($dashboardRoute) {
        echo "✅ Admin dashboard route exists: " . $dashboardRoute->uri() . "\n";
        echo "   - Middleware: " . implode(', ', $dashboardRoute->middleware()) . "\n";
    } else {
        echo "❌ Admin dashboard route missing\n";
    }
    
} catch (\Exception $e) {
    echo "⚠️  Could not verify routes: {$e->getMessage()}\n";
}

echo "\n5. SUMMARY\n";
echo "==========\n";

if ($admin && $admin->is_active && $isSuperAdmin) {
    echo "🎯 EXPECTED RESULT: Super admin login should work!\n";
    echo "\n📋 LOGIN STEPS TO TEST:\n";
    echo "1. Go to: http://localhost:8000/admin/login\n";
    echo "2. Enter email: superadmin@rms.com\n";
    echo "3. Enter password: password123\n";
    echo "4. Should redirect to: http://localhost:8000/admin/dashboard\n";
    echo "\n💡 If still redirecting to login, check:\n";
    echo "   - Browser cookies/session\n";
    echo "   - Laravel logs for any errors\n";
    echo "   - Middleware issues\n";
} else {
    echo "❌ Issues found that will prevent login\n";
}

echo "\n🏁 Verification completed!\n";
