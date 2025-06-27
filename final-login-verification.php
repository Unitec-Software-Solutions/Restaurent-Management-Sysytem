<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🎯 FINAL LOGIN VERIFICATION TEST\n";
echo "================================\n\n";

use App\Models\Admin;
use App\Models\Role;
use App\Services\AdminAuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

// Test data
$testCredentials = [
    'email' => 'superadmin@rms.com',
    'password' => 'password'
];

echo "📊 SYSTEM STATUS CHECK\n";
echo "======================\n";

// 1. Database checks
$admin = Admin::where('email', $testCredentials['email'])->first();
$superAdminRole = Role::where('name', 'Super Admin')->where('organization_id', null)->first();

echo "1. Database Status:\n";
echo "   ✅ Super Admin exists: " . ($admin ? 'YES' : 'NO') . "\n";
echo "   ✅ Super Admin role exists: " . ($superAdminRole ? 'YES' : 'NO') . "\n";

if ($admin) {
    echo "   ✅ Admin active: " . ($admin->is_active ? 'YES' : 'NO') . "\n";
    echo "   ✅ Admin super flag: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   ✅ Password valid: " . (Hash::check($testCredentials['password'], $admin->password) ? 'YES' : 'NO') . "\n";
    
    $adminRoles = $admin->roles()->pluck('name')->toArray();
    echo "   ✅ Admin roles: " . (empty($adminRoles) ? 'NONE' : implode(', ', $adminRoles)) . "\n";
}

echo "\n";

// 2. Authentication tests
echo "2. Authentication Tests:\n";

// Clear any existing auth
Auth::guard('admin')->logout();

$authService = new AdminAuthService();

// Test login
$loginResult = $authService->login($testCredentials['email'], $testCredentials['password'], false);

echo "   ✅ Login attempt: " . ($loginResult['success'] ? 'SUCCESS' : 'FAILED') . "\n";

if ($loginResult['success']) {
    echo "   ✅ Session created: YES\n";
    echo "   ✅ User authenticated: " . (Auth::guard('admin')->check() ? 'YES' : 'NO') . "\n";
    
    $authUser = Auth::guard('admin')->user();
    if ($authUser) {
        echo "   ✅ Authenticated user: {$authUser->email}\n";
        echo "   ✅ User has Super Admin role: " . ($authUser->hasRole('Super Admin', 'admin') ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "   ❌ Login error: " . ($loginResult['error'] ?? 'Unknown') . "\n";
}

echo "\n";

// 3. Middleware test
echo "3. Middleware Tests:\n";

if (Auth::guard('admin')->check()) {
    $user = Auth::guard('admin')->user();
    
    // Test conditions that middleware checks
    echo "   ✅ User is active: " . ($user->is_active ? 'YES' : 'NO') . "\n";
    echo "   ✅ User is Admin model: " . ($user instanceof \App\Models\Admin ? 'YES' : 'NO') . "\n";
      $isSuperAdmin = $user->is_super_admin || $user->roles()->where('name', 'Super Admin')->exists();
    echo "   ✅ User is super admin: " . ($isSuperAdmin ? 'YES' : 'NO') . "\n";echo "   ✅ Organization check: " . ($user->organization_id || $isSuperAdmin ? 'PASS' : 'FAIL') . "\n";
    
    // Simulate middleware
    $request = Request::create('/admin/dashboard', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    $middleware = new \App\Http\Middleware\EnhancedAdminAuth();
    try {
        $result = $middleware->handle($request, function ($req) {
            return response()->json(['status' => 'allowed']);
        });
        
        echo "   ✅ Middleware result: " . ($result->getStatusCode() === 200 ? 'ALLOWED' : 'BLOCKED') . "\n";
    } catch (Exception $e) {
        echo "   ❌ Middleware error: {$e->getMessage()}\n";
    }
} else {
    echo "   ❌ No authenticated user for middleware test\n";
}

echo "\n";

// 4. Route tests
echo "4. Route Tests:\n";

try {
    $loginRoute = route('admin.login');
    $dashboardRoute = route('admin.dashboard');
    
    echo "   ✅ Login route: {$loginRoute}\n";
    echo "   ✅ Dashboard route: {$dashboardRoute}\n";
    
    // Check if routes have proper middleware
    $routes = app('router')->getRoutes();
    $adminLoginRoute = $routes->getByName('admin.login');
    $adminDashboardRoute = $routes->getByName('admin.dashboard');
    
    if ($adminLoginRoute) {
        $loginMiddleware = $adminLoginRoute->middleware();
        echo "   ✅ Login middleware: " . (empty($loginMiddleware) ? 'NONE' : implode(', ', $loginMiddleware)) . "\n";
    }
    
    if ($adminDashboardRoute) {
        $dashboardMiddleware = $adminDashboardRoute->middleware();
        echo "   ✅ Dashboard middleware: " . (empty($dashboardMiddleware) ? 'NONE' : implode(', ', $dashboardMiddleware)) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Route error: {$e->getMessage()}\n";
}

echo "\n";

// 5. Configuration checks
echo "5. Configuration Checks:\n";

$sessionDriver = config('session.driver');
$sessionLifetime = config('session.lifetime');
$defaultGuard = config('auth.defaults.guard');
$adminGuard = config('auth.guards.admin');

echo "   ✅ Session driver: {$sessionDriver}\n";
echo "   ✅ Session lifetime: {$sessionLifetime} minutes\n";
echo "   ✅ Default guard: {$defaultGuard}\n";
echo "   ✅ Admin guard driver: {$adminGuard['driver']}\n";
echo "   ✅ Admin guard provider: {$adminGuard['provider']}\n";

echo "\n";

// Final verdict
echo "🏆 FINAL VERDICT\n";
echo "================\n";

$allGood = true;
$issues = [];

if (!$admin) {
    $allGood = false;
    $issues[] = "Super admin account missing";
}

if (!$superAdminRole) {
    $allGood = false;
    $issues[] = "Super Admin role missing";
}

if ($admin && !$admin->is_active) {
    $allGood = false;
    $issues[] = "Super admin account inactive";
}

if (!isset($loginResult) || !$loginResult['success']) {
    $allGood = false;
    $issues[] = "Login functionality not working";
}

if (!Auth::guard('admin')->check()) {
    $allGood = false;
    $issues[] = "Authentication state not persisting";
}

if ($allGood) {
    echo "🎉 ALL SYSTEMS GO! Login functionality is working perfectly.\n\n";
    echo "✅ You can now login with:\n";
    echo "   📧 Email: superadmin@rms.com\n";
    echo "   🔐 Password: password\n";
    echo "   🌐 URL: " . route('admin.login') . "\n";
} else {
    echo "⚠️ ISSUES DETECTED:\n";
    foreach ($issues as $issue) {
        echo "   ❌ {$issue}\n";
    }
}

echo "\n🏁 Verification completed!\n";
