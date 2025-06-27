<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->boot();

try {
    echo "Testing database connection...\n";
    
    // Test basic database connection
    $connection = \Illuminate\Support\Facades\DB::connection('pgsql');
    echo "Database connection: OK\n";
    
    // Check table exists
    $tableExists = \Illuminate\Support\Facades\Schema::hasTable('menu_menu_items');
    echo "Table exists: " . ($tableExists ? 'YES' : 'NO') . "\n";
    
    if ($tableExists) {
        // Get column listing
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('menu_menu_items');
        echo "Columns in menu_menu_items table:\n";
        foreach ($columns as $column) {
            echo "  - $column\n";
        }
        
        // Test a simple query
        $count = \Illuminate\Support\Facades\DB::table('menu_menu_items')->count();
        echo "\nRows in table: $count\n";
        
        // Test Menu model
        echo "\nTesting Menu model...\n";
        $menuCount = \App\Models\Menu::count();
        echo "Total menus: $menuCount\n";
        
        // Test menu with items relationship
        echo "\nTesting Menu with menuItems relationship...\n";
        $menus = \App\Models\Menu::with('menuItems')->limit(1)->get();
        echo "Query successful! Found " . $menus->count() . " menu(s)\n";
        
        if ($menus->isNotEmpty()) {
            $menu = $menus->first();
            echo "First menu: " . $menu->name . "\n";
            echo "Menu items count: " . $menu->menuItems->count() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
