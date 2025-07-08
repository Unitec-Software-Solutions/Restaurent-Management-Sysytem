<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing order creation after organization_id fix...\n";

try {
    // Simulate creating an order like the AdminOrderController does
    $admin = App\Models\Admin::find(1);
    $branch = App\Models\Branch::find(1);
    
    if (!$admin) {
        echo "Admin not found\n";
        exit;
    }
    
    if (!$branch) {
        echo "Branch not found\n";
        exit;
    }
    
    echo "Admin: " . $admin->name . " (Super Admin: " . ($admin->is_super_admin ? 'Yes' : 'No') . ")\n";
    echo "Branch: " . $branch->name . " (Org ID: " . $branch->organization_id . ")\n";
    
    // Determine organization_id based on admin type (same logic as in the controller)
    $organizationId = null;
    if ($admin->is_super_admin) {
        // For super admin, get organization_id from the selected branch
        $organizationId = $branch->organization_id;
        echo "Using organization_id from branch: " . $organizationId . "\n";
    } else {
        // For regular admins, use their organization_id
        $organizationId = $admin->organization_id;
        echo "Using organization_id from admin: " . $organizationId . "\n";
    }
    
    // Generate order number
    $orderNumber = App\Services\OrderNumberService::generate($branch->id);
    echo "Generated order number: " . $orderNumber . "\n";
    
    // Create or find customer first (like the controller does)
    $customer = App\Models\Customer::findByPhone('+94123456789');
    if (!$customer) {
        $customer = App\Models\Customer::createFromPhone('+94123456789', [
            'name' => 'Test Customer',
            'email' => null,
            'preferred_contact' => 'sms',
        ]);
        echo "Created customer: " . $customer->name . " (" . $customer->phone . ")\n";
    } else {
        echo "Found existing customer: " . $customer->name . " (" . $customer->phone . ")\n";
    }
    
    // Test order creation with all required fields
    $order = App\Models\Order::create([
        'order_number' => $orderNumber,
        'customer_name' => $customer->name,
        'customer_phone' => $customer->phone,
        'customer_phone_fk' => $customer->phone,
        'customer_email' => $customer->email,
        'branch_id' => $branch->id,
        'organization_id' => $organizationId,
        'order_type' => 'takeaway_walk_in_demand',
        'order_time' => now(),
        'special_instructions' => 'Test order',
        'status' => 'pending',
        'created_by' => $admin->id,
        'placed_by_admin' => true,
        'order_date' => now(),
        'subtotal' => 0.00,
        'total_amount' => 0.00
    ]);
    
    echo "Order created successfully!\n";
    echo "Order ID: " . $order->id . "\n";
    echo "Order Number: " . $order->order_number . "\n";
    echo "Organization ID: " . $order->organization_id . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
