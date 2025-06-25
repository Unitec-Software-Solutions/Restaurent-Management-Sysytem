<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 DEBUGGING AUTH:ADMIN MIDDLEWARE CONFLICT\n";
echo "===========================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;

// Test 1: Login with our service
echo "1. TESTING WITH ADMIN AUTH SERVICE\n";
echo "==================================\n";

Auth::guard('admin')->logout();

$authService = new AdminAuthService();
$result = $authService->login('superadmin@rms.com', 'password', false);

if ($result['success']) {
    echo "✅ AdminAuthService login: SUCCESS\n";
    echo "   - User: {$result['admin']->email}\n";
    echo "   - Session: {$result['session_id']}\n";
    
    // Check authentication immediately
    $authCheck1 = Auth::guard('admin')->check();
    echo "   - Auth check after service login: " . ($authCheck1 ? 'TRUE' : 'FALSE') . "\n";
    
    if ($authCheck1) {
        $user1 = Auth::guard('admin')->user();
        echo "   - User from guard: {$user1->email}\n";
    }
} else {
    echo "❌ AdminAuthService login failed\n";
}

echo "\n2. TESTING DIRECT AUTH::ATTEMPT\n";
echo "================================\n";

// Clear and try with direct Auth::attempt
Auth::guard('admin')->logout();

$attemptResult = Auth::guard('admin')->attempt([
    'email' => 'superadmin@rms.com',
    'password' => 'password'
], false);

if ($attemptResult) {
    echo "✅ Auth::attempt login: SUCCESS\n";
    
    $authCheck2 = Auth::guard('admin')->check();
    echo "   - Auth check after attempt: " . ($authCheck2 ? 'TRUE' : 'FALSE') . "\n";
    
    if ($authCheck2) {
        $user2 = Auth::guard('admin')->user();
        echo "   - User from guard: {$user2->email}\n";
    }
} else {
    echo "❌ Auth::attempt failed\n";
}

echo "\n3. TESTING AUTH:ADMIN MIDDLEWARE DIRECTLY\n";
echo "==========================================\n";

if (Auth::guard('admin')->check()) {
    // Create a request
    $request = Request::create('/admin/dashboard', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    echo "Testing Laravel's built-in auth:admin middleware...\n";
    
    try {
        // Get the auth middleware instance
        $authMiddleware = app('Illuminate\Auth\Middleware\Authenticate');
        
        // Test the middleware
        $response = $authMiddleware->handle($request, function ($req) {
            return response()->json(['status' => 'auth_middleware_passed']);
        }, 'admin');
        
        echo "✅ Auth:admin middleware status: {$response->getStatusCode()}\n";
        
        if ($response->getStatusCode() === 200) {
            echo "   - Auth middleware passed\n";
        } else {
            echo "   - Auth middleware failed\n";
            if ($response->getStatusCode() === 302) {
                echo "   - Redirect to: {$response->headers->get('Location')}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Auth middleware error: {$e->getMessage()}\n";
    }
} else {
    echo "❌ No authenticated user for auth middleware test\n";
}

echo "\n4. COMPARING SESSION STRUCTURES\n";
echo "================================\n";

// Login again and examine session structure
Auth::guard('admin')->logout();

echo "Testing session structure with Auth::attempt...\n";
Auth::guard('admin')->attempt([
    'email' => 'superadmin@rms.com',
    'password' => 'password'
]);

$session1 = session()->all();
echo "Session after Auth::attempt:\n";
foreach ($session1 as $key => $value) {
    if (is_string($value) && strlen($value) > 50) {
        $value = substr($value, 0, 50) . '...';
    } elseif (is_array($value) || is_object($value)) {
        $value = '[' . gettype($value) . ']';
    }
    echo "   - {$key}: {$value}\n";
}

Auth::guard('admin')->logout();
session()->flush();

echo "\nTesting session structure with AdminAuthService...\n";
$authService->login('superadmin@rms.com', 'password', false);

$session2 = session()->all();
echo "Session after AdminAuthService:\n";
foreach ($session2 as $key => $value) {
    if (is_string($value) && strlen($value) > 50) {
        $value = substr($value, 0, 50) . '...';
    } elseif (is_array($value) || is_object($value)) {
        $value = '[' . gettype($value) . ']';
    }
    echo "   - {$key}: {$value}\n";
}

echo "\n5. TESTING ROUTE HANDLING\n";
echo "==========================\n";

// Test the actual route handling
if (Auth::guard('admin')->check()) {
    echo "Testing actual route handling...\n";
    
    // Create request exactly as browser would
    $routeRequest = Request::create('/admin/dashboard', 'GET');
    $routeRequest->setLaravelSession(app('session.store'));
    $routeRequest->headers->set('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
    
    try {
        $routeResponse = app()->handle($routeRequest);
        
        echo "✅ Route response status: {$routeResponse->getStatusCode()}\n";
        
        if ($routeResponse->getStatusCode() === 200) {
            echo "   ✅ Dashboard route accessible!\n";
        } elseif ($routeResponse->getStatusCode() === 302) {
            $location = $routeResponse->headers->get('Location');
            echo "   ❌ Dashboard route redirected to: {$location}\n";
            
            // Check if it's redirecting due to auth failure
            if (str_contains($location, 'login')) {
                echo "   - This suggests auth middleware is failing\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Route handling error: {$e->getMessage()}\n";
    }
}

echo "\n6. CHECKING AUTH CONFIGURATION\n";
echo "===============================\n";

$guards = config('auth.guards');
$providers = config('auth.providers');
$defaults = config('auth.defaults');

echo "Auth configuration:\n";
echo "   - Default guard: {$defaults['guard']}\n";
echo "   - Default passwords: {$defaults['passwords']}\n";

echo "Admin guard config:\n";
foreach ($guards['admin'] as $key => $value) {
    echo "   - {$key}: {$value}\n";
}

echo "Admin provider config:\n";
foreach ($providers['admins'] as $key => $value) {
    echo "   - {$key}: {$value}\n";
}

echo "\n🎯 AUTH MIDDLEWARE DEBUG SUMMARY\n";
echo "=================================\n";

$authIssues = [];

if (!Auth::guard('admin')->check()) {
    $authIssues[] = "Authentication not working";
}

if (isset($routeResponse) && $routeResponse->getStatusCode() !== 200) {
    $authIssues[] = "Route protection blocking access";
}

if (empty($authIssues)) {
    echo "🎉 AUTH MIDDLEWARE WORKING!\n";
} else {
    echo "⚠️ AUTH MIDDLEWARE ISSUES:\n";
    foreach ($authIssues as $issue) {
        echo "   ❌ {$issue}\n";
    }
}

echo "\n🏁 Auth middleware debugging completed!\n";
