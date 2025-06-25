<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Admin;
use App\Models\Role;
use App\Services\AdminAuthService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 COMPREHENSIVE LOGIN FUNCTIONALITY TEST\n";
echo "==========================================\n\n";

// Test 1: Check if super admin exists
echo "1. CHECKING SUPER ADMIN EXISTENCE...\n";
$superAdmin = Admin::where('email', 'superadmin@rms.com')->first();

if ($superAdmin) {
    echo "✅ Super admin found:\n";
    echo "   - ID: {$superAdmin->id}\n";
    echo "   - Email: {$superAdmin->email}\n";
    echo "   - Name: {$superAdmin->name}\n";
    echo "   - Organization ID: " . ($superAdmin->organization_id ?? 'NULL') . "\n";
    echo "   - Created: {$superAdmin->created_at}\n";
    
    // Check password
    $testPassword = 'password';
    if (Hash::check($testPassword, $superAdmin->password)) {
        echo "✅ Password verification: CORRECT\n";
    } else {
        echo "❌ Password verification: INCORRECT\n";
        echo "   Expected: {$testPassword}\n";
        echo "   Hash: {$superAdmin->password}\n";
    }
    
    // Check roles
    $roles = $superAdmin->roles()->get();
    echo "   - Roles: ";
    if ($roles->count() > 0) {
        echo $roles->pluck('name')->join(', ') . "\n";
    } else {
        echo "NONE\n";
    }
} else {
    echo "❌ Super admin NOT found\n";
}

echo "\n";

// Test 2: Check authentication configuration
echo "2. CHECKING AUTH CONFIGURATION...\n";
$adminGuardConfig = config('auth.guards.admin');
$adminProviderConfig = config('auth.providers.admins');
$defaultGuard = config('auth.defaults.guard');

echo "✅ Auth configuration:\n";
echo "   - Default guard: {$defaultGuard}\n";
echo "   - Admin guard driver: {$adminGuardConfig['driver']}\n";
echo "   - Admin guard provider: {$adminGuardConfig['provider']}\n";
echo "   - Admin provider driver: {$adminProviderConfig['driver']}\n";
echo "   - Admin provider model: {$adminProviderConfig['model']}\n";

echo "\n";

// Test 3: Test AdminAuthService login
echo "3. TESTING ADMIN AUTH SERVICE LOGIN...\n";
if ($superAdmin) {
    $authService = new AdminAuthService();
    
    // Clear any existing sessions
    Auth::guard('admin')->logout();
    Session::flush();
    
    echo "   Testing login with superadmin@rms.com / password...\n";
    $result = $authService->login('superadmin@rms.com', 'password', false);
    
    if ($result['success']) {
        echo "✅ Login successful via AuthService\n";
        echo "   - Admin ID: {$result['admin']->id}\n";
        echo "   - Session ID: {$result['session_id']}\n";
        
        // Check if auth guard recognizes the login
        $isAuthenticated = Auth::guard('admin')->check();
        $authenticatedUser = Auth::guard('admin')->user();
        
        echo "   - Guard check: " . ($isAuthenticated ? 'AUTHENTICATED' : 'NOT AUTHENTICATED') . "\n";
        if ($authenticatedUser) {
            echo "   - Authenticated user: {$authenticatedUser->email}\n";
        }
    } else {
        echo "❌ Login failed via AuthService\n";
        echo "   - Error: {$result['error']}\n";
    }
} else {
    echo "❌ Cannot test login - super admin not found\n";
}

echo "\n";

// Test 4: Test direct Auth::attempt
echo "4. TESTING DIRECT AUTH ATTEMPT...\n";
if ($superAdmin) {
    // Clear session first
    Auth::guard('admin')->logout();
    Session::flush();
    
    $credentials = [
        'email' => 'superadmin@rms.com',
        'password' => 'password'
    ];
    
    echo "   Testing Auth::guard('admin')->attempt()...\n";
    $attemptResult = Auth::guard('admin')->attempt($credentials);
    
    if ($attemptResult) {
        echo "✅ Direct auth attempt successful\n";
        $user = Auth::guard('admin')->user();
        echo "   - Authenticated user: {$user->email}\n";
        echo "   - Session ID: " . session()->getId() . "\n";
    } else {
        echo "❌ Direct auth attempt failed\n";
    }
} else {
    echo "❌ Cannot test direct auth - super admin not found\n";
}

echo "\n";

// Test 5: Check roles table
echo "5. CHECKING ROLES TABLE...\n";
$roles = Role::all();
echo "   Total roles: {$roles->count()}\n";
foreach ($roles as $role) {
    $orgName = $role->organization_id ? 
        \App\Models\Organization::find($role->organization_id)->name ?? "Unknown Org" : 
        'Global';
    echo "   - {$role->name} (Guard: {$role->guard_name}, Org: {$orgName})\n";
}

echo "\n";

// Test 6: Check middleware
echo "6. CHECKING MIDDLEWARE FUNCTIONALITY...\n";
if (class_exists('\App\Http\Middleware\EnhancedAdminAuth')) {
    echo "✅ EnhancedAdminAuth middleware exists\n";
    
    // Simulate middleware check
    if ($superAdmin && Auth::guard('admin')->check()) {
        $user = Auth::guard('admin')->user();
        echo "   - Current authenticated user: {$user->email}\n";
        echo "   - User organization_id: " . ($user->organization_id ?? 'NULL') . "\n";
        
        // Check if user has Super Admin role
        $hasSuperAdminRole = $user->roles()->where('name', 'Super Admin')->exists();
        echo "   - Has Super Admin role: " . ($hasSuperAdminRole ? 'YES' : 'NO') . "\n";
        
        if ($hasSuperAdminRole || $user->organization_id) {
            echo "✅ Middleware check would PASS\n";
        } else {
            echo "❌ Middleware check would FAIL\n";
        }
    } else {
        echo "❌ No authenticated user for middleware test\n";
    }
} else {
    echo "❌ EnhancedAdminAuth middleware not found\n";
}

echo "\n";

// Test 7: Test route accessibility
echo "7. CHECKING ROUTE CONFIGURATION...\n";
try {
    $routes = app('router')->getRoutes();
    $adminRoutes = [];
    
    foreach ($routes as $route) {
        $name = $route->getName();
        if ($name && str_starts_with($name, 'admin.')) {
            $adminRoutes[] = [
                'name' => $name,
                'uri' => $route->uri(),
                'methods' => implode('|', $route->methods()),
                'middleware' => implode(',', $route->middleware())
            ];
        }
    }
    
    echo "✅ Found " . count($adminRoutes) . " admin routes:\n";
    foreach (array_slice($adminRoutes, 0, 10) as $route) {
        echo "   - {$route['name']}: {$route['uri']} [{$route['methods']}]\n";
    }
    
    if (count($adminRoutes) > 10) {
        echo "   ... and " . (count($adminRoutes) - 10) . " more\n";
    }
} catch (Exception $e) {
    echo "❌ Could not check routes: {$e->getMessage()}\n";
}

echo "\n";

// Test 8: Database connectivity test
echo "8. TESTING DATABASE CONNECTIVITY...\n";
try {
    $adminCount = Admin::count();
    $roleCount = Role::count();
    $orgCount = \App\Models\Organization::count();
    
    echo "✅ Database connectivity OK\n";
    echo "   - Admins: {$adminCount}\n";
    echo "   - Roles: {$roleCount}\n";
    echo "   - Organizations: {$orgCount}\n";
} catch (Exception $e) {
    echo "❌ Database connectivity issue: {$e->getMessage()}\n";
}

echo "\n";

// Summary
echo "🎯 SUMMARY\n";
echo "==========\n";
if ($superAdmin) {
    echo "✅ Super admin exists\n";
} else {
    echo "❌ Super admin missing\n";
}

if (isset($result) && $result['success']) {
    echo "✅ Login service works\n";
} else {
    echo "❌ Login service has issues\n";
}

if (isset($attemptResult) && $attemptResult) {
    echo "✅ Direct auth works\n";
} else {
    echo "❌ Direct auth has issues\n";
}

echo "\n🏁 Test completed!\n";
