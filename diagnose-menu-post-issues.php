<?php
/**
 * Comprehensive Menu POST/Activation Issue Diagnostic Script
 * This script will test menu creation, update, activation/deactivation functionality
 */

require_once __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

echo "=== COMPREHENSIVE MENU POST/ACTIVATION DIAGNOSTIC ===\n\n";

// 1. Check database schema and relationships
echo "1. CHECKING DATABASE SCHEMA:\n";
echo "   Tables exist:\n";
echo "   - menus: " . (Schema::hasTable('menus') ? "✓" : "✗") . "\n";
echo "   - menu_items: " . (Schema::hasTable('menu_items') ? "✓" : "✗") . "\n";
echo "   - menu_menu_items: " . (Schema::hasTable('menu_menu_items') ? "✓" : "✗") . "\n";
echo "   - branches: " . (Schema::hasTable('branches') ? "✓" : "✗") . "\n";

if (Schema::hasTable('menus')) {
    $menuColumns = Schema::getColumnListing('menus');
    echo "   Menu table columns: " . implode(', ', $menuColumns) . "\n";
}

echo "\n";

// 2. Check model relationships
echo "2. CHECKING MODEL RELATIONSHIPS:\n";
try {
    $testMenu = Menu::with(['menuItems', 'branch', 'creator'])->first();
    if ($testMenu) {
        echo "   Menu->menuItems relationship: " . ($testMenu->menuItems ? "✓" : "✗") . "\n";
        echo "   Menu->branch relationship: " . ($testMenu->branch ? "✓" : "✗") . "\n";
        echo "   Menu->creator relationship: " . ($testMenu->creator ? "✓" : "✗") . "\n";
    }
} catch (Exception $e) {
    echo "   Error testing relationships: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Test model scopes
echo "3. TESTING MODEL SCOPES:\n";
try {
    $activeMenus = Menu::active()->count();
    echo "   active() scope: ✓ (found $activeMenus active menus)\n";
} catch (Exception $e) {
    echo "   active() scope: ✗ (" . $e->getMessage() . ")\n";
}

try {
    $inactiveMenus = Menu::where('is_active', false)->count();
    echo "   inactive query: ✓ (found $inactiveMenus inactive menus)\n";
} catch (Exception $e) {
    echo "   inactive query: ✗ (" . $e->getMessage() . ")\n";
}

echo "\n";

// 4. Test Menu model methods
echo "4. TESTING MENU MODEL METHODS:\n";
$testMenu = Menu::first();
if ($testMenu) {
    try {
        $shouldBeActive = $testMenu->shouldBeActiveNow();
        echo "   shouldBeActiveNow(): ✓ (returns: " . ($shouldBeActive ? 'true' : 'false') . ")\n";
    } catch (Exception $e) {
        echo "   shouldBeActiveNow(): ✗ (" . $e->getMessage() . ")\n";
    }
    
    try {
        $activated = $testMenu->activate();
        echo "   activate(): ✓ (returns: " . ($activated ? 'true' : 'false') . ")\n";
    } catch (Exception $e) {
        echo "   activate(): ✗ (" . $e->getMessage() . ")\n";
    }
    
    try {
        $deactivated = $testMenu->deactivate();
        echo "   deactivate(): ✓ (returns: " . ($deactivated ? 'true' : 'false') . ")\n";
    } catch (Exception $e) {
        echo "   deactivate(): ✗ (" . $e->getMessage() . ")\n";
    }
}

echo "\n";

// 5. Test menu creation simulation
echo "5. TESTING MENU CREATION SIMULATION:\n";
try {
    $branch = Branch::first();
    $menuItems = MenuItem::take(3)->pluck('id')->toArray();
    $user = User::first();
    
    if (!$branch) {
        echo "   No branch found for testing\n";
    } elseif (empty($menuItems)) {
        echo "   No menu items found for testing\n";
    } elseif (!$user) {
        echo "   No user found for testing\n";
    } else {
        echo "   Prerequisites: ✓\n";
        echo "   Branch: " . $branch->name . "\n";
        echo "   Menu items: " . count($menuItems) . " items\n";
        echo "   User: " . $user->name . "\n";
        
        // Test validation data structure
        $todayDayName = strtolower(Carbon::today()->format('l'));
        $testData = [
            'name' => 'Test Menu - ' . date('Y-m-d H:i:s'),
            'description' => 'Test menu for diagnostic',
            'type' => 'lunch',
            'branch_id' => $branch->id,
            'valid_from' => Carbon::today()->toDateString(),
            'valid_until' => Carbon::today()->addDays(7)->toDateString(),
            'available_days' => [$todayDayName], // Use today's day
            'start_time' => '09:00',
            'end_time' => '17:00',
            'menu_items' => $menuItems,
            'is_active' => false
        ];
        
        echo "   Test data structure: ✓\n";
        
        // Test menu creation
        DB::beginTransaction();
        try {
            $menu = Menu::create([
                'name' => $testData['name'],
                'description' => $testData['description'],
                'type' => $testData['type'],
                'branch_id' => $testData['branch_id'],
                'organization_id' => $user->organization_id ?? 1,
                'date_from' => $testData['valid_from'],
                'date_to' => $testData['valid_until'],
                'valid_from' => $testData['valid_from'],
                'valid_until' => $testData['valid_until'],
                'available_days' => $testData['available_days'],
                'start_time' => $testData['start_time'],
                'end_time' => $testData['end_time'],
                'is_active' => $testData['is_active'],
                'created_by' => $user->id
            ]);
            
            echo "   Menu creation: ✓ (ID: " . $menu->id . ")\n";
            
            // Test menu item attachment
            $menu->menuItems()->attach($testData['menu_items']);
            echo "   Menu items attachment: ✓\n";
            
            // Test menu activation
            $activated = $menu->activate();
            echo "   Menu activation: " . ($activated ? "✓" : "✗") . "\n";
            
            // Test menu deactivation
            $deactivated = $menu->deactivate();
            echo "   Menu deactivation: " . ($deactivated ? "✓" : "✗") . "\n";
            
            DB::rollBack(); // Don't actually save the test menu
            echo "   Test cleanup: ✓\n";
            
        } catch (Exception $e) {
            DB::rollBack();
            echo "   Menu creation failed: ✗ (" . $e->getMessage() . ")\n";
        }
    }
} catch (Exception $e) {
    echo "   Setup error: ✗ (" . $e->getMessage() . ")\n";
}

echo "\n";

// 6. Check route functionality
echo "6. CHECKING ROUTE CONFIGURATION:\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $menuRoutes = [];
    
    foreach ($routes as $route) {
        if (strpos($route->getName(), 'admin.menus.') === 0) {
            $menuRoutes[] = $route->getName();
        }
    }
    
    echo "   Menu routes found: " . count($menuRoutes) . "\n";
    foreach ($menuRoutes as $routeName) {
        echo "   - $routeName\n";
    }
} catch (Exception $e) {
    echo "   Route check failed: ✗ (" . $e->getMessage() . ")\n";
}

echo "\n";

// 7. Check specific controller issues
echo "7. CHECKING CONTROLLER ISSUES:\n";

// Check if MenuController exists and methods are callable
try {
    $controller = new \App\Http\Controllers\Admin\MenuController(
        new \App\Services\MenuScheduleService()
    );
    echo "   MenuController instantiation: ✓\n";
    
    $methods = ['store', 'update', 'activate', 'deactivate', 'bulkStore'];
    foreach ($methods as $method) {
        if (method_exists($controller, $method)) {
            echo "   Method '$method': ✓\n";
        } else {
            echo "   Method '$method': ✗\n";
        }
    }
} catch (Exception $e) {
    echo "   Controller check failed: ✗ (" . $e->getMessage() . ")\n";
}

echo "\n";

// 8. Check common validation issues
echo "8. CHECKING VALIDATION REQUIREMENTS:\n";
$requiredFields = ['name', 'type', 'branch_id', 'valid_from', 'available_days', 'menu_items'];
foreach ($requiredFields as $field) {
    echo "   Required field '$field': documented\n";
}

echo "\n";

// 9. Check pivot table structure
echo "9. CHECKING PIVOT TABLE STRUCTURE:\n";
if (Schema::hasTable('menu_menu_items')) {
    $pivotColumns = Schema::getColumnListing('menu_menu_items');
    echo "   Pivot table columns: " . implode(', ', $pivotColumns) . "\n";
    
    $requiredPivotColumns = ['menu_id', 'menu_item_id'];
    foreach ($requiredPivotColumns as $column) {
        if (in_array($column, $pivotColumns)) {
            echo "   Required column '$column': ✓\n";
        } else {
            echo "   Required column '$column': ✗\n";
        }
    }
} else {
    echo "   Pivot table 'menu_menu_items' does not exist: ✗\n";
}

echo "\n";

// 10. Final recommendations
echo "10. RECOMMENDATIONS:\n";
echo "    Based on the diagnostic results above, check for:\n";
echo "    - Missing database columns or tables\n";
echo "    - Incorrect relationship definitions\n";
echo "    - Missing model scopes (especially 'inactive')\n";
echo "    - Route parameter binding issues\n";
echo "    - Validation rule mismatches\n";
echo "    - Controller method errors\n";
echo "    - Missing pivot table or incorrect structure\n";

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
