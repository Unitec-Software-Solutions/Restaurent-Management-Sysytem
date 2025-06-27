<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$plans = \App\Models\SubscriptionPlan::all();

echo "Subscription Plans Data Structure:\n";
echo "=================================\n\n";

foreach($plans as $plan) {
    echo "Plan: {$plan->name}\n";
    echo "Modules type: " . gettype($plan->modules) . "\n";
    echo "Modules data:\n";
    var_dump($plan->modules);
    echo "\n---\n\n";
}
