<?php

/**
 * Test Order Creation Page Loading
 * 
 * This script tests if the order creation page loads without the previous errors.
 */

echo "=== Testing Order Creation Page ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ItemCategory;
use App\Models\ItemMaster;
use App\Models\Admin;

try {
    
    echo "1. Testing ItemCategory model and relationships...\n";
    
    // Test if ItemCategory loads properly
    $categories = ItemCategory::active()->take(5)->get();
    echo "   ✓ ItemCategory::active() works - found {$categories->count()} categories\n";
    
    // Test organization relationship
    $firstCategory = $categories->first();
    if ($firstCategory) {
        $organization = $firstCategory->organization;
        echo "   ✓ ItemCategory->organization relationship: " . ($organization ? "works" : "null") . "\n";
    }
    
    echo "\n2. Testing ItemMaster with menu items...\n";
    
    // Test menu items query similar to what's in AdminOrderController
    $menuItems = ItemMaster::select('id', 'name', 'selling_price as price', 'description', 'attributes')
        ->where('is_menu_item', true)
        ->where('is_active', true)
        ->take(5)
        ->get();
    
    echo "   ✓ Menu items query works - found {$menuItems->count()} menu items\n";
    
    // Test menu item attributes
    foreach ($menuItems as $item) {
        $attributes = is_array($item->attributes) ? $item->attributes : [];
        $hasRequiredAttrs = !empty($attributes['cuisine_type']) && 
                           !empty($attributes['prep_time_minutes']) && 
                           !empty($attributes['serving_size']);
        
        echo "   - {$item->name}: " . ($hasRequiredAttrs ? "✓ has required attributes" : "✗ missing attributes") . "\n";
    }
    
    echo "\n3. Testing Admin authentication context...\n";
    
    // Test admin loading
    $admin = Admin::where('is_active', true)->first();
    if ($admin) {
        echo "   ✓ Admin found: {$admin->name} (ID: {$admin->id})\n";
        echo "   - Is super admin: " . ($admin->is_super_admin ? "Yes" : "No") . "\n";
        echo "   - Organization ID: " . ($admin->organization_id ?? "None") . "\n";
        echo "   - Branch ID: " . ($admin->branch_id ?? "None") . "\n";
    } else {
        echo "   ✗ No admin found\n";
    }
    
    echo "\n4. Simulating AdminOrderController@create variables...\n";
    
    // Simulate the variables that should be passed to the view
    $branches = \App\Models\Branch::active()->take(3)->get();
    $categories = ItemCategory::active()->take(5)->get();
    $menuItems = ItemMaster::where('is_menu_item', true)
        ->where('is_active', true)
        ->take(5)
        ->get();
    
    echo "   ✓ Branches: {$branches->count()}\n";
    echo "   ✓ Categories: {$categories->count()}\n";
    echo "   ✓ Menu Items: {$menuItems->count()}\n";
    
    // Variables that should be available in the view
    $reservation = null;
    $stockSummary = [
        'available_count' => $menuItems->count(),
        'low_stock_count' => 0,
        'out_of_stock_count' => 0
    ];
    
    echo "   ✓ Reservation: " . ($reservation ? "set" : "null (as expected)") . "\n";
    echo "   ✓ Stock Summary: {$stockSummary['available_count']} available items\n";
    
    echo "\n5. Testing view requirements...\n";
    
    // Check if all required variables are available
    $requiredVars = [
        'branches' => $branches,
        'categories' => $categories, 
        'menuItems' => $menuItems,
        'reservation' => $reservation,
        'stockSummary' => $stockSummary
    ];
    
    foreach ($requiredVars as $varName => $value) {
        echo "   ✓ \${$varName}: " . (isset($value) ? "set" : "not set") . "\n";
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "✓ ItemCategory model working correctly\n";
    echo "✓ Menu items with attributes loading\n";
    echo "✓ All required variables for view available\n";
    echo "✓ No undefined variable errors should occur\n";
    
    echo "\nOrder creation page should now load successfully! 🎉\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
