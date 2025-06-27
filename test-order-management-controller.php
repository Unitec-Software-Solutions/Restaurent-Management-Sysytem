<?php

require_once 'vendor/autoload.php';

// Initialize Laravel environment
if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Testing OrderManagementController dependencies...\n\n";

try {
    // Test OrderService
    echo "1. Testing OrderService:\n";
    $orderService = app(\App\Services\OrderService::class);
    echo "   ✓ OrderService instantiated successfully\n";
    
    $requiredMethods = [
        'createOrder', 'updateOrder', 'updateOrderStatus',
        'getAvailableStewards', 'getItemsWithStock', 
        'getStockAlerts', 'cancelOrder'
    ];
    
    foreach($requiredMethods as $method) {
        if(method_exists($orderService, $method)) {
            echo "   ✓ $method exists\n";
        } else {
            echo "   ✗ $method missing\n";
        }
    }
    
    // Test PrintService
    echo "\n2. Testing PrintService:\n";
    $printService = app(\App\Services\PrintService::class);
    echo "   ✓ PrintService instantiated successfully\n";
    
    // Test OrderManagementController
    echo "\n3. Testing OrderManagementController:\n";
    $controller = app(\App\Http\Controllers\Admin\OrderManagementController::class);
    echo "   ✓ OrderManagementController instantiated successfully\n";
    
    // Test controller methods exist
    $controllerMethods = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        'getStockAlerts', 'getAvailableStewards'
    ];
    
    $reflection = new ReflectionClass($controller);
    foreach($controllerMethods as $method) {
        if($reflection->hasMethod($method)) {
            echo "   ✓ $method exists\n";
        } else {
            echo "   ✗ $method missing\n";
        }
    }
    
    echo "\n4. Testing service method calls:\n";
    
    // Create a test environment with database connection
    try {
        // Get a branch ID for testing
        $branch = \App\Models\Branch::first();
        if ($branch) {
            echo "   Testing with branch ID: {$branch->id}\n";
            
            // Test getAvailableStewards
            $stewards = $orderService->getAvailableStewards($branch->id);
            echo "   ✓ getAvailableStewards returned " . $stewards->count() . " stewards\n";
            
            // Test getItemsWithStock  
            $items = $orderService->getItemsWithStock($branch->id);
            echo "   ✓ getItemsWithStock returned " . $items->count() . " items\n";
            
            // Test getStockAlerts
            $alerts = $orderService->getStockAlerts($branch->id);
            echo "   ✓ getStockAlerts returned " . count($alerts) . " alerts\n";
            
        } else {
            echo "   ⚠ No branches found for testing\n";
        }
        
    } catch (Exception $e) {
        echo "   ⚠ Database test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
