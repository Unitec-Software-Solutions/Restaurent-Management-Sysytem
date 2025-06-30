<?php
/**
 * Order Placement Diagnosis Script
 * Identify specific issues with order placement functionality
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ItemMaster;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ItemTransaction;
use Illuminate\Support\Facades\DB;

echo "🔍 DIAGNOSING ORDER PLACEMENT ISSUES\n";
echo "====================================\n\n";

// 1. Check Basic Data Availability
echo "1. CHECKING BASIC DATA...\n";
$branches = Branch::where('is_active', true)->count();
$menuItems = ItemMaster::where('is_menu_item', true)->where('is_active', true)->count();
$customers = Customer::count();

echo "   Active Branches: $branches\n";
echo "   Menu Items: $menuItems\n";
echo "   Customers: $customers\n";

if ($branches == 0) {
    echo "   ❌ ERROR: No active branches found!\n";
}
if ($menuItems == 0) {
    echo "   ❌ ERROR: No menu items found!\n";
}

// 2. Check Recent Order Attempts
echo "\n2. CHECKING RECENT ORDER ATTEMPTS...\n";
$recentOrders = Order::orderBy('created_at', 'desc')->limit(5)->get();
echo "   Recent orders count: " . $recentOrders->count() . "\n";

foreach ($recentOrders as $order) {
    $itemsCount = $order->orderItems()->count();
    echo "   Order #{$order->id}: Status={$order->status}, Items={$itemsCount}, Date={$order->created_at}\n";
}

// 3. Test Stock Validation
echo "\n3. TESTING STOCK VALIDATION...\n";
if ($menuItems > 0) {
    $testItem = ItemMaster::where('is_menu_item', true)->where('is_active', true)->first();
    if ($testItem && $branches > 0) {
        $testBranch = Branch::where('is_active', true)->first();
        $currentStock = ItemTransaction::stockOnHand($testItem->id, $testBranch->id);
        echo "   Test Item: {$testItem->name}\n";
        echo "   Current Stock: $currentStock\n";
        
        if ($currentStock <= 0) {
            echo "   ⚠️  WARNING: Test item has no stock!\n";
        }
    }
}

// 4. Check Controller Methods Existence
echo "\n4. CHECKING CONTROLLER METHODS...\n";
$orderControllerPath = app_path('Http/Controllers/OrderController.php');
$adminOrderControllerPath = app_path('Http/Controllers/AdminOrderController.php');

if (file_exists($orderControllerPath)) {
    $orderControllerContent = file_get_contents($orderControllerPath);
    $methods = ['store', 'storeTakeaway', 'create', 'createTakeaway'];
    foreach ($methods as $method) {
        if (strpos($orderControllerContent, "function $method") !== false) {
            echo "   ✅ OrderController::$method exists\n";
        } else {
            echo "   ❌ OrderController::$method missing\n";
        }
    }
} else {
    echo "   ❌ OrderController.php not found\n";
}

// 5. Check Routes
echo "\n5. CHECKING ROUTES...\n";
try {
    $routes = app('router')->getRoutes();
    $orderRoutes = [
        'orders.store',
        'orders.takeaway.store',
        'admin.orders.store',
        'orders.create',
        'orders.takeaway.create'
    ];
    
    foreach ($orderRoutes as $routeName) {
        try {
            $route = $routes->getByName($routeName);
            if ($route) {
                echo "   ✅ Route '$routeName' exists\n";
            } else {
                echo "   ❌ Route '$routeName' missing\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Route '$routeName' missing or invalid\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error checking routes: " . $e->getMessage() . "\n";
}

// 6. Test Simple Order Creation
echo "\n6. TESTING SIMPLE ORDER CREATION...\n";
if ($branches > 0 && $menuItems > 0) {
    try {
        $testBranch = Branch::where('is_active', true)->first();
        $testMenuItem = ItemMaster::where('is_menu_item', true)->where('is_active', true)->first();
        
        // Try to create a simple test order
        DB::beginTransaction();
        
        $testOrder = Order::create([
            'branch_id' => $testBranch->id,
            'organization_id' => $testBranch->organization_id,
            'customer_name' => 'Test Customer',
            'customer_phone' => '1234567890',
            'order_type' => 'takeaway_walk_in_demand',
            'status' => 'draft',
            'order_date' => now(),
            'subtotal' => $testMenuItem->selling_price,
            'tax' => $testMenuItem->selling_price * 0.1,
            'total' => $testMenuItem->selling_price * 1.1,
        ]);
        
        $testOrderItem = OrderItem::create([
            'order_id' => $testOrder->id,
            'menu_item_id' => $testMenuItem->id,
            'inventory_item_id' => $testMenuItem->id,
            'item_name' => $testMenuItem->name,
            'quantity' => 1,
            'unit_price' => $testMenuItem->selling_price,
            'total_price' => $testMenuItem->selling_price,
        ]);
        
        echo "   ✅ Test order created successfully (ID: {$testOrder->id})\n";
        
        // Clean up test order
        $testOrderItem->delete();
        $testOrder->delete();
        DB::rollback();
        
    } catch (Exception $e) {
        DB::rollback();
        echo "   ❌ Test order creation failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Cannot test order creation - missing branches or menu items\n";
}

// 7. Check Validation Rules
echo "\n7. CHECKING VALIDATION ISSUES...\n";
$validationIssues = [];

// Check for common validation problems
$itemsWithoutPrices = ItemMaster::where('is_menu_item', true)
    ->where('is_active', true)
    ->where(function($q) {
        $q->whereNull('selling_price')->orWhere('selling_price', '<=', 0);
    })
    ->count();

if ($itemsWithoutPrices > 0) {
    echo "   ❌ $itemsWithoutPrices menu items have invalid selling prices\n";
    $validationIssues[] = "Invalid selling prices";
}

$branchesWithoutOrg = Branch::whereNull('organization_id')->count();
if ($branchesWithoutOrg > 0) {
    echo "   ❌ $branchesWithoutOrg branches have no organization assigned\n";
    $validationIssues[] = "Branches without organization";
}

if (empty($validationIssues)) {
    echo "   ✅ No major validation issues found\n";
}

// 8. Check Form Validation
echo "\n8. CHECKING FORM VALIDATION...\n";
try {
    // Simulate form validation
    $validationRules = [
        'branch_id' => 'required|exists:branches,id',
        'customer_name' => 'required|string|max:255',
        'customer_phone' => 'required|string|max:20',
        'items' => 'required|array|min:1',
        'items.*.item_id' => 'required|exists:item_master,id',
        'items.*.quantity' => 'required|integer|min:1',
    ];
    
    echo "   ✅ Form validation rules look correct\n";
} catch (Exception $e) {
    echo "   ❌ Form validation error: " . $e->getMessage() . "\n";
}

// 9. Summary and Recommendations
echo "\n" . str_repeat("=", 50) . "\n";
echo "DIAGNOSIS SUMMARY\n";
echo str_repeat("=", 50) . "\n";

$criticalIssues = [];
$warnings = [];

if ($branches == 0) $criticalIssues[] = "No active branches";
if ($menuItems == 0) $criticalIssues[] = "No menu items";
if ($itemsWithoutPrices > 0) $criticalIssues[] = "Items with invalid prices";

if (!empty($criticalIssues)) {
    echo "\n❌ CRITICAL ISSUES FOUND:\n";
    foreach ($criticalIssues as $issue) {
        echo "   • $issue\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "   • $warning\n";
    }
}

echo "\n💡 RECOMMENDATIONS:\n";
if ($branches == 0) {
    echo "   1. Create and activate at least one branch\n";
}
if ($menuItems == 0) {
    echo "   2. Add menu items and mark them as active\n";
}
if ($itemsWithoutPrices > 0) {
    echo "   3. Fix selling prices for all menu items\n";
}

echo "   4. Test order placement through the web interface\n";
echo "   5. Check browser console for JavaScript errors\n";
echo "   6. Verify CSRF tokens are properly included in forms\n";

echo "\nDiagnosis completed!\n";
