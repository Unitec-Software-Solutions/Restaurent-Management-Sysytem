<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Bootstrap Laravel application
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

// Boot the application
$app->boot();

echo "=== Testing Menu Index Route Fix ===\n\n";

try {
    // Test if the route exists
    $routeCollection = app('router')->getRoutes();
    
    // List all menu routes
    echo "Available Menu Routes:\n";
    foreach ($routeCollection as $route) {
        if (str_contains($route->getName() ?? '', 'admin.menus')) {
            echo "- " . $route->getName() . " -> " . $route->uri() . "\n";
        }
    }
    echo "\n";
    
    // Test specific route that was failing
    echo "Testing admin.menus.bulk-create route:\n";
    $bulkCreateRoute = $routeCollection->getByName('admin.menus.bulk-create');
    if ($bulkCreateRoute) {
        echo "✓ Route 'admin.menus.bulk-create' exists\n";
        echo "  URI: " . $bulkCreateRoute->uri() . "\n";
        echo "  Methods: " . implode(', ', $bulkCreateRoute->methods()) . "\n";
    } else {
        echo "✗ Route 'admin.menus.bulk-create' not found\n";
    }
    
    // Test route generation
    echo "\nTesting route generation:\n";
    try {
        $url = route('admin.menus.bulk-create');
        echo "✓ Route URL generated successfully: $url\n";
    } catch (Exception $e) {
        echo "✗ Route generation failed: " . $e->getMessage() . "\n";
    }
    
    // Test other critical menu routes
    $testRoutes = [
        'admin.menus.index',
        'admin.menus.create',
        'admin.menus.list',
        'admin.menus.calendar',
        'admin.menus.bulk-store'
    ];
    
    echo "\nTesting other critical menu routes:\n";
    foreach ($testRoutes as $routeName) {
        try {
            $url = route($routeName);
            echo "✓ $routeName -> $url\n";
        } catch (Exception $e) {
            echo "✗ $routeName failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Route Fix Test Complete ===\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
