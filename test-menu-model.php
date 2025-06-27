<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing Menu model...\n";
    
    // Test Menu model instantiation
    $menu = new App\Models\Menu();
    echo "✓ Menu model loaded successfully\n";
    
    // Test fillable fields
    echo "Fillable fields: " . implode(', ', $menu->getFillable()) . "\n";
    
    // Test simple query
    $menus = App\Models\Menu::limit(1)->get();
    echo "✓ Successfully queried menus table: " . $menus->count() . " records\n";
    
    // Test the specific query that was causing the error
    $upcomingMenus = App\Models\Menu::where('valid_from', '>', now())
                                   ->take(5)
                                   ->orderBy('valid_from')
                                   ->get();
    echo "✓ Successfully queried upcoming menus: " . $upcomingMenus->count() . " records\n";
    
    echo "\n✅ All Menu model tests passed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
