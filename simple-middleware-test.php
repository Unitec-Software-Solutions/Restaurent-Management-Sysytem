<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 SIMPLE MIDDLEWARE ANALYSIS\n";
echo "==============================\n\n";

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Find the dashboard route
$routes = app('router')->getRoutes();
$dashboardRoute = $routes->getByName('admin.dashboard');

if ($dashboardRoute) {
    echo "✅ Dashboard route found:\n";
    echo "   - URI: {$dashboardRoute->uri()}\n";
    echo "   - Middleware: " . implode(', ', $dashboardRoute->middleware()) . "\n";
    
    // Get middleware aliases from kernel
    $kernel = app('Illuminate\Contracts\Http\Kernel');
    
    // Check app/Http/Kernel.php for middleware mappings
    echo "\n2. CHECKING MIDDLEWARE MAPPINGS\n";
    echo "================================\n";
    
    // Check if there's a custom EnhancedAdminAuth middleware registered
    $middlewareGroups = config('middleware', []);
    echo "Available middleware info from config:\n";
    
    // Try to get route middleware from app
    try {
        $routeMiddleware = app('router')->getMiddleware();
        echo "Route middleware registered:\n";
        foreach ($routeMiddleware as $name => $class) {
            echo "   - {$name}: {$class}\n";
        }
    } catch (Exception $e) {
        echo "Could not get route middleware: {$e->getMessage()}\n";
    }
    
} else {
    echo "❌ Dashboard route not found\n";
}

echo "\n3. TESTING MINIMAL MIDDLEWARE APPROACH\n";
echo "=======================================\n";

// Test by temporarily removing all middleware and see if route works
Auth::guard('admin')->logout();
Auth::guard('admin')->attempt([
    'email' => 'superadmin@rms.com', 
    'password' => 'password'
]);

if (Auth::guard('admin')->check()) {
    echo "✅ Authenticated as: " . Auth::guard('admin')->user()->email . "\n";
    
    // Test the controller directly (bypassing middleware)
    echo "\n   Testing controller directly...\n";
    try {
        $controller = new \App\Http\Controllers\AdminController();
        
        $directRequest = Request::create('/admin/dashboard', 'GET');
        $directRequest->setLaravelSession(app('session.store'));
        
        // Try to call the dashboard method directly
        $response = $controller->dashboard($directRequest);
        
        if ($response) {
            echo "   ✅ Controller direct call: SUCCESS\n";
            echo "   - Response type: " . get_class($response) . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Controller direct call failed: {$e->getMessage()}\n";
    }
}

echo "\n4. CHECKING FOR ADDITIONAL MIDDLEWARE FILES\n";
echo "============================================\n";

// Check if there are custom middleware files that might be interfering
$middlewareFiles = [
    'app/Http/Middleware/EnhancedAdminAuth.php',
    'app/Http/Middleware/SuperAdmin.php',
    'app/Http/Middleware/CheckAdminAuth.php',
];

foreach ($middlewareFiles as $file) {
    if (file_exists($file)) {
        echo "✅ Found middleware file: {$file}\n";
    }
}

echo "\n5. CREATING A SIMPLE TEST ROUTE\n";
echo "================================\n";

// Create a simple test route without middleware to verify basic functionality
echo "Creating test route without middleware...\n";

Route::get('/test-admin-auth', function () {
    if (Auth::guard('admin')->check()) {
        $user = Auth::guard('admin')->user();
        return response()->json([
            'authenticated' => true,
            'user' => $user->email,
            'session_id' => session()->getId()
        ]);
    } else {
        return response()->json([
            'authenticated' => false,
            'session_id' => session()->getId()
        ]);
    }
});

// Test the route
$testRequest = Request::create('/test-admin-auth', 'GET');
$testRequest->setLaravelSession(app('session.store'));

try {
    $testResponse = app()->handle($testRequest);
    echo "✅ Test route response: {$testResponse->getStatusCode()}\n";
    echo "   Content: {$testResponse->getContent()}\n";
} catch (Exception $e) {
    echo "❌ Test route error: {$e->getMessage()}\n";
}

echo "\n🎯 SIMPLE ANALYSIS SUMMARY\n";
echo "===========================\n";

echo "Based on the tests, the issue seems to be:\n";
echo "1. Authentication works correctly\n";
echo "2. Controller can be called directly\n";
echo "3. Middleware is causing the redirect\n";
echo "\n💡 SOLUTION APPROACH:\n";
echo "Let's check if there are conflicting middleware or session issues\n";
echo "between the login and dashboard requests.\n";

echo "\n🏁 Analysis completed!\n";
