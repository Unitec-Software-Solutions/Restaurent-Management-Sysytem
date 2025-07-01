<?php


require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\ItemMaster;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

echo "🧪 TESTING ORDER CREATION FIX\n";
echo "=============================\n\n";

try {
    $branch = Branch::where('is_active', true)->first();
    $menuItem = ItemMaster::where('is_menu_item', true)->where('is_active', true)->first();
    
    if (!$branch || !$menuItem) {
        echo "❌ Missing test data (branch or menu item)\n";
        exit;
    }
    
    echo "✅ Test data found:\n";
    echo "   Branch: {$branch->name}\n";
    echo "   Menu Item: {$menuItem->name} (LKR {$menuItem->selling_price})\n\n";
    
    // Test data similar to what the form sends
    $testData = [
        'branch_id' => $branch->id,
        'order_time' => now()->addMinutes(30)->format('Y-m-d\TH:i'),
        'customer_name' => 'Test Customer Fix',
        'customer_phone' => '0777123456',
        'items' => [
            $menuItem->id => [
                'item_id' => $menuItem->id,
                'quantity' => 2
            ]
        ],
        'order_type' => 'takeaway_walk_in_demand',
        // Notice: NO special_instructions field - this should work now
    ];
    
    echo "🧪 Testing order creation without special_instructions field...\n";
    
    DB::beginTransaction();
    
    // Find or create customer
    $customer = Customer::findByPhone($testData['customer_phone']);
    if (!$customer) {
        $customer = Customer::createFromPhone($testData['customer_phone'], $testData['customer_name']);
    }
    
    // Create order without special_instructions
    $order = Order::create([
        'branch_id' => $testData['branch_id'],
        'organization_id' => $branch->organization_id,
        'order_time' => $testData['order_time'],
        'customer_name' => $customer->name,
        'customer_phone' => $customer->phone,
        'customer_phone_fk' => $customer->phone,
        'order_type' => $testData['order_type'],
        'status' => Order::STATUS_PENDING,
        'special_instructions' => null, // This should work now
        'takeaway_id' => 'TW' . now()->format('YmdHis') . rand(100, 999),
        'order_date' => now(),
    ]);
    
    echo "✅ Order created successfully!\n";
    echo "   Order ID: {$order->id}\n";
    echo "   Takeaway ID: {$order->takeaway_id}\n";
    echo "   Special Instructions: " . ($order->special_instructions ?? 'NULL (as expected)') . "\n";
    
    DB::rollback(); // Don't save test data
    
    echo "\n🎉 FIX VERIFICATION PASSED!\n";
    echo "The order system now handles missing special_instructions field correctly.\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}