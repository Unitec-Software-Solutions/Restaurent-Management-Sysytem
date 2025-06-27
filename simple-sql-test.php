<?php
// Simple test to verify the SQL fix works
echo "=== TESTING SQL FIX VERIFICATION ===\n";

$output = shell_exec('php artisan tinker << EOF
use App\Models\Menu;
try {
    echo "Testing Menu::with(\'menuItems\') query...\n";
    $menus = Menu::with("menuItems")->take(1)->get();
    echo "SUCCESS: Query executed without SQLSTATE[42703] error!\n";
    echo "Found " . $menus->count() . " menu(s)\n";
    if ($menus->count() > 0) {
        $menu = $menus->first();
        echo "First menu: " . $menu->name . "\n";
        echo "Menu items count: " . $menu->menuItems->count() . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
exit
EOF');

echo $output;

// Also test with a direct SQL query similar to what caused the original error
echo "\n=== DIRECT SQL TEST ===\n";

$output2 = shell_exec('php artisan tinker << EOF
use Illuminate\Support\Facades\DB;
try {
    echo "Testing direct SQL query with override_price column...\n";
    $result = DB::select("
        SELECT m.id, m.name, mmi.override_price, mmi.sort_order 
        FROM menus m 
        LEFT JOIN menu_menu_items mmi ON m.id = mmi.menu_id 
        LIMIT 3
    ");
    echo "SUCCESS: Direct SQL query with override_price worked!\n";
    echo "Returned " . count($result) . " rows\n";
    foreach ($result as $row) {
        echo "Menu: " . $row->name . " (override_price: " . ($row->override_price ?? "NULL") . ")\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
exit
EOF');

echo $output2;

echo "\n=== CONCLUSION ===\n";
echo "If you see 'SUCCESS' messages above without any SQLSTATE[42703] errors,\n";
echo "then the original SQL column error has been successfully resolved!\n";
