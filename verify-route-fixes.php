<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

echo "🔍 VERIFYING ROUTE FIXES\n";
echo "========================\n\n";

// Check if routes are properly registered
$routes = Route::getRoutes();
$adminRoutes = 0;
$guestRoutes = 0;
$publicRoutes = 0;

$routeCount = 0;
foreach ($routes as $route) {
    $routeCount++;
    $name = $route->getName();
    if (strpos($name, 'admin.') === 0) {
        $adminRoutes++;
    } elseif (strpos($name, 'guest.') === 0) {
        $guestRoutes++;
    } else {
        $publicRoutes++;
    }
}

echo "📊 ROUTE STATISTICS:\n";
echo "Admin routes: $adminRoutes\n";
echo "Guest routes: $guestRoutes\n";
echo "Public routes: $publicRoutes\n";
echo "Total routes: $routeCount\n\n";

// Check specific problematic routes that were identified
$criticalRoutes = [
    'admin.dashboard',
    'admin.inventory.index',
    'admin.suppliers.index',
    'admin.orders.index',
    'admin.grn.index',
    'guest.menu.view',
    'reservations.create',
];

echo "🎯 CHECKING CRITICAL ROUTES:\n";
foreach ($criticalRoutes as $routeName) {
    if (Route::has($routeName)) {
        $route = Route::getRoutes()->getByName($routeName);
        $action = $route->getActionName();
        
        // Check if controller exists
        if (strpos($action, '@') !== false) {
            [$controller, $method] = explode('@', $action);
            $controllerExists = class_exists($controller);
            $methodExists = $controllerExists && method_exists($controller, $method);
            
            $status = $controllerExists && $methodExists ? '✅' : '❌';
            echo "{$status} {$routeName} -> {$controller}@{$method}\n";
            
            if (!$controllerExists) {
                echo "   ⚠️  Controller missing: {$controller}\n";
            } elseif (!$methodExists) {
                echo "   ⚠️  Method missing: {$method}\n";
            }
        } else {
            echo "✅ {$routeName} -> Closure\n";
        }
    } else {
        echo "❌ Route not found: {$routeName}\n";
    }
}

echo "\n🔧 CHECKING CONTROLLER FILES:\n";

$controllerDirs = [
    'App\Http\Controllers' => app_path('Http/Controllers'),
    'App\Http\Controllers\Admin' => app_path('Http/Controllers/Admin'),
];

foreach ($controllerDirs as $namespace => $dir) {
    if (File::exists($dir)) {
        $files = File::files($dir);
        echo "📁 {$namespace}: " . count($files) . " files\n";
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $filename = $file->getFilenameWithoutExtension();
                if ($filename !== 'Controller') {
                    $fullClass = $namespace . '\\' . $filename;
                    $exists = class_exists($fullClass);
                    $status = $exists ? '✅' : '❌';
                    echo "  {$status} {$filename}\n";
                }
            }
        }
    }
}

echo "\n📝 TESTING ROUTE RESOLUTION:\n";

$testRoutes = [
    'admin.login' => [],
    'admin.dashboard' => [],
    'admin.inventory.index' => [],
    'guest.menu.view' => ['branchId' => 1],
];

foreach ($testRoutes as $routeName => $params) {
    try {
        if (Route::has($routeName)) {
            $url = route($routeName, $params);
            echo "✅ {$routeName} -> {$url}\n";
        } else {
            echo "❌ Route not found: {$routeName}\n";
        }
    } catch (Exception $e) {
        echo "❌ Error resolving {$routeName}: " . $e->getMessage() . "\n";
    }
}

echo "\n📋 CHECKING MISSING METHODS:\n";

// Check specific methods that were added
$methodChecks = [
    'App\Http\Controllers\AdminReservationController' => ['destroy'],
    'App\Http\Controllers\GrnDashboardController' => ['statistics'],
    'App\Http\Controllers\ProductionOrderController' => ['calculateIngredientsFromRecipes'],
    'App\Http\Controllers\ProductionSessionController' => ['issueIngredients', 'recordProduction'],
    'App\Http\Controllers\AdminOrderController' => ['enhancedCreate', 'enhancedStore', 'confirmOrderStock', 'cancelOrderWithStock'],
];

foreach ($methodChecks as $controller => $methods) {
    if (class_exists($controller)) {
        echo "🔍 {$controller}:\n";
        foreach ($methods as $method) {
            $exists = method_exists($controller, $method);
            $status = $exists ? '✅' : '❌';
            echo "  {$status} {$method}()\n";
        }
    } else {
        echo "❌ Controller not found: {$controller}\n";
    }
}

echo "\n🧪 MIDDLEWARE VALIDATION:\n";

$adminRouteCount = 0;
$protectedRouteCount = 0;

foreach ($routes as $route) {
    $name = $route->getName();
    if ($name && strpos($name, 'admin.') === 0 && !in_array($name, ['admin.login', 'admin.login.submit'])) {
        $adminRouteCount++;
        $middleware = $route->gatherMiddleware();
        if (in_array('auth:admin', $middleware)) {
            $protectedRouteCount++;
        } else {
            echo "⚠️  Admin route missing auth middleware: {$name}\n";
        }
    }
}

echo "Admin routes: {$adminRouteCount}\n";
echo "Protected with auth:admin: {$protectedRouteCount}\n";

$protectionRatio = $adminRouteCount > 0 ? round(($protectedRouteCount / $adminRouteCount) * 100, 1) : 0;
echo "Protection ratio: {$protectionRatio}%\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "VERIFICATION SUMMARY\n";
echo str_repeat("=", 50) . "\n";

$issues = [];

// Count issues
foreach ($routes as $route) {
    $action = $route->getActionName();
    if (strpos($action, '@') !== false) {
        [$controller, $method] = explode('@', $action);
        if (!class_exists($controller)) {
            $issues[] = "Missing controller: {$controller}";
        } elseif (!method_exists($controller, $method)) {
            $issues[] = "Missing method: {$controller}@{$method}";
        }
    }
}

echo "Total issues remaining: " . count($issues) . "\n";

if (count($issues) === 0) {
    echo "🎉 All route-controller mappings are valid!\n";
} else {
    echo "⚠️  Issues still need attention:\n";
    foreach (array_slice($issues, 0, 10) as $issue) {
        echo "  - {$issue}\n";
    }
    if (count($issues) > 10) {
        echo "  ... and " . (count($issues) - 10) . " more\n";
    }
}

echo "\n✅ Route verification completed!\n";
