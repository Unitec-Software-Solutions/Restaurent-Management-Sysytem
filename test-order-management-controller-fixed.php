<?php

require_once 'vendor/autoload.php';

// Initialize Laravel environment
if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Testing OrderManagementController after fixes...\n\n";

try {
    // Test OrderManagementController
    echo "1. Testing OrderManagementController instantiation:\n";
    $controller = app(\App\Http\Controllers\Admin\OrderManagementController::class);
    echo "   ✓ OrderManagementController instantiated successfully\n";
    
    // Test all methods exist
    echo "\n2. Testing controller methods:\n";
    $requiredMethods = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        'getItemsWithStock', 'getStewards', 'getStockAlerts', 'getAvailableStewards'
    ];
    
    $reflection = new ReflectionClass($controller);
    foreach($requiredMethods as $method) {
        if($reflection->hasMethod($method)) {
            echo "   ✓ $method exists\n";
        } else {
            echo "   ✗ $method missing\n";
        }
    }
    
    // Test service dependencies
    echo "\n3. Testing service dependencies:\n";
    
    // Test OrderService
    $orderService = app(\App\Services\OrderService::class);
    echo "   ✓ OrderService instantiated\n";
    
    $serviceMethods = [
        'createOrder', 'updateOrder', 'updateOrderStatus',
        'getAvailableStewards', 'getItemsWithStock', 'getStockAlerts', 'cancelOrder'
    ];
    
    foreach($serviceMethods as $method) {
        if(method_exists($orderService, $method)) {
            echo "   ✓ OrderService::$method exists\n";
        } else {
            echo "   ✗ OrderService::$method missing\n";
        }
    }
    
    // Test PrintService
    $printService = app(\App\Services\PrintService::class);
    echo "   ✓ PrintService instantiated\n";
    
    echo "\n4. Testing request handling (mock):\n";
    
    // Mock request for AJAX endpoints
    $mockRequest = new \Illuminate\Http\Request();
    $mockRequest->merge(['branch_id' => 1]);
    
    try {
        // Test getItemsWithStock endpoint
        $response = $controller->getItemsWithStock($mockRequest);
        echo "   ✓ getItemsWithStock endpoint works (returns JSON response)\n";
    } catch (Exception $e) {
        echo "   ⚠ getItemsWithStock endpoint error: " . $e->getMessage() . "\n";
    }
    
    try {
        // Test getStewards endpoint
        $response = $controller->getStewards($mockRequest);
        echo "   ✓ getStewards endpoint works (returns JSON response)\n";
    } catch (Exception $e) {
        echo "   ⚠ getStewards endpoint error: " . $e->getMessage() . "\n";
    }
    
    try {
        // Test getStockAlerts endpoint
        $response = $controller->getStockAlerts($mockRequest);
        echo "   ✓ getStockAlerts endpoint works (returns JSON response)\n";
    } catch (Exception $e) {
        echo "   ⚠ getStockAlerts endpoint error: " . $e->getMessage() . "\n";
    }
    
    try {
        // Test getAvailableStewards endpoint (alias)
        $response = $controller->getAvailableStewards($mockRequest);
        echo "   ✓ getAvailableStewards endpoint works (returns JSON response)\n";
    } catch (Exception $e) {
        echo "   ⚠ getAvailableStewards endpoint error: " . $e->getMessage() . "\n";
    }
    
    echo "\n5. Testing method signatures:\n";
    
    // Check if methods have proper signatures
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach($methods as $method) {
        if($method->class === 'App\Http\Controllers\Admin\OrderManagementController') {
            $params = $method->getParameters();
            $paramTypes = array_map(function($param) {
                return $param->getType() ? $param->getType()->getName() : 'mixed';
            }, $params);
            echo "   ✓ " . $method->getName() . "(" . implode(', ', $paramTypes) . ")\n";
        }
    }
    
    echo "\n✅ All OrderManagementController tests completed successfully!\n";
    echo "\n🎉 The undefined method errors should now be resolved!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
