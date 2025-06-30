<?php
/**
 * Create MenuItem records from ItemMaster to fix foreign key issues
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MenuItem;
use App\Models\ItemMaster;
use App\Models\MenuCategory;

echo "🔧 CREATING MENU ITEMS FROM ITEM MASTER\n";
echo "=======================================\n\n";

// First, let's create a default menu category if none exists
$defaultCategory = MenuCategory::firstOrCreate([
    'name' => 'General Items'
], [
    'description' => 'General menu items',
    'is_active' => true,
    'sort_order' => 1
]);

echo "✅ Default category: {$defaultCategory->name} (ID: {$defaultCategory->id})\n\n";

$itemMasters = ItemMaster::where('is_menu_item', true)->where('is_active', true)->get();

echo "Converting {$itemMasters->count()} ItemMaster records to MenuItems...\n\n";

foreach ($itemMasters as $itemMaster) {
    // Check if MenuItem already exists for this ItemMaster
    $existingMenuItem = MenuItem::where('item_master_id', $itemMaster->id)->first();
    
    if ($existingMenuItem) {
        echo "  ⚠️  MenuItem already exists for {$itemMaster->name}\n";
        continue;
    }
    
    // Create MenuItem from ItemMaster
    $menuItem = MenuItem::create([
        'name' => $itemMaster->name,
        'description' => $itemMaster->description ?? '',
        'price' => $itemMaster->selling_price,
        'menu_category_id' => $defaultCategory->id,
        'item_master_id' => $itemMaster->id,
        'is_active' => true,
        'is_available' => true,
        'organization_id' => $itemMaster->organization_id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "  ✅ Created MenuItem: {$menuItem->name} (ID: {$menuItem->id})\n";
}

echo "\n🎉 MenuItem creation completed!\n";

// Now let's test if we can create an order item
echo "\n🧪 Testing OrderItem creation with MenuItem ID...\n";

$firstMenuItem = MenuItem::first();
if ($firstMenuItem) {
    echo "Using MenuItem: {$firstMenuItem->name} (ID: {$firstMenuItem->id})\n";
} else {
    echo "❌ No MenuItems found!\n";
}
