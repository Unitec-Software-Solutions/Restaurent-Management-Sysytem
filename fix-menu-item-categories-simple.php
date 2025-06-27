<?php
// Simple script to fix menu item categories using artisan tinker approach
echo "Fixing Menu Item Categories\n";
echo "===========================\n\n";

// Create a PHP script that can be run with php artisan tinker
$tinkerScript = "
use App\Models\MenuItem;
use App\Models\MenuCategory;

// Get all menu items with string categories but no menu_category_id
\$menuItems = MenuItem::whereNull('menu_category_id')->whereNotNull('category')->get();
echo 'Found ' . \$menuItems->count() . ' menu items with string categories but no menu_category_id' . PHP_EOL;

if (\$menuItems->count() === 0) {
    echo '✅ No menu items need category fixing!' . PHP_EOL;
    exit(0);
}

// Get unique categories
\$uniqueCategories = \$menuItems->pluck('category')->unique();
echo 'Processing unique categories: ' . \$uniqueCategories->join(', ') . PHP_EOL;

// Process each category
\$categoryMap = [];
foreach (\$uniqueCategories as \$categoryName) {
    if (empty(\$categoryName)) continue;
    
    // Try to find existing category
    \$category = MenuCategory::where('name', \$categoryName)->first();
    
    if (!\$category) {
        // Create new category
        \$category = MenuCategory::create([
            'name' => \$categoryName,
            'description' => 'Auto-created category for: ' . \$categoryName,
            'is_active' => true,
        ]);
        echo '✅ Created new category: ' . \$categoryName . ' (ID: ' . \$category->id . ')' . PHP_EOL;
    } else {
        echo '✅ Found existing category: ' . \$categoryName . ' (ID: ' . \$category->id . ')' . PHP_EOL;
    }
    
    \$categoryMap[\$categoryName] = \$category->id;
}

// Update menu items with proper menu_category_id
\$updatedCount = 0;
foreach (\$menuItems as \$menuItem) {
    if (isset(\$categoryMap[\$menuItem->category])) {
        \$menuItem->menu_category_id = \$categoryMap[\$menuItem->category];
        \$menuItem->save();
        \$updatedCount++;
        echo '✅ Updated ' . \$menuItem->name . ' - category: ' . \$menuItem->category . ' -> ID: ' . \$menuItem->menu_category_id . PHP_EOL;
    }
}

echo PHP_EOL . '📊 Summary:' . PHP_EOL;
echo '- Categories processed: ' . count(\$categoryMap) . PHP_EOL;
echo '- Menu items updated: ' . \$updatedCount . PHP_EOL;

// Verify the fix
echo PHP_EOL . '🔍 Verification:' . PHP_EOL;
\$testItem = MenuItem::whereNotNull('menu_category_id')->first();
if (\$testItem) {
    echo 'Testing menu item: ' . \$testItem->name . PHP_EOL;
    try {
        \$categoryRelation = \$testItem->category;
        if (\$categoryRelation) {
            echo '✅ Category relationship works: ' . \$categoryRelation->name . PHP_EOL;
        } else {
            echo '❌ Category relationship returned null' . PHP_EOL;
        }
    } catch (Exception \$e) {
        echo '❌ Category relationship error: ' . \$e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . '✅ Category fix process completed!' . PHP_EOL;
";

// Write the script to a temporary file
file_put_contents(__DIR__ . '/temp_category_fix.php', $tinkerScript);

echo "Created temporary script: temp_category_fix.php\n";
echo "Run with: php artisan tinker < temp_category_fix.php\n";
echo "\nOr run the commands directly in artisan tinker:\n";
echo $tinkerScript;
