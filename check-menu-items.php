<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MenuItem;
use App\Models\ItemMaster;

echo "🔍 CHECKING MENU ITEMS vs ITEM MASTER\n";
echo "=====================================\n\n";

$menuItems = MenuItem::count();
$itemMasters = ItemMaster::where('is_menu_item', true)->count();

echo "MenuItem records: $menuItems\n";
echo "ItemMaster menu items: $itemMasters\n\n";

if ($menuItems > 0) {
    echo "Sample MenuItems:\n";
    MenuItem::take(5)->get()->each(function($item) {
        $name = $item->name ? $item->name : 'N/A';
        echo "  • ID: {$item->id}, Name: {$name}\n";
    });
}

echo "\nSample ItemMaster menu items:\n";
ItemMaster::where('is_menu_item', true)->take(5)->get()->each(function($item) {
    echo "  • ID: {$item->id}, Name: {$item->name}\n";
});

echo "\n💡 SOLUTION: We need to either:\n";
echo "1. Use MenuItem IDs instead of ItemMaster IDs, OR\n";
echo "2. Change the foreign key to reference item_master table, OR\n";
echo "3. Set menu_item_id to nullable and use inventory_item_id\n";
