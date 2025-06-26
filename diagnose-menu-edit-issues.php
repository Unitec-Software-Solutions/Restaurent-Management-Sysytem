<?php

// Diagnostic script for Menu Edit functionality
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Branch;

echo "=== Menu Edit Functionality Diagnostic ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Initialize Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel initialized successfully\n\n";

    // 1. Check Routes
    echo "=== ROUTE ANALYSIS ===\n";
    
    $routes = Route::getRoutes();
    $menuEditRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        $name = $route->getName();
        
        if (strpos($uri, 'menu') !== false && strpos($uri, 'edit') !== false) {
            $menuEditRoutes[] = [
                'uri' => $uri,
                'name' => $name,
                'methods' => implode('|', $route->methods()),
                'action' => $route->getActionName()
            ];
        }
    }
    
    echo "Found " . count($menuEditRoutes) . " menu edit related routes:\n";
    foreach ($menuEditRoutes as $route) {
        echo "- {$route['methods']} {$route['uri']} -> {$route['name']} ({$route['action']})\n";
    }
    echo "\n";

    // 2. Check Menu Model Structure
    echo "=== MENU MODEL ANALYSIS ===\n";
    
    if (class_exists(Menu::class)) {
        $menuModel = new Menu();
        $fillable = $menuModel->getFillable();
        $casts = $menuModel->getCasts();
        
        echo "Fillable fields: " . implode(', ', $fillable) . "\n";
        echo "Casted fields: " . implode(', ', array_keys($casts)) . "\n";
        
        // Check if start_time and end_time are properly handled
        if (in_array('start_time', $fillable) && in_array('end_time', $fillable)) {
            echo "✅ start_time and end_time are fillable\n";
        } else {
            echo "❌ start_time or end_time not fillable\n";
        }
        
        if (isset($casts['start_time']) && isset($casts['end_time'])) {
            echo "✅ start_time and end_time are properly casted\n";
            echo "   start_time cast: {$casts['start_time']}\n";
            echo "   end_time cast: {$casts['end_time']}\n";
        } else {
            echo "❌ start_time or end_time not properly casted\n";
        }
    } else {
        echo "❌ Menu model not found\n";
    }
    echo "\n";

    // 3. Check Database Schema
    echo "=== DATABASE SCHEMA ANALYSIS ===\n";
    
    try {
        $menuColumns = DB::select("DESCRIBE menus");
        $timeFields = [];
        
        foreach ($menuColumns as $column) {
            if (strpos($column->Field, 'time') !== false) {
                $timeFields[] = $column->Field . ' (' . $column->Type . ')';
            }
        }
        
        echo "Time-related fields in menus table:\n";
        foreach ($timeFields as $field) {
            echo "- $field\n";
        }
        
        if (empty($timeFields)) {
            echo "❌ No time fields found in menus table\n";
        } else {
            echo "✅ Time fields found in database\n";
        }
    } catch (Exception $e) {
        echo "❌ Error checking database schema: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 4. Test Sample Menu Data
    echo "=== SAMPLE MENU DATA TEST ===\n";
    
    try {
        $sampleMenu = Menu::with('menuItems')->first();
        
        if ($sampleMenu) {
            echo "✅ Found sample menu: {$sampleMenu->name}\n";
            echo "   ID: {$sampleMenu->id}\n";
            echo "   Start Time: " . ($sampleMenu->start_time ?: 'Not set') . "\n";
            echo "   End Time: " . ($sampleMenu->end_time ?: 'Not set') . "\n";
            echo "   Valid From: " . ($sampleMenu->valid_from ? $sampleMenu->valid_from->format('Y-m-d') : 'Not set') . "\n";
            echo "   Valid Until: " . ($sampleMenu->valid_until ? $sampleMenu->valid_until->format('Y-m-d') : 'Not set') . "\n";
            echo "   Available Days: " . (is_array($sampleMenu->available_days) ? implode(', ', $sampleMenu->available_days) : 'Not set') . "\n";
            echo "   Menu Items Count: " . $sampleMenu->menuItems->count() . "\n";
        } else {
            echo "❌ No sample menu found in database\n";
        }
    } catch (Exception $e) {
        echo "❌ Error fetching sample menu: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 5. Check Related Models
    echo "=== RELATED MODELS ANALYSIS ===\n";
    
    try {
        $menuItemsCount = MenuItem::count();
        $categoriesCount = MenuCategory::count();
        $branchesCount = Branch::count();
        
        echo "✅ MenuItem count: $menuItemsCount\n";
        echo "✅ MenuCategory count: $categoriesCount\n";
        echo "✅ Branch count: $branchesCount\n";
    } catch (Exception $e) {
        echo "❌ Error checking related models: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 6. Check View Files
    echo "=== VIEW FILES ANALYSIS ===\n";
    
    $viewPaths = [
        'resources/views/admin/menus/edit.blade.php',
        'resources/views/admin/menus/create.blade.php',
        'resources/views/admin/menus/show.blade.php',
        'resources/views/admin/menus/list.blade.php'
    ];
    
    foreach ($viewPaths as $path) {
        if (file_exists($path)) {
            echo "✅ View exists: $path\n";
            
            // Check for start_time and end_time fields in edit view
            if (strpos($path, 'edit.blade.php') !== false) {
                $content = file_get_contents($path);
                $hasStartTime = strpos($content, 'name="start_time"') !== false;
                $hasEndTime = strpos($content, 'name="end_time"') !== false;
                
                echo "   Contains start_time field: " . ($hasStartTime ? "✅" : "❌") . "\n";
                echo "   Contains end_time field: " . ($hasEndTime ? "✅" : "❌") . "\n";
            }
        } else {
            echo "❌ View missing: $path\n";
        }
    }
    echo "\n";

    echo "=== DIAGNOSTIC COMPLETE ===\n";
    echo "Please review the above information to identify any issues.\n";

} catch (Exception $e) {
    echo "❌ Fatal error during diagnostic: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
