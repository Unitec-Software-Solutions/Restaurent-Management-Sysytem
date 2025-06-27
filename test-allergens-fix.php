<?php

echo "=== Testing Preview View Allergens Fix ===\n\n";

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$app->boot();

echo "1. Testing MenuItem with different allergen formats:\n";

// Test various allergen formats
$menuItem = \App\Models\MenuItem::first();
if ($menuItem) {
    echo "✓ Found menu item: {$menuItem->name}\n";
    
    // Test current allergens field
    echo "  - Current allergens value: " . var_export($menuItem->allergens, true) . "\n";
    echo "  - Type: " . gettype($menuItem->allergens) . "\n";
    
    // Test allergen_info field
    echo "  - Current allergen_info value: " . var_export($menuItem->allergen_info, true) . "\n";
    echo "  - Type: " . gettype($menuItem->allergen_info) . "\n";
    
    // Test the fixed logic
    $allergens = null;
    if ($menuItem->allergen_info && is_array($menuItem->allergen_info)) {
        $allergens = $menuItem->allergen_info;
    } elseif ($menuItem->allergens) {
        $allergens = is_array($menuItem->allergens) ? $menuItem->allergens : explode(',', $menuItem->allergens);
    }
    
    echo "  - Processed allergens: " . var_export($allergens, true) . "\n";
    
    if ($allergens && count($allergens) > 0) {
        echo "  - Display text: " . implode(', ', array_filter($allergens)) . "\n";
    } else {
        echo "  - No allergens to display\n";
    }
} else {
    echo "✗ No menu items found\n";
}

echo "\n2. Testing count() function safety:\n";

// Test different data types with count()
$testData = [
    'array' => ['peanuts', 'dairy'],
    'string' => 'peanuts,dairy',
    'null' => null,
    'empty_array' => [],
    'empty_string' => ''
];

foreach ($testData as $type => $data) {
    echo "  - Testing {$type}: ";
    try {
        $count = is_countable($data) ? count($data) : 0;
        echo "count = {$count}\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\n3. Testing preview view logic simulation:\n";

// Simulate the view logic for different allergen formats
$testItems = [
    (object) ['allergens' => ['peanuts', 'dairy'], 'allergen_info' => null],
    (object) ['allergens' => 'peanuts,dairy', 'allergen_info' => null],
    (object) ['allergens' => null, 'allergen_info' => ['gluten', 'soy']],
    (object) ['allergens' => '', 'allergen_info' => []],
    (object) ['allergens' => null, 'allergen_info' => null]
];

foreach ($testItems as $index => $item) {
    echo "  - Test item " . ($index + 1) . ":\n";
    
    $allergens = null;
    if ($item->allergen_info && is_array($item->allergen_info)) {
        $allergens = $item->allergen_info;
    } elseif ($item->allergens) {
        $allergens = is_array($item->allergens) ? $item->allergens : explode(',', $item->allergens);
    }
    
    if ($allergens && count($allergens) > 0) {
        echo "    Display: " . implode(', ', array_filter($allergens)) . "\n";
    } else {
        echo "    No allergens\n";
    }
}

echo "\n=== Test Complete ===\n";
