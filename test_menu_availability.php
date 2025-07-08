<?php

echo "Testing Menu Item Availability Detection\n";
echo "=====================================\n\n";

// Get all menu items
$menuItems = \App\Models\MenuItem::all();

echo "Found " . $menuItems->count() . " menu items:\n\n";

foreach ($menuItems as $item) {
    echo "ID: {$item->id}\n";
    echo "Name: {$item->name}\n";
    echo "Type: {$item->type} (" . ($item->type === 3 ? 'KOT' : 'Buy & Sell') . ")\n";
    echo "Is Available: " . ($item->is_available ? 'Yes' : 'No') . "\n";
    echo "Is Active: " . ($item->is_active ? 'Yes' : 'No') . "\n";
    echo "Item Master ID: " . ($item->item_master_id ?? 'NULL') . "\n";
    echo "---\n";
}

// Test API endpoint simulation
echo "\nTesting API Data Structure:\n";
echo "==========================\n";

$testItem = $menuItems->first();
if ($testItem) {
    $itemType = $testItem->type ?? 3;
    $apiData = [
        'id' => $testItem->id,
        'name' => $testItem->name,
        'type' => $itemType,
        'type_name' => $itemType === 3 ? 'KOT' : 'Buy & Sell',
        'item_type' => $itemType === 3 ? 'KOT' : 'Buy & Sell',
        'current_stock' => 0,
        'can_order' => true,
        'stock_status' => 'available'
    ];
    
    echo "Sample API Data:\n";
    print_r($apiData);
    
    echo "\nJavaScript Detection Test:\n";
    echo "item_type: " . $apiData['item_type'] . "\n";
    echo "type: " . $apiData['type'] . "\n";
    echo "Should be detected as KOT: " . ($apiData['item_type'] === 'KOT' ? 'YES' : 'NO') . "\n";
}
