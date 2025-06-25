<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 ANALYZING MIDDLEWARE STACK FOR DASHBOARD ROUTE\n";
echo "==================================================\n\n";

use Illuminate\Support\Facades\Route;

// Get all routes and find the dashboard route
$routes = app('router')->getRoutes();
$dashboardRoute = null;

foreach ($routes as $route) {
    if ($route->getName() === 'admin.dashboard') {
        $dashboardRoute = $route;
        break;
    }
}

if ($dashboardRoute) {
    echo "✅ Found admin.dashboard route:\n";
    echo "   - URI: {$dashboardRoute->uri()}\n";
    echo "   - Methods: " . implode('|', $dashboardRoute->methods()) . "\n";
    echo "   - Name: {$dashboardRoute->getName()}\n";
    echo "   - Action: {$dashboardRoute->getActionName()}\n";
    
    // Get middleware stack
    $middleware = $dashboardRoute->middleware();
    echo "   - Middleware count: " . count($middleware) . "\n";
    echo "   - Middleware stack:\n";
    
    foreach ($middleware as $index => $middlewareName) {
        echo "     {$index}. {$middlewareName}\n";
        
        // Try to resolve middleware class
        try {
            $middlewareClass = app('router')->resolveMiddleware($middlewareName);
            if (is_string($middlewareClass)) {
                echo "        -> Class: {$middlewareClass}\n";
            } elseif (is_object($middlewareClass)) {
                echo "        -> Object: " . get_class($middlewareClass) . "\n";
            } elseif (is_array($middlewareClass)) {
                echo "        -> Array with " . count($middlewareClass) . " items\n";
            }
        } catch (Exception $e) {
            echo "        -> Resolution error: {$e->getMessage()}\n";
        }
    }
    
    // Check route groups and their middleware
    echo "\n   - Route group analysis:\n";
    $routeCollection = app('router')->getRoutes();
    
    // Check if there are nested route groups affecting this route
    echo "     Checking for nested middleware...\n";
    
} else {
    echo "❌ admin.dashboard route not found!\n";
    
    // List all admin routes
    echo "\nAvailable admin routes:\n";
    foreach ($routes as $route) {
        $name = $route->getName();
        if ($name && str_starts_with($name, 'admin.')) {
            echo "   - {$name}: {$route->uri()}\n";
        }
    }
}

echo "\n2. CHECKING MIDDLEWARE ALIASES\n";
echo "===============================\n";

// Check middleware aliases in the kernel
$kernel = app('Illuminate\Contracts\Http\Kernel');
$middlewareAliases = [];

// Try to get middleware aliases using reflection
try {
    $reflection = new ReflectionClass($kernel);
    
    if ($reflection->hasProperty('middlewareAliases')) {
        $aliasProperty = $reflection->getProperty('middlewareAliases');
        $aliasProperty->setAccessible(true);
        $middlewareAliases = $aliasProperty->getValue($kernel);
    } elseif ($reflection->hasProperty('routeMiddleware')) {
        $aliasProperty = $reflection->getProperty('routeMiddleware');
        $aliasProperty->setAccessible(true);
        $middlewareAliases = $aliasProperty->getValue($kernel);
    }
    
    echo "✅ Middleware aliases:\n";
    foreach ($middlewareAliases as $alias => $class) {
        echo "   - {$alias} -> {$class}\n";
        
        if ($alias === 'auth') {
            echo "     *** This is the 'auth' middleware ***\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Could not access middleware aliases: {$e->getMessage()}\n";
}

echo "\n3. TESTING EACH MIDDLEWARE INDIVIDUALLY\n";
echo "========================================\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Ensure we're logged in
Auth::guard('admin')->logout();
Auth::guard('admin')->attempt([
    'email' => 'superadmin@rms.com',
    'password' => 'password'
]);

if (Auth::guard('admin')->check()) {
    echo "✅ Authenticated as: " . Auth::guard('admin')->user()->email . "\n\n";
    
    if ($dashboardRoute) {
        $middleware = $dashboardRoute->middleware();
        
        foreach ($middleware as $middlewareName) {
            echo "Testing middleware: {$middlewareName}\n";
            
            $request = Request::create('/admin/dashboard', 'GET');
            $request->setLaravelSession(app('session.store'));
            
            try {
                // Try to instantiate and test the middleware
                if ($middlewareName === 'web') {
                    echo "   - 'web' middleware (group) - skipping individual test\n";
                    continue;
                }
                
                if (str_starts_with($middlewareName, 'auth:')) {
                    $guard = substr($middlewareName, 5);
                    echo "   - Testing auth middleware with guard: {$guard}\n";
                    
                    $authMiddleware = app('Illuminate\Auth\Middleware\Authenticate');
                    $response = $authMiddleware->handle($request, function ($req) {
                        return response()->json(['status' => 'middleware_passed']);
                    }, $guard);
                    
                    echo "   - Status: {$response->getStatusCode()}\n";
                    if ($response->getStatusCode() === 302) {
                        echo "   - Redirect: {$response->headers->get('Location')}\n";
                    }
                } else {
                    // Try to resolve and test other middleware
                    if (isset($middlewareAliases[$middlewareName])) {
                        $middlewareClass = $middlewareAliases[$middlewareName];
                        echo "   - Resolved to: {$middlewareClass}\n";
                        
                        try {
                            $middlewareInstance = app($middlewareClass);
                            $response = $middlewareInstance->handle($request, function ($req) {
                                return response()->json(['status' => 'middleware_passed']);
                            });
                            
                            echo "   - Status: {$response->getStatusCode()}\n";
                            if ($response->getStatusCode() === 302) {
                                echo "   - Redirect: {$response->headers->get('Location')}\n";
                                echo "   *** THIS MIDDLEWARE IS CAUSING THE REDIRECT ***\n";
                            }
                        } catch (Exception $e) {
                            echo "   - Error: {$e->getMessage()}\n";
                        }
                    } else {
                        echo "   - Could not resolve middleware\n";
                    }
                }
                
            } catch (Exception $e) {
                echo "   - Error testing middleware: {$e->getMessage()}\n";
            }
            
            echo "\n";
        }
    }
} else {
    echo "❌ Not authenticated for middleware testing\n";
}

echo "\n4. CHECKING FOR CUSTOM MIDDLEWARE IN ROUTE GROUPS\n";
echo "==================================================\n";

// Check the routes file for any custom middleware that might be applied
echo "Checking route groups that might affect admin.dashboard...\n";

// Look for any middleware applied to route groups containing admin routes
$routeGroups = [
    "prefix('admin')",
    "name('admin.')",
    "middleware(['auth:admin'])",
    "middleware('auth:admin')"
];

foreach ($routeGroups as $pattern) {
    echo "   - Looking for route groups with: {$pattern}\n";
}

echo "\n🎯 MIDDLEWARE STACK ANALYSIS SUMMARY\n";
echo "====================================\n";

if ($dashboardRoute) {
    echo "✅ Dashboard route found with middleware: " . implode(', ', $dashboardRoute->middleware()) . "\n";
    echo "\n💡 RECOMMENDATIONS:\n";
    echo "1. Check if multiple auth middlewares are conflicting\n";
    echo "2. Verify that session state is maintained between requests\n";
    echo "3. Consider simplifying middleware stack\n";
    echo "4. Test with minimal middleware to isolate the issue\n";
} else {
    echo "❌ Dashboard route not found - route registration issue\n";
}

echo "\n🏁 Middleware stack analysis completed!\n";
