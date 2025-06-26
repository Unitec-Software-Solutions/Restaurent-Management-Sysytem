<?php

echo "=== Menu Relationship and Activation Diagnostic ===\n\n";

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$app->boot();

echo "1. Testing MenuItem-MenuCategory Relationship:\n";
try {
    // Find a menu item
    $menuItem = \App\Models\MenuItem::first();
    if ($menuItem) {
        echo "✓ Found menu item: {$menuItem->name}\n";
        
        // Test relationship access
        $category = $menuItem->menuCategory;
        if ($category) {
            echo "✓ MenuCategory relationship works: {$category->name}\n";
        } else {
            echo "⚠ No category associated with menu item\n";
        }
        
        // Test old 'category' attribute access
        try {
            $oldCategory = $menuItem->category;
            echo "⚠ Old 'category' attribute still accessible\n";
        } catch (Exception $e) {
            echo "✓ Old 'category' attribute properly removed\n";
        }
        
    } else {
        echo "✗ No menu items found in database\n";
    }
} catch (Exception $e) {
    echo "✗ Error testing relationships: " . $e->getMessage() . "\n";
}

echo "\n2. Testing Menu with MenuItems and Categories:\n";
try {
    $menu = \App\Models\Menu::with(['menuItems.menuCategory'])->first();
    if ($menu) {
        echo "✓ Found menu: {$menu->name}\n";
        echo "  - Menu items count: " . $menu->menuItems->count() . "\n";
        
        foreach ($menu->menuItems as $item) {
            $categoryName = $item->menuCategory ? $item->menuCategory->name : 'No Category';
            echo "  - Item: {$item->name} (Category: {$categoryName})\n";
        }
        
        // Test groupBy functionality
        $grouped = $menu->menuItems->groupBy('menuCategory.name');
        echo "  - Categories found: " . $grouped->keys()->count() . "\n";
        foreach ($grouped->keys() as $categoryName) {
            echo "    * {$categoryName}\n";
        }
        
    } else {
        echo "✗ No menus found in database\n";
    }
} catch (Exception $e) {
    echo "✗ Error testing menu relationships: " . $e->getMessage() . "\n";
}

echo "\n3. Testing Menu Activation Logic:\n";
try {
    $menu = \App\Models\Menu::first();
    if ($menu) {
        echo "✓ Found menu: {$menu->name}\n";
        echo "  - Current status: " . ($menu->is_active ? 'Active' : 'Inactive') . "\n";
        echo "  - Date from: " . ($menu->date_from ? $menu->date_from->format('Y-m-d') : 'Not set') . "\n";
        echo "  - Date to: " . ($menu->date_to ? $menu->date_to->format('Y-m-d') : 'Not set') . "\n";
        echo "  - Available days: " . (is_array($menu->available_days) ? implode(', ', $menu->available_days) : 'Not set') . "\n";
        
        // Test if should be active now
        $shouldBeActive = $menu->shouldBeActiveNow();
        echo "  - Should be active now: " . ($shouldBeActive ? 'Yes' : 'No') . "\n";
        
        if (!$shouldBeActive) {
            echo "  - Activation blocked because:\n";
            
            $now = \Carbon\Carbon::now();
            
            // Check date validity
            if (!$menu->isValidForDate($now)) {
                echo "    * Menu is not valid for current date\n";
                
                if ($menu->date_from && $now->lt($menu->date_from)) {
                    echo "      - Current date is before start date\n";
                }
                
                if ($menu->date_to && $now->gt($menu->date_to)) {
                    echo "      - Current date is after end date\n";
                }
                
                if (!empty($menu->available_days) && is_array($menu->available_days)) {
                    $dayName = strtolower($now->format('l'));
                    if (!in_array($dayName, $menu->available_days)) {
                        echo "      - Current day ({$dayName}) not in available days\n";
                    }
                }
            }
            
            // Check time windows
            if ($menu->activation_time || $menu->deactivation_time) {
                $currentTime = $now->format('H:i');
                
                if ($menu->activation_time && $currentTime < $menu->activation_time->format('H:i')) {
                    echo "    * Current time is before activation time\n";
                }
                
                if ($menu->deactivation_time && $currentTime > $menu->deactivation_time->format('H:i')) {
                    echo "    * Current time is after deactivation time\n";
                }
            }
        }
        
        // Test activation
        if ($shouldBeActive) {
            echo "\n  - Testing activation...\n";
            $result = $menu->activate();
            echo "  - Activation result: " . ($result ? 'Success' : 'Failed') . "\n";
        }
        
    } else {
        echo "✗ No menus found in database\n";
    }
} catch (Exception $e) {
    echo "✗ Error testing menu activation: " . $e->getMessage() . "\n";
}

echo "\n4. Testing Menu Controller Preview Method:\n";
try {
    $menu = \App\Models\Menu::first();
    if ($menu) {
        echo "✓ Testing preview method load...\n";
        
        // Simulate the controller's load call
        $menu->load(['menuItems.menuCategory']);
        echo "✓ Successfully loaded menuItems.menuCategory\n";
        
        // Test access to loaded relationships
        foreach ($menu->menuItems as $item) {
            $categoryName = $item->menuCategory ? $item->menuCategory->name : 'No Category';
            echo "  - {$item->name} -> {$categoryName}\n";
        }
        
    } else {
        echo "✗ No menus found for preview test\n";
    }
} catch (Exception $e) {
    echo "✗ Error testing preview method: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
