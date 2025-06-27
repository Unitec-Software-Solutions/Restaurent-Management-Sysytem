<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 DEBUGGING MIDDLEWARE AND SESSION ISSUES\n";
echo "==========================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Http\Middleware\EnhancedAdminAuth;

// Test 1: Check current authentication state
echo "1. CURRENT AUTHENTICATION STATE\n";
echo "================================\n";

$isAuthenticated = Auth::guard('admin')->check();
$user = Auth::guard('admin')->user();

echo "✅ Auth::guard('admin')->check(): " . ($isAuthenticated ? 'TRUE' : 'FALSE') . "\n";
echo "✅ Auth::guard('admin')->user(): " . ($user ? $user->email : 'NULL') . "\n";
echo "✅ Session ID: " . session()->getId() . "\n";
echo "✅ Session has auth data: " . (session()->has('login_admin_' . sha1(Auth::getDefaultDriver())) ? 'YES' : 'NO') . "\n";

// Login again to establish fresh session
echo "\n2. ESTABLISHING FRESH SESSION\n";
echo "==============================\n";

Auth::guard('admin')->logout();
Session::flush();

use App\Services\AdminAuthService;
$authService = new AdminAuthService();
$loginResult = $authService->login('superadmin@rms.com', 'password', false);

if ($loginResult['success']) {
    echo "✅ Fresh login successful\n";
    echo "   - User: {$loginResult['admin']->email}\n";
    echo "   - Session: {$loginResult['session_id']}\n";
    
    $isAuthNow = Auth::guard('admin')->check();
    echo "   - Auth check after login: " . ($isAuthNow ? 'TRUE' : 'FALSE') . "\n";
} else {
    echo "❌ Fresh login failed: {$loginResult['error']}\n";
}

echo "\n3. TESTING MIDDLEWARE MANUALLY\n";
echo "===============================\n";

if (Auth::guard('admin')->check()) {
    $user = Auth::guard('admin')->user();
    echo "✅ User authenticated: {$user->email}\n";
    
    // Create a request to simulate dashboard access
    $request = Request::create('/admin/dashboard', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    // Test the middleware logic step by step
    echo "\nMiddleware checks:\n";
    
    // Check 1: Is admin authenticated?
    $authCheck = Auth::guard('admin')->check();
    echo "   1. Auth::guard('admin')->check(): " . ($authCheck ? 'PASS' : 'FAIL') . "\n";
    
    if ($authCheck) {
        $admin = Auth::guard('admin')->user();
        
        // Check 2: Is admin instance correct?
        $isAdminModel = $admin instanceof \App\Models\Admin;
        echo "   2. User is Admin model: " . ($isAdminModel ? 'PASS' : 'FAIL') . "\n";
        
        if ($isAdminModel) {
            // Check 3: Is admin active?
            echo "   3. Admin is_active: " . ($admin->is_active ? 'PASS' : 'FAIL') . "\n";
            
            // Check 4: Organization/Super Admin check
            $isSuperAdmin = $admin->is_super_admin || $admin->roles()->where('name', 'Super Admin')->exists();
            $orgCheck = $admin->organization_id || $isSuperAdmin;
            echo "   4. Organization check: " . ($orgCheck ? 'PASS' : 'FAIL') . "\n";
            echo "      - Has organization_id: " . ($admin->organization_id ? 'YES' : 'NO') . "\n";
            echo "      - is_super_admin flag: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
            echo "      - Has Super Admin role: " . ($admin->roles()->where('name', 'Super Admin')->exists() ? 'YES' : 'NO') . "\n";
            
            // Test actual middleware
            echo "\n   Testing actual middleware...\n";
            try {
                $middleware = new EnhancedAdminAuth();
                $response = $middleware->handle($request, function ($req) {
                    return response()->json(['status' => 'middleware_passed']);
                });
                
                if ($response->getStatusCode() === 200) {
                    echo "   ✅ Middleware allows access\n";
                } else {
                    echo "   ❌ Middleware blocks access\n";
                    echo "      Status: {$response->getStatusCode()}\n";
                    if ($response->getStatusCode() === 302) {
                        echo "      Redirect to: {$response->headers->get('Location')}\n";
                    }
                }
            } catch (Exception $e) {
                echo "   ❌ Middleware error: {$e->getMessage()}\n";
            }
        }
    }
} else {
    echo "❌ No authenticated user for middleware test\n";
}

echo "\n4. CHECKING SESSION PERSISTENCE\n";
echo "================================\n";

// Check if session data persists across requests
$sessionData = session()->all();
echo "✅ Session data count: " . count($sessionData) . "\n";

// Check specific auth-related session data
$authSessionKey = 'login_admin_' . sha1('admin');
echo "✅ Auth session key '{$authSessionKey}': " . (session()->has($authSessionKey) ? 'EXISTS' : 'MISSING') . "\n";

// Check session storage
$sessionId = session()->getId();
echo "✅ Session ID: {$sessionId}\n";

// For database sessions, check if session exists in DB
if (config('session.driver') === 'database') {
    try {
        $sessionExists = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('id', $sessionId)
            ->exists();
        echo "✅ Session in database: " . ($sessionExists ? 'YES' : 'NO') . "\n";
        
        if ($sessionExists) {
            $sessionRecord = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('id', $sessionId)
                ->first();
            echo "   - Last activity: " . date('Y-m-d H:i:s', $sessionRecord->last_activity) . "\n";
            echo "   - User ID: " . ($sessionRecord->user_id ?? 'NULL') . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Database session check error: {$e->getMessage()}\n";
    }
}

echo "\n5. TESTING SESSION DOMAIN ISSUE\n";
echo "================================\n";

$sessionDomain = config('session.domain');
$appUrl = config('app.url');
echo "✅ Session domain: " . ($sessionDomain ?? 'NULL') . "\n";
echo "✅ App URL: {$appUrl}\n";

// Check if domain mismatch could cause issues
if ($sessionDomain && $sessionDomain !== 'localhost' && !str_contains($appUrl, $sessionDomain)) {
    echo "⚠️ POTENTIAL DOMAIN MISMATCH!\n";
    echo "   Session domain '{$sessionDomain}' doesn't match app URL '{$appUrl}'\n";
    echo "   This could cause session/auth issues\n";
}

echo "\n6. TESTING GUARD CONFIGURATION\n";
echo "===============================\n";

$guardConfig = config('auth.guards.admin');
$providerConfig = config('auth.providers.admins');
$defaultGuard = config('auth.defaults.guard');

echo "✅ Default guard: {$defaultGuard}\n";
echo "✅ Admin guard configuration:\n";
echo "   - Driver: {$guardConfig['driver']}\n";
echo "   - Provider: {$guardConfig['provider']}\n";
echo "✅ Admin provider configuration:\n";
echo "   - Driver: {$providerConfig['driver']}\n";
echo "   - Model: {$providerConfig['model']}\n";

echo "\n7. SIMULATING BROWSER-LIKE REQUEST\n";
echo "===================================\n";

// Create a fresh session and test the full flow
$newSessionStore = app('session.store');
$newSessionStore->start();

// Simulate login POST request
$loginRequest = Request::create('/admin/login', 'POST', [
    'email' => 'superadmin@rms.com',
    'password' => 'password',
    '_token' => $newSessionStore->token()
]);
$loginRequest->setLaravelSession($newSessionStore);

echo "Testing login POST request...\n";
try {
    $loginResponse = app()->handle($loginRequest);
    echo "   - Login response status: {$loginResponse->getStatusCode()}\n";
    
    if ($loginResponse->getStatusCode() === 302) {
        echo "   - Redirected to: {$loginResponse->headers->get('Location')}\n";
        
        // Now test dashboard access with the same session
        $dashRequest = Request::create('/admin/dashboard', 'GET');
        $dashRequest->setLaravelSession($newSessionStore);
        
        echo "\nTesting dashboard GET request with same session...\n";
        $dashResponse = app()->handle($dashRequest);
        echo "   - Dashboard response status: {$dashResponse->getStatusCode()}\n";
        
        if ($dashResponse->getStatusCode() === 302) {
            echo "   - Dashboard redirected to: {$dashResponse->headers->get('Location')}\n";
        } elseif ($dashResponse->getStatusCode() === 200) {
            echo "   ✅ Dashboard accessible!\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Request handling error: {$e->getMessage()}\n";
}

echo "\n🎯 MIDDLEWARE DEBUG SUMMARY\n";
echo "============================\n";

$middlewareIssues = [];

if (!Auth::guard('admin')->check()) {
    $middlewareIssues[] = "Authentication state not maintained";
}

if ($sessionDomain && $sessionDomain !== 'localhost' && !str_contains($appUrl, $sessionDomain)) {
    $middlewareIssues[] = "Session domain mismatch";
}

if (!session()->has($authSessionKey)) {
    $middlewareIssues[] = "Auth session data missing";
}

if (empty($middlewareIssues)) {
    echo "🎉 NO MIDDLEWARE ISSUES FOUND!\n";
    echo "The authentication and middleware should be working.\n";
} else {
    echo "⚠️ MIDDLEWARE ISSUES DETECTED:\n";
    foreach ($middlewareIssues as $issue) {
        echo "   ❌ {$issue}\n";
    }
}

echo "\n🏁 Middleware debugging completed!\n";
