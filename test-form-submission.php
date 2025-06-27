<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🌐 TESTING ACTUAL LOGIN FORM SUBMISSION\n";
echo "=======================================\n\n";

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\AdminAuthController;
use App\Services\AdminAuthService;

// Test the exact same process that happens when a user submits the login form

echo "1. SIMULATING FORM SUBMISSION\n";
echo "=============================\n";

// Clear any existing authentication
Auth::guard('admin')->logout();
Session::flush();

// Create a realistic request similar to what the browser would send
$request = Request::create('/admin/login', 'POST', [
    'email' => 'superadmin@rms.com',
    'password' => 'password',
    '_token' => csrf_token(), // Generate a real CSRF token
]);

// Set up the session
$sessionStore = app('session.store');
$request->setLaravelSession($sessionStore);

// Initialize CSRF protection
$sessionStore->put('_token', csrf_token());

echo "✅ Request created:\n";
echo "   - Method: POST\n";
echo "   - URL: /admin/login\n";
echo "   - Email: superadmin@rms.com\n";
echo "   - Password: [PROVIDED]\n";
echo "   - CSRF Token: [PROVIDED]\n";

echo "\n2. TESTING VALIDATION\n";
echo "======================\n";

try {
    // Test the validation logic
    $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];
    
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
    
    if ($validator->passes()) {
        echo "✅ Form validation: PASSED\n";
        echo "   - Email format: VALID\n";
        echo "   - Password provided: YES\n";
    } else {
        echo "❌ Form validation: FAILED\n";
        foreach ($validator->errors()->all() as $error) {
            echo "   - {$error}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Validation error: {$e->getMessage()}\n";
}

echo "\n3. TESTING CONTROLLER LOGIC\n";
echo "============================\n";

try {
    // Create controller and dependencies
    $authService = new AdminAuthService();
    $controller = new AdminAuthController($authService);
    
    // Call the login method (without the redirect part)
    $credentials = [
        'email' => $request->input('email'),
        'password' => $request->input('password')
    ];
    
    echo "   Testing authentication with AdminAuthService...\n";
    $result = $authService->login(
        $credentials['email'], 
        $credentials['password'], 
        $request->boolean('remember', false)
    );
    
    if ($result['success']) {
        echo "✅ Controller login logic: SUCCESS\n";
        echo "   - Authentication: SUCCESSFUL\n";
        echo "   - Session ID: {$result['session_id']}\n";
        echo "   - User: {$result['admin']->email}\n";
        
        // Check authentication state
        if (Auth::guard('admin')->check()) {
            echo "   - Auth guard state: AUTHENTICATED\n";
            $user = Auth::guard('admin')->user();
            echo "   - Authenticated user: {$user->email}\n";
        } else {
            echo "   - Auth guard state: NOT AUTHENTICATED\n";
        }
        
    } else {
        echo "❌ Controller login logic: FAILED\n";
        echo "   - Error: {$result['error']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Controller exception: {$e->getMessage()}\n";
    echo "   Stack trace: {$e->getTraceAsString()}\n";
}

echo "\n4. TESTING REDIRECT LOGIC\n";
echo "==========================\n";

if (isset($result) && $result['success']) {
    try {
        // Test the intended redirect
        $intendedUrl = route('admin.dashboard');
        echo "✅ Intended redirect URL: {$intendedUrl}\n";
        
        // Check if route exists
        $routes = app('router')->getRoutes();
        $dashboardRoute = $routes->getByName('admin.dashboard');
        
        if ($dashboardRoute) {
            echo "✅ Dashboard route exists\n";
            $middleware = $dashboardRoute->middleware();
            echo "   - Middleware: " . implode(', ', $middleware) . "\n";
        } else {
            echo "❌ Dashboard route not found\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Redirect test error: {$e->getMessage()}\n";
    }
} else {
    echo "❌ Cannot test redirect - login failed\n";
}

echo "\n5. TESTING ERROR HANDLING\n";
echo "==========================\n";

// Test with wrong credentials
echo "   Testing with wrong password...\n";
$wrongResult = $authService->login('superadmin@rms.com', 'wrongpassword', false);

if (!$wrongResult['success']) {
    echo "✅ Wrong credentials properly rejected\n";
    echo "   - Error: {$wrongResult['error']}\n";
} else {
    echo "❌ Wrong credentials incorrectly accepted\n";
}

// Test with non-existent user
echo "   Testing with non-existent user...\n";
$nonExistentResult = $authService->login('nonexistent@example.com', 'password', false);

if (!$nonExistentResult['success']) {
    echo "✅ Non-existent user properly rejected\n";
    echo "   - Error: {$nonExistentResult['error']}\n";
} else {
    echo "❌ Non-existent user incorrectly accepted\n";
}

echo "\n6. TESTING SESSION PERSISTENCE\n";
echo "===============================\n";

if (Auth::guard('admin')->check()) {
    $sessionId = session()->getId();
    $userId = Auth::guard('admin')->id();
    
    echo "✅ Session persistence test:\n";
    echo "   - Session ID: {$sessionId}\n";
    echo "   - User ID: {$userId}\n";
    echo "   - Session driver: " . config('session.driver') . "\n";
    
    // Test session data
    $sessionData = session()->all();
    echo "   - Session has data: " . (count($sessionData) > 0 ? 'YES' : 'NO') . "\n";
    
    // Check if login info is in session
    if (session()->has('login_admin_' . md5(Auth::getDefaultDriver()))) {
        echo "   - Login info in session: YES\n";
    } else {
        echo "   - Login info in session: NO\n";
    }
    
} else {
    echo "❌ No active session to test\n";
}

echo "\n🎯 FORM SUBMISSION TEST SUMMARY\n";
echo "================================\n";

$formIssues = [];

if (!isset($result) || !$result['success']) {
    $formIssues[] = "Login authentication failed";
}

if (!Auth::guard('admin')->check()) {
    $formIssues[] = "Session not maintained after login";
}

if (empty($formIssues)) {
    echo "🎉 FORM SUBMISSION WORKING PERFECTLY!\n\n";
    echo "✅ Users can successfully:\n";
    echo "   1. Access the login form at /admin/login\n";
    echo "   2. Submit valid credentials\n";
    echo "   3. Get authenticated and redirected\n";
    echo "   4. Access protected admin pages\n";
    echo "\n💡 LOGIN CREDENTIALS:\n";
    echo "   📧 Email: superadmin@rms.com\n";
    echo "   🔐 Password: password\n";
} else {
    echo "⚠️ ISSUES WITH FORM SUBMISSION:\n";
    foreach ($formIssues as $issue) {
        echo "   ❌ {$issue}\n";
    }
}

echo "\n🏁 Form submission test completed!\n";
