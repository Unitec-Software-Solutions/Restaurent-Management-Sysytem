<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Foundation\Bootstrap\RegisterFacades;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\MenuCategory;


// Bootstrap Laravel
$app = new Application(realpath(__DIR__));
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Illuminate\Foundation\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
);

echo "Fixing Menu Item Categories\n";
echo "===========================\n\n";

try {
    // Step 1: Get all menu items with string categories but no menu_category_id
    $menuItems = MenuItem::whereNull('menu_category_id')
        ->whereNotNull('category')
        ->get();
    
    echo "Found " . $menuItems->count() . " menu items with string categories but no menu_category_id\n\n";
    
    if ($menuItems->count() === 0) {
        echo "✅ No menu items need category fixing!\n";
        exit(0);
    }
    
    // Step 2: Get or create menu categories for each unique string category
    $categoryMap = [];
    $uniqueCategories = $menuItems->pluck('category')->unique();
    
    echo "Processing unique categories: " . $uniqueCategories->join(', ') . "\n\n";
    
    foreach ($uniqueCategories as $categoryName) {
        if (empty($categoryName)) continue;
        
        // Try to find existing category
        $category = MenuCategory::where('name', $categoryName)->first();
        
        if (!$category) {
            // Create new category
            $category = MenuCategory::create([
                'name' => $categoryName,
                'description' => 'Auto-created category for: ' . $categoryName,
                'is_active' => true,
            ]);
            echo "✅ Created new category: {$categoryName} (ID: {$category->id})\n";
        } else {
            echo "✅ Found existing category: {$categoryName} (ID: {$category->id})\n";
        }
        
        $categoryMap[$categoryName] = $category->id;
    }
    
    echo "\n";
    
    // Step 3: Update menu items with proper menu_category_id
    $updatedCount = 0;
    foreach ($menuItems as $menuItem) {
        if (isset($categoryMap[$menuItem->category])) {
            $menuItem->menu_category_id = $categoryMap[$menuItem->category];
            $menuItem->save();
            $updatedCount++;
            echo "✅ Updated '{$menuItem->name}' - category: {$menuItem->category} -> ID: {$menuItem->menu_category_id}\n";
        }
    }
    
    echo "\n";
    echo "📊 Summary:\n";
    echo "- Categories processed: " . count($categoryMap) . "\n";
    echo "- Menu items updated: {$updatedCount}\n";
    
    // Step 4: Verify the fix
    echo "\n🔍 Verification:\n";
    $testItem = MenuItem::whereNotNull('menu_category_id')->first();
    if ($testItem) {
        echo "Testing menu item: '{$testItem->name}'\n";
        try {
            $categoryRelation = $testItem->menuCategory;
            if ($categoryRelation) {
                echo "✅ Category relationship works: {$categoryRelation->name}\n";
            } else {
                echo "❌ Category relationship returned null\n";
            }
        } catch (Exception $e) {
            echo "❌ Category relationship error: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Category fix process completed!\n";
