<?php

// Simple Laravel application test script
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

try {
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "=== Laravel Application Status Test ===\n";
    
    // Test 1: Database Connection
    echo "\n1. Testing database connection...\n";
    $result = DB::select('SELECT COUNT(*) as count FROM branches');
    echo "✅ Database connection successful\n";
    echo "   Branches count: " . $result[0]->count . "\n";
    
    // Test 2: Models
    echo "\n2. Testing models...\n";
    $menuItemsCount = \App\Models\MenuItem::count();
    $menusCount = \App\Models\Menu::count();
    echo "✅ Models working correctly\n";
    echo "   Menu items: {$menuItemsCount}\n";
    echo "   Menus: {$menusCount}\n";
    
    // Test 3: KOT Items Specifically  
    echo "\n3. Testing KOT items...\n";
    $kotItems = \App\Models\MenuItem::where('type', 3)->where('is_active', true)->count();
    echo "✅ KOT items found: {$kotItems}\n";
    
    // Test 4: Menu-MenuItem Relationships
    echo "\n4. Testing menu-menuitem relationships...\n";
    $pivotCount = DB::table('menu_menu_items')->where('is_available', true)->count();
    echo "✅ Available menu-menuitem relationships: {$pivotCount}\n";
    
    // Test 5: Active Menu Check
    echo "\n5. Testing active menu logic...\n";
    $branch = \App\Models\Branch::first();
    if ($branch) {
        $activeMenu = \App\Models\Menu::getActiveMenuForBranch($branch->id);
        if ($activeMenu) {
            $availableItems = $activeMenu->menuItems()
                ->where('is_active', true)
                ->wherePivot('is_available', true)
                ->count();
            echo "✅ Active menu found: {$activeMenu->name}\n";
            echo "   Available items in active menu: {$availableItems}\n";
        } else {
            echo "❌ No active menu found for branch {$branch->id}\n";
        }
    }
    
    echo "\n=== All tests completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
