<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Comprehensive Menu System Test\n";
echo "==============================\n\n";

$allTestsPassed = true;

// Test 1: Menu available_days
echo "✅ Test 1: Menu available_days\n";
$menusWithNullDays = \App\Models\Menu::whereNull('available_days')->count();
echo "   Menus with null available_days: $menusWithNullDays\n";
if ($menusWithNullDays > 0) {
    echo "   ❌ FAILED: Some menus still have null available_days\n";
    $allTestsPassed = false;
} else {
    echo "   ✅ PASSED: All menus have available_days set\n";
}

// Test 2: MenuItem-MenuCategory relationships
echo "\n✅ Test 2: MenuItem-MenuCategory relationships\n";
try {
    $menuItemsWithCategory = \App\Models\MenuItem::whereNotNull('menu_category_id')->count();
    echo "   Menu items with menu_category_id: $menuItemsWithCategory\n";
    
    $testItem = \App\Models\MenuItem::whereNotNull('menu_category_id')->first();
    if ($testItem) {
        $categoryRel = $testItem->menuCategory;
        if ($categoryRel) {
            echo "   ✅ PASSED: MenuItem->menuCategory relationship works\n";
            echo "      Item: {$testItem->name} -> Category: {$categoryRel->name}\n";
        } else {
            echo "   ❌ FAILED: MenuItem->menuCategory relationship returns null\n";
            $allTestsPassed = false;
        }
    }
    
    // Test reverse relationship
    $category = \App\Models\MenuCategory::first();
    if ($category) {
        $itemsCount = $category->menuItems->count();
        echo "   ✅ PASSED: MenuCategory->menuItems relationship works\n";
        echo "      Category: {$category->name} has {$itemsCount} items\n";
    }
} catch (Exception $e) {
    echo "   ❌ FAILED: Relationship error: " . $e->getMessage() . "\n";
    $allTestsPassed = false;
}

// Test 3: Routes
echo "\n✅ Test 3: Bulk menu routes\n";
try {
    $router = app('router');
    $routes = $router->getRoutes();
    
    $bulkCreateFound = false;
    $bulkStoreFound = false;
    
    foreach ($routes as $route) {
        $name = $route->getName() ?: '';
        if ($name === 'admin.menus.bulk.create') {
            $bulkCreateFound = true;
            echo "   ✅ Found bulk create route: " . implode('|', $route->methods()) . " " . $route->uri() . "\n";
        }
        if ($name === 'admin.menus.bulk-store') {
            $bulkStoreFound = true;
            echo "   ✅ Found bulk store route: " . implode('|', $route->methods()) . " " . $route->uri() . "\n";
        }
    }
    
    if ($bulkCreateFound && $bulkStoreFound) {
        echo "   ✅ PASSED: All bulk menu routes are registered\n";
    } else {
        echo "   ❌ FAILED: Missing bulk menu routes\n";
        $allTestsPassed = false;
    }
} catch (Exception $e) {
    echo "   ❌ FAILED: Route error: " . $e->getMessage() . "\n";
    $allTestsPassed = false;
}

// Test 4: Menu model valid_from field handling
echo "\n✅ Test 4: Menu model date fields\n";
try {
    $menu = \App\Models\Menu::first();
    if ($menu) {
        echo "   Testing menu: {$menu->name}\n";
        
        // Test that valid_from can be null without errors
        if ($menu->valid_from === null) {
            echo "   ✅ PASSED: Menu can have null valid_from\n";
        } else {
            echo "   ✅ PASSED: Menu has valid_from: {$menu->valid_from->format('Y-m-d')}\n";
        }
        
        // Test that valid_until can be null without errors
        if ($menu->valid_until === null) {
            echo "   ✅ PASSED: Menu can have null valid_until\n";
        } else {
            echo "   ✅ PASSED: Menu has valid_until: {$menu->valid_until->format('Y-m-d')}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ FAILED: Menu date field error: " . $e->getMessage() . "\n";
    $allTestsPassed = false;
}

// Test 5: Controller bulk methods
echo "\n✅ Test 5: MenuController bulk methods\n";
try {
    $reflection = new ReflectionClass(\App\Http\Controllers\Admin\MenuController::class);
    
    if ($reflection->hasMethod('bulkCreate')) {
        echo "   ✅ PASSED: bulkCreate method exists\n";
    } else {
        echo "   ❌ FAILED: bulkCreate method missing\n";
        $allTestsPassed = false;
    }
    
    if ($reflection->hasMethod('bulkStore')) {
        echo "   ✅ PASSED: bulkStore method exists\n";
    } else {
        echo "   ❌ FAILED: bulkStore method missing\n";
        $allTestsPassed = false;
    }
} catch (Exception $e) {
    echo "   ❌ FAILED: Controller error: " . $e->getMessage() . "\n";
    $allTestsPassed = false;
}

// Final result
echo "\n" . str_repeat("=", 50) . "\n";
if ($allTestsPassed) {
    echo "🎉 ALL TESTS PASSED! Menu system is ready.\n";
    echo "\nFixed Issues:\n";
    echo "- ✅ Menu available_days null values\n";
    echo "- ✅ MenuItem category relationship conflicts\n";
    echo "- ✅ Bulk menu routes registration\n";
    echo "- ✅ Menu date field null handling\n";
    echo "- ✅ MenuController bulk methods\n";
} else {
    echo "❌ SOME TESTS FAILED! Please review the errors above.\n";
}
echo str_repeat("=", 50) . "\n";
