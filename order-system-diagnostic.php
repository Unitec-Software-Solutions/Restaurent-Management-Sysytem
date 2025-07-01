<?php

echo "=== ORDER MANAGEMENT SYSTEM DIAGNOSTIC ===\n\n";

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\ItemMaster;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Customer;
use App\Models\Reservation;
use App\Http\Controllers\AdminOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "1. CHECKING DATABASE CONNECTIVITY...\n";
try {
    DB::connection()->getPdo();
    echo "   ✅ Database connection successful\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. CHECKING REQUIRED TABLES...\n";
$requiredTables = [
    'orders',
    'order_items', 
    'menu_items',
    'item_master',
    'branches',
    'organizations',
    'customers',
    'reservations'
];

foreach ($requiredTables as $table) {
    try {
        DB::table($table)->limit(1)->get();
        echo "   ✅ Table '$table' exists and accessible\n";
    } catch (Exception $e) {
        echo "   ❌ Table '$table' issue: " . $e->getMessage() . "\n";
    }
}

echo "\n3. CHECKING MODEL RELATIONSHIPS...\n";
try {
    // Test Order model
    $order = new Order();
    $fillable = $order->getFillable();
    echo "   ✅ Order model fillable fields: " . count($fillable) . " fields\n";
    
    // Test OrderItem model
    $orderItem = new OrderItem();
    $fillable = $orderItem->getFillable();
    echo "   ✅ OrderItem model fillable fields: " . count($fillable) . " fields\n";
    
} catch (Exception $e) {
    echo "   ❌ Model relationship error: " . $e->getMessage() . "\n";
}

echo "\n4. CHECKING CONTROLLER METHODS...\n";
$controller = new AdminOrderController(
    new \App\Services\MenuSafetyService(),
    new \App\Services\EnhancedOrderService(),
    new \App\Services\EnhancedMenuSchedulingService(),
    new \App\Services\NotificationService()
);

$requiredMethods = [
    'index',
    'create', 
    'store',
    'show',
    'edit',
    'update',
    'destroy'
];

foreach ($requiredMethods as $method) {
    if (method_exists($controller, $method)) {
        echo "   ✅ Method '$method' exists\n";
    } else {
        echo "   ❌ Method '$method' missing\n";
    }
}

echo "\n5. CHECKING SAMPLE DATA...\n";
try {
    $organizationCount = Organization::count();
    $branchCount = Branch::count();
    $menuItemCount = MenuItem::count();
    $itemMasterCount = ItemMaster::count();
    $customerCount = Customer::count();
    $reservationCount = Reservation::count();
    $orderCount = Order::count();
    
    echo "   📊 Organizations: $organizationCount\n";
    echo "   📊 Branches: $branchCount\n";
    echo "   📊 Menu Items: $menuItemCount\n";
    echo "   📊 Item Master: $itemMasterCount\n";
    echo "   📊 Customers: $customerCount\n";
    echo "   📊 Reservations: $reservationCount\n";
    echo "   📊 Orders: $orderCount\n";
    
    if ($branchCount == 0) {
        echo "   ⚠️  No branches found - orders cannot be created without branches\n";
    }
    
    if ($menuItemCount == 0 && $itemMasterCount == 0) {
        echo "   ⚠️  No menu items found - orders cannot be created without items\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Sample data check failed: " . $e->getMessage() . "\n";
}

echo "\n6. TESTING ORDER CREATION FLOW...\n";
try {
    // Test basic order creation without saving
    $testOrder = new Order([
        'customer_name' => 'Test Customer',
        'customer_phone' => '1234567890',
        'order_type' => 'takeaway',
        'status' => 'pending',
        'total_amount' => 100.00
    ]);
    
    echo "   ✅ Order object creation successful\n";
    
    // Test validation
    $request = new Request([
        'customer_name' => 'Test Customer',
        'customer_phone' => '1234567890',
        'branch_id' => 1,
        'order_type' => 'takeaway',
        'items' => [
            [
                'menu_item_id' => 1,
                'quantity' => 2
            ]
        ]
    ]);
    
    echo "   ✅ Request object creation successful\n";
    
} catch (Exception $e) {
    echo "   ❌ Order creation test failed: " . $e->getMessage() . "\n";
}

echo "\n7. CHECKING MISSING METHODS FROM CONTROLLER-FIXES...\n";
$missingMethods = [
    'validateStockForItems',
    'reserveStock', 
    'generateOrderNumber',
    'getStockSummary',
    'validateCart',
    'getMenuAlternatives',
    'getRealTimeAvailability',
    'getInventoryItems',
    'updateMenuAvailability',
    'confirmOrderStock',
    'cancelOrderWithStock'
];

foreach ($missingMethods as $method) {
    if (method_exists($controller, $method)) {
        echo "   ✅ Method '$method' exists\n";
    } else {
        echo "   ❌ Method '$method' missing - may cause order placement issues\n";
    }
}

echo "\n8. CHECKING ROUTE ACCESSIBILITY...\n";
try {
    // Check if routes are properly defined
    $routes = app('router')->getRoutes();
    $adminOrderRoutes = 0;
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'admin/orders')) {
            $adminOrderRoutes++;
        }
    }
    
    echo "   📊 Admin order routes found: $adminOrderRoutes\n";
    
    if ($adminOrderRoutes < 5) {
        echo "   ⚠️  Insufficient admin order routes - expected at least 5 (index, create, store, show, edit)\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Route check failed: " . $e->getMessage() . "\n";
}

echo "\n9. CHECKING FRONTEND ASSETS...\n";
$frontendFiles = [
    'public/js/order-system.js',
    'public/js/enhanced-order.js',
    'resources/views/admin/orders/create.blade.php',
    'resources/views/admin/orders/index.blade.php'
];

foreach ($frontendFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ File '$file' exists\n";
    } else {
        echo "   ❌ File '$file' missing\n";
    }
}

echo "\n10. SUMMARY OF POTENTIAL ISSUES...\n";
echo "   🔍 Common reasons orders can't be placed:\n";
echo "   - Missing required fields in validation\n";
echo "   - Stock validation failures\n";
echo "   - Missing menu items or inventory data\n";
echo "   - Authentication/authorization issues\n";
echo "   - Database constraint violations\n";
echo "   - Missing or incorrect routes\n";
echo "   - Frontend JavaScript errors\n";

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
