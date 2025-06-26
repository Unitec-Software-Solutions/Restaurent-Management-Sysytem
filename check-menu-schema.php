<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Menu;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Schema Check ===\n";

try {
    // Check PostgreSQL schema
    $columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'menus' ORDER BY ordinal_position");
    
    echo "Menus table columns:\n";
    foreach ($columns as $column) {
        echo "- {$column->column_name}: {$column->data_type}\n";
    }
    
    echo "\n=== Sample Menu Data ===\n";
    
    $menu = Menu::first();
    if ($menu) {
        echo "Sample menu raw data:\n";
        echo "ID: {$menu->id}\n";
        echo "Name: {$menu->name}\n";
        echo "Start Time (raw): " . var_export($menu->getRawOriginal('start_time'), true) . "\n";
        echo "End Time (raw): " . var_export($menu->getRawOriginal('end_time'), true) . "\n";
        echo "Start Time (formatted): " . ($menu->start_time ? \Carbon\Carbon::parse($menu->start_time)->format('H:i') : 'null') . "\n";
        echo "End Time (formatted): " . ($menu->end_time ? \Carbon\Carbon::parse($menu->end_time)->format('H:i') : 'null') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
