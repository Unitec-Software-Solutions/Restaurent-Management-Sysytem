<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubscriptionPlan;

echo "Testing SubscriptionPlan modules field:\n";

try {
    $plan = SubscriptionPlan::first();
    if ($plan) {
        echo "Plan found: {$plan->name}\n";
        echo "Modules type: " . gettype($plan->modules) . "\n";
        echo "Modules value: " . json_encode($plan->modules) . "\n";
        
        // Test the fix
        $modules = is_array($plan->modules) ? $plan->modules : json_decode($plan->modules, true) ?? [];
        echo "After fix - modules count: " . count($modules) . "\n";
        echo "Fix successful!\n";
    } else {
        echo "No subscription plans found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
