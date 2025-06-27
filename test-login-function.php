<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';

// Boot the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 COMPREHENSIVE LOGIN FUNCTION TEST\n";
echo "====================================\n\n";

try {
    // Clear any existing sessions first
    session()->flush();
    
    echo "🧪 Testing Admin Login Functionality\n";
    echo "------------------------------------\n\n";
    
    // Test 1: Check if admin exists
    echo "1️⃣ Testing Super Admin Existence:\n";
    $admin = \App\Models\Admin::where('email', 'superadmin@rms.com')->first();
    if ($admin) {
        echo "   ✅ Super Admin found: {$admin->name}\n";
        echo "   📧 Email: {$admin->email}\n";
        echo "   🔑 Is Super Admin: " . ($admin->is_super_admin ? 'Yes' : 'No') . "\n";
        echo "   ✅ Is Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
        echo "   🏢 Organization ID: " . ($admin->organization_id ?? 'null (OK for super admin)') . "\n";
        echo "   🏭 Branch ID: " . ($admin->branch_id ?? 'null (OK for super admin)') . "\n";
    } else {
        echo "   ❌ Super Admin NOT FOUND!\n";
        return;
    }
    
    echo "\n";
    
    // Test 2: Check password manually
    echo "2️⃣ Testing Password Verification:\n";
    $passwordTest = \Illuminate\Support\Facades\Hash::check('password', $admin->password);
    echo "   Password 'password': " . ($passwordTest ? '✅ MATCHES' : '❌ DOES NOT MATCH') . "\n";
    
    echo "\n";
    
    // Test 3: Test authentication attempt
    echo "3️⃣ Testing Authentication Attempt:\n";
    
    // Clear any existing auth
    \Illuminate\Support\Facades\Auth::guard('admin')->logout();
    
    $credentials = [
        'email' => 'superadmin@rms.com',
        'password' => 'password'
    ];
    
    $attemptResult = \Illuminate\Support\Facades\Auth::guard('admin')->attempt($credentials);
    echo "   Auth::guard('admin')->attempt(): " . ($attemptResult ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    
    if ($attemptResult) {
        $authenticatedUser = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        echo "   Authenticated user: {$authenticatedUser->name} ({$authenticatedUser->email})\n";
        echo "   Session ID: " . session()->getId() . "\n";
    }
    
    echo "\n";
    
    // Test 4: Test AdminAuthService
    echo "4️⃣ Testing AdminAuthService:\n";
    
    $authService = new \App\Services\AdminAuthService();
    $loginResult = $authService->login('superadmin@rms.com', 'password');
    
    echo "   AdminAuthService login result:\n";
    echo "   - Success: " . ($loginResult['success'] ? '✅ TRUE' : '❌ FALSE') . "\n";
    if (isset($loginResult['error'])) {
        echo "   - Error: " . $loginResult['error'] . "\n";
    }
    if (isset($loginResult['admin'])) {
        echo "   - Admin: " . $loginResult['admin']->name . "\n";
    }
    
    echo "\n";
    
    // Test 5: Check auth configuration
    echo "5️⃣ Testing Auth Configuration:\n";
    echo "   Default guard: " . config('auth.defaults.guard') . "\n";
    echo "   Admin guard provider: " . config('auth.guards.admin.provider') . "\n";
    echo "   Admins provider model: " . config('auth.providers.admins.model') . "\n";
    
    echo "\n";
    
    // Test 6: Check role setup
    echo "6️⃣ Testing Role Setup:\n";
    $roles = \App\Models\Role::all();
    echo "   Total roles: " . $roles->count() . "\n";
    foreach ($roles as $role) {
        echo "   - {$role->name} (org: " . ($role->organization_id ?? 'global') . ")\n";
    }
    
    // Check if super admin has roles
    if ($admin->roles->count() > 0) {
        echo "   Super admin roles:\n";
        foreach ($admin->roles as $role) {
            echo "   - {$role->name}\n";
        }
    } else {
        echo "   ⚠️ Super admin has no roles assigned\n";
    }
    
    echo "\n";
    
    // Test 7: Test route accessibility
    echo "7️⃣ Testing Route Configuration:\n";
    try {
        $loginRoute = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.login');
        echo "   admin.login route: " . ($loginRoute ? '✅ EXISTS' : '❌ MISSING') . "\n";
        if ($loginRoute) {
            echo "   - URI: " . $loginRoute->uri() . "\n";
            echo "   - Methods: " . implode(', ', $loginRoute->methods()) . "\n";
        }
        
        $dashboardRoute = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.dashboard');
        echo "   admin.dashboard route: " . ($dashboardRoute ? '✅ EXISTS' : '❌ MISSING') . "\n";
        if ($dashboardRoute) {
            echo "   - URI: " . $dashboardRoute->uri() . "\n";
            echo "   - Middleware: " . implode(', ', $dashboardRoute->gatherMiddleware()) . "\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ Route check failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Summary
    echo "📋 DIAGNOSIS SUMMARY:\n";
    echo "====================\n";
    
    if ($admin && $passwordTest && $attemptResult && $loginResult['success']) {
        echo "✅ LOGIN FUNCTION IS WORKING CORRECTLY\n";
        echo "✅ Admin exists with correct password\n";
        echo "✅ Authentication attempt succeeds\n";
        echo "✅ AdminAuthService works properly\n";
        echo "\n";
        echo "🎯 RECOMMENDATION: Login should work at /admin/login\n";
    } else {
        echo "❌ LOGIN FUNCTION HAS ISSUES\n";
        echo "\n";
        echo "🔧 ISSUES FOUND:\n";
        if (!$admin) echo "   - Super admin doesn't exist\n";
        if (!$passwordTest) echo "   - Password doesn't match\n";
        if (!$attemptResult) echo "   - Auth attempt fails\n";
        if (!$loginResult['success']) echo "   - AdminAuthService fails\n";
    }

} catch (\Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n";
