<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "Checking pivot table...\n";

try {
    $hasTable = Illuminate\Support\Facades\Schema::hasTable('menu_menu_item');
    echo "menu_menu_item table exists: " . ($hasTable ? "YES" : "NO") . "\n";
    
    // Check for alternative table names
    $altNames = ['menu_item_menu', 'menu_items_menus', 'menu_menu_items'];
    foreach ($altNames as $altName) {
        $hasAlt = Illuminate\Support\Facades\Schema::hasTable($altName);
        echo "{$altName} table exists: " . ($hasAlt ? "YES" : "NO") . "\n";
    }
    
    // Check existing tables
    echo "\nAll tables in database:\n";
    $tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        if (str_contains($tableName, 'menu')) {
            echo "  - {$tableName}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
