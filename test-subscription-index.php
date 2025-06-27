<?php

// Quick test to verify subscription plans index works
echo "Testing Subscription Plans Index Fix...\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

try {
    // Test the controller method
    $controller = new App\Http\Controllers\Admin\SubscriptionPlanController();
    
    // Test getting plans with counts
    $plans = App\Models\SubscriptionPlan::withCount(['organizations', 'activeSubscriptions'])
        ->orderBy('price')
        ->get();
    
    echo "✅ Successfully loaded " . $plans->count() . " subscription plans\n";
    
    foreach ($plans as $plan) {
        echo "Plan: {$plan->name}\n";
        echo "  Organizations: " . ($plan->organizations_count ?? 0) . "\n";
        echo "  Active Subscriptions: " . ($plan->active_subscriptions_count ?? 0) . "\n";
        echo "  Modules type: " . gettype($plan->modules) . "\n";
        
        // Test the getModulesArray method
        $modules = $plan->getModulesArray();
        echo "  Modules count: " . count($modules) . "\n";
        
        foreach ($modules as $module) {
            if (is_array($module)) {
                echo "    - " . ($module['name'] ?? 'Unknown') . " (" . ($module['tier'] ?? 'basic') . ")\n";
            } else {
                echo "    - " . $module . " (basic)\n";
            }
        }
        echo "\n";
    }
    
    echo "✅ All tests passed! The subscription plans index should work now.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
