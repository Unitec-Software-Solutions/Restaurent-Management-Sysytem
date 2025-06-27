<?php

/**
 * Menu Item Attributes Data Migration
 * 
 * This script migrates existing menu item attributes to the new required format
 * - Maps prep_time to prep_time_minutes
 * - Adds cuisine_type based on item analysis  
 * - Adds serving_size based on portion_size or defaults
 */

echo "=== Menu Item Attributes Data Migration ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ItemMaster;
use Illuminate\Support\Facades\DB;

try {
    
    echo "Starting migration of menu item attributes...\n\n";
    
    // Get all menu items that need attribute updates
    $menuItems = ItemMaster::where('is_menu_item', true)->get();
    
    echo "Found {$menuItems->count()} menu items to process.\n\n";
    
    $updated = 0;
    $errors = 0;
    
    foreach ($menuItems as $item) {
        echo "Processing: {$item->name} (ID: {$item->id})\n";
        
        $attributes = is_array($item->attributes) ? $item->attributes : [];
        $updated_attributes = $attributes;
        
        // Map prep_time to prep_time_minutes if missing
        if (empty($attributes['prep_time_minutes']) && !empty($attributes['prep_time'])) {
            $updated_attributes['prep_time_minutes'] = (int) $attributes['prep_time'];
            echo "  - Mapped prep_time ({$attributes['prep_time']}) to prep_time_minutes\n";
        } elseif (empty($attributes['prep_time_minutes'])) {
            // Default prep time based on item type
            $defaultPrepTime = 15; // Default 15 minutes
            if (stripos($item->name, 'drink') !== false || stripos($item->name, 'juice') !== false || stripos($item->name, 'cola') !== false || stripos($item->name, 'coffee') !== false) {
                $defaultPrepTime = 5; // Drinks are quick
            } elseif (stripos($item->name, 'pizza') !== false) {
                $defaultPrepTime = 20; // Pizza takes longer
            }
            $updated_attributes['prep_time_minutes'] = $defaultPrepTime;
            echo "  - Added default prep_time_minutes: {$defaultPrepTime}\n";
        }
        
        // Add cuisine_type if missing
        if (empty($attributes['cuisine_type'])) {
            $cuisine = 'International'; // Default
            
            // Analyze item name for cuisine type
            $name_lower = strtolower($item->name);
            if (stripos($name_lower, 'pizza') !== false || stripos($name_lower, 'margherita') !== false) {
                $cuisine = 'Italian';
            } elseif (stripos($name_lower, 'caesar') !== false) {
                $cuisine = 'Mediterranean';
            } elseif (stripos($name_lower, 'wings') !== false || stripos($name_lower, 'chicken') !== false) {
                $cuisine = 'American';
            } elseif (stripos($name_lower, 'cake') !== false || stripos($name_lower, 'tiramisu') !== false || stripos($name_lower, 'ice cream') !== false) {
                $cuisine = 'Dessert';
            } elseif (stripos($name_lower, 'coffee') !== false || stripos($name_lower, 'espresso') !== false) {
                $cuisine = 'Beverage';
            } elseif (stripos($name_lower, 'cola') !== false || stripos($name_lower, 'juice') !== false) {
                $cuisine = 'Beverage';
            }
            
            $updated_attributes['cuisine_type'] = $cuisine;
            echo "  - Added cuisine_type: {$cuisine}\n";
        }
        
        // Add serving_size if missing
        if (empty($attributes['serving_size'])) {
            $serving_size = '1 person'; // Default
            
            // Use existing portion_size if available
            if (!empty($attributes['portion_size'])) {
                $portion = $attributes['portion_size'];
                if ($portion === 'regular') {
                    $serving_size = '1-2 people';
                } elseif ($portion === 'large') {
                    $serving_size = '2-3 people';
                } elseif ($portion === 'small') {
                    $serving_size = '1 person';
                }
            } else {
                // Analyze item name for serving size
                $name_lower = strtolower($item->name);
                if (stripos($name_lower, 'pizza') !== false) {
                    $serving_size = '2-4 people';
                } elseif (stripos($name_lower, 'salad') !== false) {
                    $serving_size = '1-2 people';
                } elseif (stripos($name_lower, 'wings') !== false) {
                    $serving_size = '1-2 people';
                } elseif (stripos($name_lower, 'cake') !== false) {
                    $serving_size = '1 slice';
                } elseif (stripos($name_lower, 'drink') !== false || stripos($name_lower, 'juice') !== false || stripos($name_lower, 'cola') !== false || stripos($name_lower, 'coffee') !== false) {
                    $serving_size = '1 glass/cup';
                }
            }
            
            $updated_attributes['serving_size'] = $serving_size;
            echo "  - Added serving_size: {$serving_size}\n";
        }
        
        // Update the item if attributes were changed
        if ($updated_attributes !== $attributes) {
            try {
                $item->attributes = $updated_attributes;
                $item->save();
                $updated++;
                echo "  ✓ Successfully updated\n";
            } catch (Exception $e) {
                $errors++;
                echo "  ✗ Error updating: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  - No changes needed\n";
        }
        
        echo "\n";
    }
    
    echo "=== MIGRATION SUMMARY ===\n";
    echo "Total items processed: {$menuItems->count()}\n";
    echo "Items updated: {$updated}\n";
    echo "Errors: {$errors}\n\n";
    
    if ($updated > 0) {
        echo "Running verification to check updated items...\n\n";
        
        // Re-check menu items
        $validMenuItems = ItemMaster::where('is_menu_item', true)
            ->where('is_active', true)
            ->get()
            ->filter(function ($item) {
                $attributes = is_array($item->attributes) ? $item->attributes : [];
                $requiredAttrs = ['cuisine_type', 'prep_time_minutes', 'serving_size'];
                
                foreach ($requiredAttrs as $attr) {
                    if (empty($attributes[$attr])) {
                        return false;
                    }
                }
                return true;
            });
        
        echo "Menu items now valid for orders: {$validMenuItems->count()}\n";
        
        if ($validMenuItems->count() > 0) {
            echo "\nSample updated items:\n";
            foreach ($validMenuItems->take(3) as $item) {
                $attributes = $item->attributes;
                echo "- {$item->name}: {$attributes['cuisine_type']}, {$attributes['prep_time_minutes']}min, {$attributes['serving_size']}\n";
            }
        }
    }
    
    echo "\nMenu item attributes migration completed! 🎉\n";
    
} catch (Exception $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
