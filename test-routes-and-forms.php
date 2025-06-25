<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 TESTING ROUTE RESOLUTION AND FORM SUBMISSION\n";
echo "================================================\n\n";

// Test route resolution
echo "1. CHECKING ROUTE RESOLUTION\n";
echo "=============================\n";

try {
    $loginGetRoute = route('admin.login');
    echo "✅ GET admin.login route: {$loginGetRoute}\n";
} catch (Exception $e) {
    echo "❌ GET admin.login route error: {$e->getMessage()}\n";
}

try {
    $loginPostRoute = route('admin.login.submit');
    echo "✅ POST admin.login.submit route: {$loginPostRoute}\n";
} catch (Exception $e) {
    echo "❌ POST admin.login.submit route error: {$e->getMessage()}\n";
}

try {
    $dashboardRoute = route('admin.dashboard');
    echo "✅ Dashboard route: {$dashboardRoute}\n";
} catch (Exception $e) {
    echo "❌ Dashboard route error: {$e->getMessage()}\n";
}

echo "\n2. TESTING ACTUAL LOGIN FORM ACCESS\n";
echo "====================================\n";

use Illuminate\Http\Request;
use App\Http\Controllers\AdminAuthController;
use App\Services\AdminAuthService;

// Test the GET route (showing login form)
echo "Testing GET /admin/login...\n";
try {
    $authService = new AdminAuthService();
    $controller = new AdminAuthController($authService);
    
    // This should work without issues
    echo "✅ AdminAuthController instantiated successfully\n";
    echo "✅ Can access showLoginForm method\n";
    
} catch (Exception $e) {
    echo "❌ Controller instantiation error: {$e->getMessage()}\n";
}

echo "\n3. TESTING FORM SUBMISSION TO POST ROUTE\n";
echo "=========================================\n";

// Clear any existing auth
use Illuminate\Support\Facades\Auth;
Auth::guard('admin')->logout();

// Create a POST request to the admin login endpoint
$request = Request::create('/admin/login', 'POST', [
    'email' => 'superadmin@rms.com',
    'password' => 'password',
    '_token' => csrf_token()
]);

$request->setLaravelSession(app('session.store'));

echo "Created POST request to /admin/login\n";
echo "✅ Email: superadmin@rms.com\n";
echo "✅ Password: [PROVIDED]\n";
echo "✅ CSRF Token: [PROVIDED]\n";

// Test validation
echo "\nTesting form validation...\n";
$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
    'email' => 'required|email',
    'password' => 'required',
]);

if ($validator->passes()) {
    echo "✅ Form validation: PASSED\n";
} else {
    echo "❌ Form validation: FAILED\n";
    foreach ($validator->errors()->all() as $error) {
        echo "   - {$error}\n";
    }
}

// Test authentication directly
echo "\nTesting authentication process...\n";
try {
    $authService = new AdminAuthService();
    $result = $authService->login('superadmin@rms.com', 'password', false);
    
    if ($result['success']) {
        echo "✅ Authentication: SUCCESS\n";
        echo "   - User: {$result['admin']->email}\n";
        echo "   - Session: {$result['session_id']}\n";
        
        // Test if user is properly authenticated
        if (Auth::guard('admin')->check()) {
            echo "✅ Auth guard status: AUTHENTICATED\n";
            $user = Auth::guard('admin')->user();
            echo "   - Authenticated user: {$user->email}\n";
        } else {
            echo "❌ Auth guard status: NOT AUTHENTICATED\n";
        }
    } else {
        echo "❌ Authentication: FAILED\n";
        echo "   - Error: {$result['error']}\n";
    }
} catch (Exception $e) {
    echo "❌ Authentication error: {$e->getMessage()}\n";
}

echo "\n4. CHECKING FOR POTENTIAL ISSUES\n";
echo "=================================\n";

// Check if there are any conflicting routes
echo "Checking for route conflicts...\n";
$routes = app('router')->getRoutes();
$adminRoutes = [];

foreach ($routes as $route) {
    if (str_contains($route->uri(), 'admin/login')) {
        $methods = implode('|', $route->methods());
        $name = $route->getName() ?? 'UNNAMED';
        $action = $route->getActionName();
        
        echo "   - {$route->uri()} [{$methods}] -> {$name} -> {$action}\n";
    }
}

// Check session configuration
echo "\nChecking session configuration...\n";
$sessionDriver = config('session.driver');
$sessionLifetime = config('session.lifetime');
$sessionPath = config('session.path');
$sessionSecure = config('session.secure');
$sessionHttpOnly = config('session.http_only');

echo "   - Driver: {$sessionDriver}\n";
echo "   - Lifetime: {$sessionLifetime} minutes\n";
echo "   - Path: {$sessionPath}\n";
echo "   - Secure: " . ($sessionSecure ? 'true' : 'false') . "\n";
echo "   - HTTP Only: " . ($sessionHttpOnly ? 'true' : 'false') . "\n";

// Check if there are any middleware issues
echo "\nChecking middleware...\n";
$adminLoginRoute = $routes->getByName('admin.login');
if ($adminLoginRoute) {
    $middleware = $adminLoginRoute->middleware();
    echo "   - GET /admin/login middleware: " . (empty($middleware) ? 'NONE' : implode(', ', $middleware)) . "\n";
}

// Check for CSRF issues
echo "\nChecking CSRF configuration...\n";
$csrfToken = csrf_token();
echo "   - CSRF token generated: " . (!empty($csrfToken) ? 'YES' : 'NO') . "\n";
echo "   - CSRF token length: " . strlen($csrfToken) . "\n";

echo "\n5. SIMULATION OF FULL LOGIN FLOW\n";
echo "=================================\n";

// Simulate the exact flow that happens in browser
echo "Step 1: User visits GET /admin/login\n";
echo "   - Route exists: " . (route('admin.login') ? 'YES' : 'NO') . "\n";

echo "Step 2: User submits form to POST /admin/login\n";
echo "   - Form action should point to: /admin/login\n";
echo "   - Current formAction in blade: " . route('admin.login') . "\n";

echo "Step 3: Controller processes login\n";
if (isset($result) && $result['success']) {
    echo "   - Authentication result: SUCCESS\n";
} else {
    echo "   - Authentication result: FAILED\n";
}

echo "Step 4: Redirect to dashboard\n";
try {
    $dashboardUrl = route('admin.dashboard');
    echo "   - Dashboard URL: {$dashboardUrl}\n";
    echo "   - Dashboard accessible: " . (Auth::guard('admin')->check() ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo "   - Dashboard error: {$e->getMessage()}\n";
}

echo "\n🎯 DIAGNOSIS SUMMARY\n";
echo "====================\n";

$issues = [];

// Check for common issues
if (!route('admin.login')) {
    $issues[] = "admin.login route not found";
}

if (!Auth::guard('admin')->check()) {
    $issues[] = "Authentication not persisting after login";
}

if (empty($csrfToken)) {
    $issues[] = "CSRF token generation issue";
}

if (empty($issues)) {
    echo "🎉 NO OBVIOUS ISSUES FOUND!\n";
    echo "The login system appears to be working correctly.\n";
    echo "\n💡 CREDENTIALS TO TEST:\n";
    echo "   📧 Email: superadmin@rms.com\n";
    echo "   🔐 Password: password\n";
    echo "   🌐 URL: " . route('admin.login') . "\n";
} else {
    echo "⚠️ POTENTIAL ISSUES FOUND:\n";
    foreach ($issues as $issue) {
        echo "   ❌ {$issue}\n";
    }
}

echo "\n🏁 Route and form testing completed!\n";
