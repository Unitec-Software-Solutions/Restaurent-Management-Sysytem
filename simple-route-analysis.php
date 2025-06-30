<?php

/**
 * Simple Route Analysis Script
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;

echo "=== COMPREHENSIVE ROUTE AND CONTROLLER ANALYSIS ===\n\n";

// Get all registered routes
$routes = Route::getRoutes();
$routeCollection = $routes->getRoutes();

$issues = [];
$statistics = [
    'total_routes' => 0,
    'named_routes' => 0,
    'controller_routes' => 0,
    'admin_routes' => 0,
    'missing_auth' => 0,
    'deprecated_syntax' => 0,
    'duplicate_routes' => 0
];

$routeSignatures = [];
$controllerMethodsUsed = [];
$missingControllers = [];

echo "1. ANALYZING ROUTES...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

foreach ($routeCollection as $route) {
    $statistics['total_routes']++;
    
    $uri = $route->uri();
    $methods = implode('|', $route->methods());
    $name = $route->getName();
    $action = $route->getAction();
    $middleware = $action['middleware'] ?? [];
    
    // Count named routes
    if ($name) {
        $statistics['named_routes']++;
    }
    
    // Count admin routes
    if (strpos($uri, 'admin') !== false) {
        $statistics['admin_routes']++;
        
        // Check for missing auth middleware on admin routes
        if (!in_array('auth:admin', $middleware) && !in_array('auth', $middleware)) {
            $statistics['missing_auth']++;
            $issues[] = [
                'type' => 'MISSING_AUTH_MIDDLEWARE',
                'route' => "$methods $uri",
                'name' => $name,
                'message' => 'Admin route without auth middleware'
            ];
        }
    }
    
    // Check for controller routes
    if (isset($action['controller'])) {
        $statistics['controller_routes']++;
        $controller = $action['controller'];
        
        // Check for deprecated syntax
        if (is_string($controller) && strpos($controller, '@') !== false) {
            $statistics['deprecated_syntax']++;
            $issues[] = [
                'type' => 'DEPRECATED_SYNTAX',
                'route' => "$methods $uri",
                'name' => $name,
                'controller' => $controller,
                'message' => 'Using deprecated Controller@method syntax'
            ];
        }
        
        // Track controller methods
        if (is_array($controller) && count($controller) === 2) {
            $controllerClass = $controller[0];
            $methodName = $controller[1];
            
            if (!isset($controllerMethodsUsed[$controllerClass])) {
                $controllerMethodsUsed[$controllerClass] = [];
            }
            $controllerMethodsUsed[$controllerClass][] = $methodName;
            
            // Check if controller exists
            if (!class_exists($controllerClass)) {
                $missingControllers[] = $controllerClass;
                $issues[] = [
                    'type' => 'MISSING_CONTROLLER',
                    'route' => "$methods $uri",
                    'name' => $name,
                    'controller' => $controllerClass,
                    'message' => 'Controller class does not exist'
                ];
            } else {
                // Check if method exists
                if (!method_exists($controllerClass, $methodName)) {
                    $issues[] = [
                        'type' => 'MISSING_METHOD',
                        'route' => "$methods $uri",
                        'name' => $name,
                        'controller' => $controllerClass,
                        'method' => $methodName,
                        'message' => 'Controller method does not exist'
                    ];
                }
            }
        }
    }
    
    // Check for duplicate routes
    $signature = "$methods:$uri";
    if (isset($routeSignatures[$signature])) {
        $statistics['duplicate_routes']++;
        $issues[] = [
            'type' => 'DUPLICATE_ROUTE',
            'route' => "$methods $uri",
            'name' => $name,
            'duplicate_of' => $routeSignatures[$signature],
            'message' => 'Duplicate route definition'
        ];
    } else {
        $routeSignatures[$signature] = $name ?: $uri;
    }
    
    // Check for missing route names on important routes
    if (!$name && isset($action['controller'])) {
        $issues[] = [
            'type' => 'MISSING_ROUTE_NAME',
            'route' => "$methods $uri",
            'controller' => $action['controller'],
            'message' => 'Controller route without name'
        ];
    }
}

echo "2. STATISTICS\n";
echo "━━━━━━━━━━━━━\n\n";
foreach ($statistics as $key => $value) {
    $label = str_replace('_', ' ', strtoupper($key));
    echo sprintf("%-25s: %d\n", $label, $value);
}

echo "\n3. ISSUES FOUND (" . count($issues) . ")\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$issuesByType = [];
foreach ($issues as $issue) {
    $type = $issue['type'];
    if (!isset($issuesByType[$type])) {
        $issuesByType[$type] = [];
    }
    $issuesByType[$type][] = $issue;
}

foreach ($issuesByType as $type => $typeIssues) {
    echo "🔴 $type (" . count($typeIssues) . " issues)\n";
    echo str_repeat("-", 40) . "\n";
    
    foreach (array_slice($typeIssues, 0, 5) as $issue) { // Show first 5 of each type
        echo "   Route: {$issue['route']}\n";
        if (isset($issue['name'])) echo "   Name: {$issue['name']}\n";
        if (isset($issue['controller'])) echo "   Controller: {$issue['controller']}\n";
        if (isset($issue['method'])) echo "   Method: {$issue['method']}\n";
        echo "   Message: {$issue['message']}\n";
        echo "\n";
    }
    
    if (count($typeIssues) > 5) {
        echo "   ... and " . (count($typeIssues) - 5) . " more\n";
    }
    echo "\n";
}

echo "4. CONTROLLER ANALYSIS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Controllers with methods:\n";
foreach ($controllerMethodsUsed as $controller => $methods) {
    $methodCount = count(array_unique($methods));
    echo "  $controller ($methodCount methods)\n";
    
    // Check for resource-like controllers
    $resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
    $hasResourceMethods = array_intersect($methods, $resourceMethods);
    
    if (count($hasResourceMethods) >= 4 && count($hasResourceMethods) < 7) {
        $missing = array_diff($resourceMethods, $methods);
        echo "    💡 Looks like a resource controller, missing: " . implode(', ', $missing) . "\n";
    }
}

if (!empty($missingControllers)) {
    echo "\nMissing Controllers:\n";
    foreach (array_unique($missingControllers) as $controller) {
        echo "  ❌ $controller\n";
    }
}

echo "\n5. RECOMMENDATIONS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($statistics['deprecated_syntax'] > 0) {
    echo "🔧 Update {$statistics['deprecated_syntax']} routes using deprecated Controller@method syntax\n";
    echo "   Use [Controller::class, 'method'] instead\n\n";
}

if ($statistics['missing_auth'] > 0) {
    echo "🔒 Add auth:admin middleware to {$statistics['missing_auth']} admin routes\n\n";
}

if ($statistics['duplicate_routes'] > 0) {
    echo "🔄 Remove {$statistics['duplicate_routes']} duplicate route definitions\n\n";
}

$unnamedControllerRoutes = array_filter($issues, function($issue) {
    return $issue['type'] === 'MISSING_ROUTE_NAME';
});

if (count($unnamedControllerRoutes) > 0) {
    echo "🏷️ Add names to " . count($unnamedControllerRoutes) . " controller routes\n\n";
}

echo "✅ Analysis complete!\n";
echo "📄 Review the issues above and apply the recommended fixes.\n\n";
