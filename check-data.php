<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== MENU DATA CHECK ===\n";

// Check ItemMaster data
$itemMasterTotal = App\Models\ItemMaster::count();
$itemMasterMenuItems = App\Models\ItemMaster::where('is_menu_item', true)->count();

echo "Total ItemMaster records: $itemMasterTotal\n";
echo "ItemMaster marked as menu items: $itemMasterMenuItems\n";

if ($itemMasterMenuItems > 0) {
    echo "\nSample menu items from ItemMaster:\n";
    $samples = App\Models\ItemMaster::where('is_menu_item', true)->take(5)->get(['id', 'name', 'selling_price']);
    foreach ($samples as $item) {
        echo "- ID: {$item->id}, Name: {$item->name}, Price: {$item->selling_price}\n";
    }
}

// Check MenuItem data
$menuItemTotal = App\Models\MenuItem::count();
echo "\nTotal MenuItem records: $menuItemTotal\n";

if ($menuItemTotal > 0) {
    echo "\nSample MenuItems:\n";
    $samples = App\Models\MenuItem::take(5)->get(['id', 'name', 'price']);
    foreach ($samples as $item) {
        echo "- ID: {$item->id}, Name: {$item->name}, Price: {$item->price}\n";
    }
}

// Check valid OrderType values
echo "\n=== ORDER TYPE VALUES ===\n";
$orderTypes = App\Enums\OrderType::cases();
echo "Valid OrderType values:\n";
foreach ($orderTypes as $type) {
    echo "- {$type->name}: {$type->value}\n";
}

// Check branches
echo "\n=== BRANCH DATA ===\n";
$branches = App\Models\Branch::take(3)->get(['id', 'name', 'is_active']);
foreach ($branches as $branch) {
    echo "- Branch ID: {$branch->id}, Name: {$branch->name}, Active: " . ($branch->is_active ? 'Yes' : 'No') . "\n";
}
