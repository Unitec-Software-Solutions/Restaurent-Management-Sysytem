<?php

// Test the Menu model relationships work properly after the fix
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

try {
    echo "=== LARAVEL MODEL RELATIONSHIP TEST ===\n";
    echo "Testing Menu->menuItems relationship after column fix\n\n";

    // Test 1: Basic Menu query
    echo "1. Testing basic Menu query:\n";
    $menuCount = Menu::count();
    echo "   Found {$menuCount} menus in the database.\n\n";

    // Test 2: Menu with menuItems relationship (this was failing before)
    echo "2. Testing Menu::with('menuItems') query:\n";
    try {
        $menusWithItems = Menu::with('menuItems')->get();
        echo "   ✓ Menu::with('menuItems') query successful!\n";
        echo "   Loaded " . $menusWithItems->count() . " menus with their menu items.\n";
        
        // Check if any menu has items
        $hasItems = false;
        foreach ($menusWithItems as $menu) {
            if ($menu->menuItems && $menu->menuItems->count() > 0) {
                echo "   Menu '{$menu->name}' has " . $menu->menuItems->count() . " items.\n";
                $hasItems = true;
                
                // Test accessing pivot attributes
                $firstItem = $menu->menuItems->first();
                if ($firstItem) {
                    echo "   First item pivot data: override_price=" . 
                         ($firstItem->pivot->override_price ?? 'NULL') . 
                         ", sort_order=" . ($firstItem->pivot->sort_order ?? 'NULL') . "\n";
                }
            }
        }
        
        if (!$hasItems) {
            echo "   (No menu items found, but relationship query worked without SQL errors)\n";
        }
        echo "\n";
        
    } catch (Exception $e) {
        echo "   ✗ ERROR: " . $e->getMessage() . "\n\n";
        throw $e;
    }

    // Test 3: Direct pivot table access
    echo "3. Testing direct pivot table access:\n";
    $pivotCount = DB::table('menu_menu_items')->count();
    echo "   Found {$pivotCount} records in menu_menu_items pivot table.\n";
    
    if ($pivotCount > 0) {
        $samplePivot = DB::table('menu_menu_items')
            ->select('menu_id', 'menu_item_id', 'override_price', 'sort_order')
            ->first();
        echo "   Sample pivot record: menu_id={$samplePivot->menu_id}, menu_item_id={$samplePivot->menu_item_id}\n";
        echo "   Pivot attributes: override_price=" . ($samplePivot->override_price ?? 'NULL') . 
             ", sort_order=" . ($samplePivot->sort_order ?? 'NULL') . "\n";
    }
    echo "\n";

    // Test 4: Test the exact controller query pattern
    echo "4. Testing controller-style query (MenuController@index pattern):\n";
    try {
        $controllerQuery = Menu::with(['menuItems' => function($query) {
            $query->orderBy('sort_order', 'asc');
        }])->where('is_active', true)->get();
        
        echo "   ✓ Controller-style query successful!\n";
        echo "   Found " . $controllerQuery->count() . " active menus.\n\n";
        
    } catch (Exception $e) {
        echo "   ✗ Controller query ERROR: " . $e->getMessage() . "\n\n";
        throw $e;
    }

    echo "=== ALL TESTS PASSED ===\n";
    echo "✓ The SQLSTATE[42703] error has been successfully resolved!\n";
    echo "✓ Menu model relationships work correctly with new column names.\n";
    echo "✓ Pivot table queries execute without errors.\n";
    echo "✓ Controller-style queries work properly.\n";
    echo "\nThe menus page should now load without SQL errors.\n";

} catch (Exception $e) {
    echo "\n=== TEST FAILED ===\n";
    echo "✗ Error encountered: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "\nThe issue may not be fully resolved.\n";
    exit(1);
}
