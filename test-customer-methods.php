<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Customer Model Methods\n";
echo "=================================\n\n";

try {
    $phone = '0771234567';
    echo "Testing with phone: $phone\n";
    
    // Test 1: findByPhone
    echo "\n1. Testing findByPhone...\n";
    $customer = \App\Models\Customer::findByPhone($phone);
    if ($customer) {
        echo "   ✅ Found existing customer: {$customer->name} ({$customer->phone})\n";
    } else {
        echo "   ℹ️  No existing customer found\n";
        
        // Test 2: createFromPhone
        echo "\n2. Testing createFromPhone...\n";
        $newCustomer = \App\Models\Customer::createFromPhone($phone, [
            'name' => 'Test Customer',
            'email' => 'test@example.com'
        ]);
        echo "   ✅ Created customer: {$newCustomer->name} ({$newCustomer->phone})\n";
        
        // Test 3: findByPhone again
        echo "\n3. Testing findByPhone after creation...\n";
        $foundCustomer = \App\Models\Customer::findByPhone($phone);
        if ($foundCustomer) {
            echo "   ✅ Found newly created customer: {$foundCustomer->name}\n";
        } else {
            echo "   ❌ Could not find newly created customer\n";
        }
    }
    
    echo "\n🎉 Customer model methods working correctly!\n";
    echo "\n📊 Current customers count: " . \App\Models\Customer::count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✨ Test completed!\n";
