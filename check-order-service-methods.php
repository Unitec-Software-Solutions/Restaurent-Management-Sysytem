<?php

require_once 'vendor/autoload.php';

use App\Services\OrderService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

// Initialize Laravel container
$app = new Container();
Container::setInstance($app);
Facade::setFacadeApplication($app);

try {
    $orderService = new OrderService();
    
    echo "Available methods in OrderService:\n";
    $reflection = new ReflectionClass($orderService);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    foreach($methods as $method) {
        if($method->class === 'App\Services\OrderService') {
            echo "- " . $method->getName() . "\n";
        }
    }
    
    echo "\nChecking specific methods:\n";
    $requiredMethods = [
        'createOrder',
        'updateOrder', 
        'updateOrderStatus',
        'getAvailableStewards',
        'getItemsWithStock',
        'getStockAlerts',
        'cancelOrder'
    ];
    
    foreach($requiredMethods as $method) {
        if(method_exists($orderService, $method)) {
            echo "✓ $method exists\n";
        } else {
            echo "✗ $method missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
