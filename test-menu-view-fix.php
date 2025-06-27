<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Menu System Fixes\n";
echo "==========================\n\n";

// Test 1: Check menu available_days fix
echo "1. Testing Menu available_days...\n";
$menusWithNullDays = \App\Models\Menu::whereNull('available_days')->count();
echo "   Menus with null available_days: $menusWithNullDays\n";

// Test 2: Check MenuItem category relationship
echo "\n2. Testing MenuItem category relationship...\n";
try {
    $menuItem = \App\Models\MenuItem::first();
    if ($menuItem) {
        echo "   Found menu item: {$menuItem->name}\n";
        echo "   Category (string): " . ($menuItem->category ?: 'null') . "\n";
        echo "   Menu Category ID: " . ($menuItem->menu_category_id ?: 'null') . "\n";
        
        
        try {
            $categoryRel = $menuItem->menuCategory; 
            echo "   MenuCategory relationship: " . ($categoryRel ? $categoryRel->name : 'null') . "\n";
        } catch (Exception $e) {
            echo "   MenuCategory relationship error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   No menu items found\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Test 3: Routes availability
echo "\n3. Testing route registration...\n";
try {
    $router = app('router');
    $routes = $router->getRoutes();
    
    $bulkRoutes = [];
    foreach ($routes as $route) {
        if (str_contains($route->getName() ?: '', 'admin.menus.bulk')) {
            $bulkRoutes[] = [
                'name' => $route->getName(),
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri()
            ];
        }
    }
    
    echo "   Bulk menu routes found: " . count($bulkRoutes) . "\n";
    foreach ($bulkRoutes as $route) {
        echo "   - {$route['method']} {$route['uri']} [{$route['name']}]\n";
    }
} catch (Exception $e) {
    echo "   Routes error: " . $e->getMessage() . "\n";
}

echo "\n✅ Test completed!\n";
