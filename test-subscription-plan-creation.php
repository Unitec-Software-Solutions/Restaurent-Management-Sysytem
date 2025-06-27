<?php

// Test subscription plan creation
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Subscription Plan Creation...\n\n";

try {
    // Get modules
    $modules = App\Models\Module::active()->get(['id', 'name']);
    echo "✅ Available modules: " . $modules->count() . "\n";
    
    // Test data
    $testData = [
        'name' => 'Test Plan',
        'price' => 99.99,
        'modules' => $modules->pluck('id')->toArray(),
        'description' => 'Test subscription plan',
        'currency' => 'USD',
        'max_branches' => 5,
        'max_employees' => 50,
        'trial_period_days' => 30,
        'features' => ['feature1', 'feature2'],
        'is_trial' => false
    ];
    
    echo "Creating test subscription plan...\n";
    $plan = App\Models\SubscriptionPlan::create($testData);
    
    echo "✅ Subscription plan created successfully!\n";
    echo "   - ID: {$plan->id}\n";
    echo "   - Name: {$plan->name}\n";
    echo "   - Price: {$plan->currency} {$plan->price}\n";
    echo "   - Modules: " . count($plan->modules) . "\n";
    echo "   - Max Branches: " . ($plan->max_branches ?? 'Unlimited') . "\n";
    echo "   - Max Employees: " . ($plan->max_employees ?? 'Unlimited') . "\n";
    
    // Clean up - delete the test plan
    $plan->delete();
    echo "✅ Test plan cleaned up\n";
    
    echo "\n🎉 Subscription plan creation is working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
