<?php

// Create a simple test that simulates the menu controller logic
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Set up the environment
$app->instance('request', new \Illuminate\Http\Request());

try {
    // Boot the application
    $app->boot();
    
    echo "Application booted successfully\n";
    
    // Test the specific query that's failing
    echo "Testing Menu query...\n";
    
    // Exact query from the MenuController
    $activeMenus = \App\Models\Menu::active()->with(['menuItems', 'branch'])->get();
    
    echo "Success! Retrieved " . $activeMenus->count() . " menus\n";
    
    foreach ($activeMenus as $menu) {
        echo "Menu: " . $menu->name . " - Items: " . $menu->menuItems->count() . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error occurred:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "SQL: N/A\n";
}
