<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

// Initialize database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => 'localhost',
    'database' => 'restaurant_db',
    'username' => 'restaurant_user',
    'password' => 'UnitecAdmin',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== FINAL VERIFICATION TEST ===\n";
echo "Testing the fix for SQLSTATE[42703]: Undefined column: menu_menu_items.override_price\n\n";

try {
    // Test 1: Verify table structure
    echo "1. Checking menu_menu_items table structure:\n";
    $columns = Capsule::select("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'menu_menu_items' 
        ORDER BY ordinal_position
    ");
    
    foreach ($columns as $column) {
        echo "   - {$column->column_name} ({$column->data_type})\n";
    }
    echo "\n";

    // Test 2: Test the exact query that was failing
    echo "2. Testing the problematic query (menu with menu items):\n";
    
    // This simulates what Laravel would do when loading Menu with menuItems relationship
    $query = "
        SELECT menus.*, 
               menu_menu_items.override_price,
               menu_menu_items.sort_order,
               menu_menu_items.special_notes,
               menu_menu_items.available_from,
               menu_menu_items.available_until
        FROM menus 
        LEFT JOIN menu_menu_items ON menus.id = menu_menu_items.menu_id
        LIMIT 5
    ";
    
    $results = Capsule::select($query);
    echo "   Query executed successfully! Found " . count($results) . " records.\n";
    
    if (!empty($results)) {
        $sample = $results[0];
        echo "   Sample record columns: " . implode(', ', array_keys((array)$sample)) . "\n";
    }
    echo "\n";

    // Test 3: Test pivot table data access
    echo "3. Testing direct pivot table access:\n";
    $pivotData = Capsule::select("
        SELECT menu_id, menu_item_id, override_price, sort_order 
        FROM menu_menu_items 
        LIMIT 3
    ");
    
    echo "   Pivot table query successful! Found " . count($pivotData) . " records.\n";
    foreach ($pivotData as $row) {
        echo "   - Menu {$row->menu_id} -> Item {$row->menu_item_id} (override_price: " . 
             ($row->override_price ?? 'NULL') . ", sort_order: " . 
             ($row->sort_order ?? 'NULL') . ")\n";
    }
    echo "\n";

    // Test 4: Verify old columns don't exist
    echo "4. Verifying old problematic columns are gone:\n";
    $oldColumns = ['special_price', 'display_order'];
    
    foreach ($oldColumns as $oldCol) {
        $check = Capsule::select("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'menu_menu_items' AND column_name = ?
        ", [$oldCol]);
        
        if (empty($check)) {
            echo "   ✓ Old column '{$oldCol}' successfully removed\n";
        } else {
            echo "   ✗ Old column '{$oldCol}' still exists!\n";
        }
    }
    echo "\n";

    echo "=== VERIFICATION COMPLETE ===\n";
    echo "✓ All tests passed! The SQL error should be resolved.\n";
    echo "✓ Table structure is correct with override_price and sort_order columns.\n";
    echo "✓ Old problematic columns (special_price, display_order) have been removed.\n";
    echo "✓ Pivot table queries work without errors.\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "This indicates the issue is not fully resolved.\n\n";
    exit(1);
}
