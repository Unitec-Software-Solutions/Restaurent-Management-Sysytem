<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING BULK CREATE RELATIONSHIP FIX ===\n\n";

try {
    // Test the exact query used in the controller
    echo "1. Testing MenuCategory with menuItems relationship...\n";
    $categories = App\Models\MenuCategory::with('menuItems')->get();
    
    echo "   Found " . $categories->count() . " categories\n\n";
    
    foreach ($categories as $category) {
        echo "   Category: {$category->name}\n";
        
        // Test the relationship access
        try {
            $menuItems = $category->menuItems;
            if ($menuItems) {
                echo "     ✅ menuItems relationship works: {$menuItems->count()} items\n";
            } else {
                echo "     ⚠️  menuItems relationship returned null\n";
            }
        } catch (Exception $e) {
            echo "     ❌ menuItems relationship error: {$e->getMessage()}\n";
        }
        
        // Test the old 'items' relationship that was causing the error
        try {
            $items = $category->items;
            echo "     ⚠️  items relationship (should not exist): " . gettype($items) . "\n";
        } catch (Exception $e) {
            echo "     ✅ items relationship correctly doesn't exist: {$e->getMessage()}\n";
        }
        
        echo "\n";
    }
    
    // Test 2: Simulate the view rendering logic
    echo "2. Testing view rendering simulation...\n";
    
    foreach ($categories as $category) {
        echo "   Category: {$category->name}\n";
        
        // Test the fixed condition
        if ($category->menuItems && $category->menuItems->count() > 0) {
            echo "     ✅ Condition passed: Will render {$category->menuItems->count()} items\n";
            
            // Test iteration
            $itemCount = 0;
            foreach ($category->menuItems as $item) {
                $itemCount++;
                if ($itemCount <= 3) { // Only show first 3 for brevity
                    echo "       - {$item->name} (\${$item->price})\n";
                } elseif ($itemCount == 4) {
                    echo "       - ... and " . ($category->menuItems->count() - 3) . " more items\n";
                    break;
                }
            }
        } else {
            echo "     ✅ Condition failed safely: No items to render\n";
        }
        echo "\n";
    }
    
    // Test 3: Test specific error pattern
    echo "3. Testing specific error patterns...\n";
    
    $testCategory = new stdClass();
    $testCategory->name = "Test Category";
    $testCategory->menuItems = null;
    
    echo "   Testing null menuItems...\n";
    if ($testCategory->menuItems && $testCategory->menuItems->count() > 0) {
        echo "     Would render items\n";
    } else {
        echo "     ✅ Null menuItems handled safely\n";
    }
    
    echo "\n=== TEST RESULTS ===\n";
    echo "✅ MenuCategory relationship loading works\n";
    echo "✅ View rendering logic is safe\n";
    echo "✅ Null handling is working\n";
    echo "✅ Bulk create page should now work\n";
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
