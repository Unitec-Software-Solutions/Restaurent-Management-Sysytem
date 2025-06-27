<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Request::capture();
$response = $kernel->handle($request);

echo "=== MENU POST FUNCTIONS TEST ===\n\n";

// Test 1: Check if routes are properly registered
echo "1. Testing Route Registration:\n";
$routes = app('router')->getRoutes();
$menuRoutes = [];

foreach ($routes as $route) {
    $routeName = $route->getName();
    if ($routeName && str_contains($routeName, 'admin.menus')) {
        $menuRoutes[$routeName] = [
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'action' => $route->getActionName()
        ];
    }
}

foreach ($menuRoutes as $name => $details) {
    echo "  - {$name}: {$details['uri']} [" . implode(', ', $details['methods']) . "]\n";
}

echo "\n2. Testing Menu Model Methods:\n";
try {
    $menu = new App\Models\Menu();
    
    // Check if activation methods exist
    if (method_exists($menu, 'activate')) {
        echo "  ✓ activate() method exists\n";
    } else {
        echo "  ✗ activate() method missing\n";
    }
    
    if (method_exists($menu, 'deactivate')) {
        echo "  ✓ deactivate() method exists\n";
    } else {
        echo "  ✗ deactivate() method missing\n";
    }
    
    if (method_exists($menu, 'shouldBeActiveNow')) {
        echo "  ✓ shouldBeActiveNow() method exists\n";
    } else {
        echo "  ✗ shouldBeActiveNow() method missing\n";
    }
    
} catch (Exception $e) {
    echo "  ✗ Error testing Menu model: " . $e->getMessage() . "\n";
}

echo "\n3. Testing Controller Methods:\n";
try {
    // Skip controller instantiation and just check method existence with reflection
    $controllerClass = 'App\Http\Controllers\Admin\MenuController';
    
    $methods = ['store', 'update', 'activate', 'deactivate', 'bulkStore', 'bulkCreate'];
    foreach ($methods as $method) {
        if (method_exists($controllerClass, $method)) {
            echo "  ✓ {$method}() method exists\n";
        } else {
            echo "  ✗ {$method}() method missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "  ✗ Error testing MenuController: " . $e->getMessage() . "\n";
}

echo "\n4. Testing Database Tables:\n";
try {
    $menuExists = Illuminate\Support\Facades\Schema::hasTable('menus');
    $menuItemsExists = Illuminate\Support\Facades\Schema::hasTable('menu_items');
    $menuCategoriesExists = Illuminate\Support\Facades\Schema::hasTable('menu_categories');
    $menuMenuItemExists = Illuminate\Support\Facades\Schema::hasTable('menu_menu_item');
    
    echo "  " . ($menuExists ? "✓" : "✗") . " menus table exists\n";
    echo "  " . ($menuItemsExists ? "✓" : "✗") . " menu_items table exists\n";
    echo "  " . ($menuCategoriesExists ? "✓" : "✗") . " menu_categories table exists\n";
    echo "  " . ($menuMenuItemExists ? "✓" : "✗") . " menu_menu_item pivot table exists\n";
    
    if ($menuExists) {
        $menuColumns = Illuminate\Support\Facades\Schema::getColumnListing('menus');
        $requiredColumns = ['id', 'name', 'type', 'branch_id', 'valid_from', 'valid_until', 'available_days', 'is_active', 'created_by'];
        
        echo "  Menu table columns check:\n";
        foreach ($requiredColumns as $column) {
            echo "    " . (in_array($column, $menuColumns) ? "✓" : "✗") . " {$column}\n";
        }
    }
    
} catch (Exception $e) {
    echo "  ✗ Error checking database: " . $e->getMessage() . "\n";
}

echo "\n5. Testing Route Parameter Issues:\n";
$problematicRoutes = [
    'admin.menus.show',
    'admin.menus.edit', 
    'admin.menus.update',
    'admin.menus.activate',
    'admin.menus.deactivate'
];

foreach ($problematicRoutes as $routeName) {
    if (isset($menuRoutes[$routeName])) {
        $route = $menuRoutes[$routeName];
        $hasParameter = str_contains($route['uri'], '{menu}');
        echo "  " . ($hasParameter ? "✓" : "✗") . " {$routeName}: " . ($hasParameter ? "has parameter" : "missing {menu} parameter") . "\n";
    } else {
        echo "  ✗ {$routeName}: route not found\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
