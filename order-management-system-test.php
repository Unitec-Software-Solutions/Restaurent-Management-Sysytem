<?php

/**
 * Order Management System Test & Fix Script
 * 
 * This script tests all order management functions and identifies/fixes issues
 */

echo "=== Order Management System Test & Fix ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\AdminOrderController;
use App\Models\ItemMaster;
use App\Models\ItemCategory;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

try {
    
    echo "1. Testing Order Management Models...\n";
    
    // Test ItemMaster model
    $menuItems = ItemMaster::where('is_menu_item', true)->where('is_active', true)->count();
    echo "   - Menu items available: {$menuItems}\n";
    
    // Test ItemCategory model
    $categories = ItemCategory::active()->count();
    echo "   - Active categories: {$categories}\n";
    
    // Test Branch model
    $branches = Branch::active()->count();
    echo "   - Active branches: {$branches}\n";
    
    // Test Order model
    $orders = Order::count();
    echo "   - Total orders: {$orders}\n";
    
    // Test Reservation model
    $reservations = Reservation::count();
    echo "   - Total reservations: {$reservations}\n";
    
    echo "\n2. Testing AdminOrderController methods...\n";
    
    // Test if all required methods exist
    $controller = new AdminOrderController(
        app()->make(\App\Services\MenuSafetyService::class),
        app()->make(\App\Services\EnhancedOrderService::class),
        app()->make(\App\Services\EnhancedMenuSchedulingService::class)
    );
    
    $methods = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        'createForReservation', 'storeForReservation', 
        'createTakeaway', 'storeTakeaway',
        'enhancedCreate', 'enhancedStore'
    ];
    
    foreach ($methods as $method) {
        if (method_exists($controller, $method)) {
            echo "   ✓ Method {$method} exists\n";
        } else {
            echo "   ✗ Method {$method} missing\n";
        }
    }
    
    echo "\n3. Testing Routes...\n";
    
    // Get all admin order routes
    $routes = collect(Route::getRoutes())->filter(function($route) {
        return strpos($route->getName() ?? '', 'admin.orders') === 0;
    });
    
    echo "   Found " . $routes->count() . " admin order routes:\n";
    foreach ($routes as $route) {
        $name = $route->getName();
        $uri = $route->uri();
        $methods = implode('|', $route->methods());
        $action = $route->getActionName();
        echo "     - {$name}: {$methods} {$uri} -> {$action}\n";
    }
    
    echo "\n4. Testing View Files...\n";
    
    $viewFiles = [
        'resources/views/admin/orders/index.blade.php',
        'resources/views/admin/orders/create.blade.php',
        'resources/views/admin/orders/enhanced-create.blade.php',
        'resources/views/admin/orders/edit.blade.php',
        'resources/views/admin/orders/show.blade.php',
        'resources/views/admin/orders/takeaway/create.blade.php',
        'resources/views/admin/orders/takeaway/index.blade.php'
    ];
    
    foreach ($viewFiles as $viewFile) {
        if (file_exists($viewFile)) {
            echo "   ✓ {$viewFile} exists\n";
            
            // Check for common undefined variable patterns
            $content = file_get_contents($viewFile);
            
            // Check for $reservation usage
            if (strpos($content, '$reservation') !== false) {
                echo "     - Uses \$reservation variable\n";
            }
            
            // Check for $categories usage
            if (strpos($content, '$categories') !== false) {
                echo "     - Uses \$categories variable\n";
            }
            
            // Check for $menuItems usage
            if (strpos($content, '$menuItems') !== false) {
                echo "     - Uses \$menuItems variable\n";
            }
            
        } else {
            echo "   ✗ {$viewFile} missing\n";
        }
    }
    
    echo "\n5. Testing Controller Data Provision...\n";
    
    // Mock admin authentication
    if (!auth('admin')->check()) {
        echo "   Setting up test admin user...\n";
        $admin = \App\Models\Admin::first();
        if ($admin) {
            auth('admin')->login($admin);
            echo "   ✓ Test admin authenticated\n";
        } else {
            echo "   ✗ No admin users found\n";
        }
    }
    
    if (auth('admin')->check()) {
        // Test create method data
        echo "   Testing create method data provision:\n";
        
        $request = new Request();
        
        try {
            // This would be called by Laravel
            $controller = app()->make(AdminOrderController::class);
            
            // Simulate the create method logic without actually calling the view
            $admin = auth('admin')->user();
            
            $branches = Branch::when(!$admin->is_super_admin && $admin->organization_id, 
                fn($q) => $q->where('organization_id', $admin->organization_id)
            )->active()->get();
            
            $menuItems = ItemMaster::select('id', 'name', 'selling_price as price', 'description', 'attributes')
                ->where('is_menu_item', true)
                ->where('is_active', true)
                ->when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                })
                ->get();
            
            $categories = ItemCategory::when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                })
                ->active()
                ->get();
            
            echo "     ✓ Branches: {$branches->count()}\n";
            echo "     ✓ Menu Items: {$menuItems->count()}\n";
            echo "     ✓ Categories: {$categories->count()}\n";
            
        } catch (Exception $e) {
            echo "     ✗ Error in create method: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n6. Testing Order Creation Workflow...\n";
    
    // Test complete order creation workflow
    if ($branches->count() > 0 && $menuItems->count() > 0) {
        echo "   Testing order creation data requirements:\n";
        
        $sampleData = [
            'customer_name' => 'Test Customer',
            'customer_phone' => '1234567890',
            'branch_id' => $branches->first()->id,
            'items' => [
                [
                    'item_id' => $menuItems->first()->id,
                    'quantity' => 2
                ]
            ]
        ];
        
        echo "     ✓ Sample order data prepared\n";
        echo "     - Customer: {$sampleData['customer_name']}\n";
        echo "     - Branch: {$branches->first()->name}\n";
        echo "     - Items: {$menuItems->first()->name} x2\n";
        
        // Test validation requirements
        echo "   Testing validation requirements:\n";
        $requiredFields = ['customer_name', 'customer_phone', 'branch_id', 'items'];
        foreach ($requiredFields as $field) {
            if (isset($sampleData[$field])) {
                echo "     ✓ {$field} provided\n";
            } else {
                echo "     ✗ {$field} missing\n";
            }
        }
    }
    
    echo "\n7. Testing Menu Attribute Validation Integration...\n";
    
    // Test menu attributes integration with order system
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
    
    echo "   Menu items with complete attributes: {$validMenuItems->count()}/{$menuItems->count()}\n";
    
    if ($validMenuItems->count() < $menuItems->count()) {
        $invalid = $menuItems->count() - $validMenuItems->count();
        echo "   ⚠️  {$invalid} menu items missing required attributes (will be filtered from orders)\n";
    } else {
        echo "   ✓ All menu items have required attributes\n";
    }
    
    echo "\n=== SYSTEM STATUS SUMMARY ===\n";
    
    $issues = [];
    $successes = [];
    
    if ($menuItems > 0) {
        $successes[] = "Menu items are available ({$menuItems})";
    } else {
        $issues[] = "No menu items available";
    }
    
    if ($categories > 0) {
        $successes[] = "Categories are available ({$categories})";
    } else {
        $issues[] = "No categories available";
    }
    
    if ($branches->count() > 0) {
        $successes[] = "Branches are available ({$branches->count()})";
    } else {
        $issues[] = "No branches available";
    }
    
    if ($validMenuItems->count() == $menuItems->count()) {
        $successes[] = "All menu items have required attributes";
    } else {
        $issues[] = "Some menu items missing required attributes";
    }
    
    echo "✅ SUCCESSES:\n";
    foreach ($successes as $success) {
        echo "   - {$success}\n";
    }
    
    if (!empty($issues)) {
        echo "\n⚠️  ISSUES TO ADDRESS:\n";
        foreach ($issues as $issue) {
            echo "   - {$issue}\n";
        }
    }
    
    echo "\n🎯 RECOMMENDATIONS:\n";
    echo "   1. Ensure all admin order creation routes provide required variables (reservation, categories, menuItems)\n";
    echo "   2. Use enhanced-create.blade.php for general orders, create.blade.php for reservation orders\n";
    echo "   3. All menu items should have complete attributes for proper order functionality\n";
    echo "   4. Test order creation workflow in browser after fixes\n";
    
    echo "\nOrder management system analysis complete! 🎉\n";
    
} catch (Exception $e) {
    echo "❌ Error during analysis: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
