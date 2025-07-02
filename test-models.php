<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing database connection and models...\n";

try {
    // Test database connection
    $result = DB::select('SELECT 1 as test');
    echo "✅ Database connection: OK\n";
    
    // Test if subscription_plans table exists
    $tableExists = Schema::hasTable('subscription_plans');
    echo "✅ subscription_plans table exists: " . ($tableExists ? 'Yes' : 'No') . "\n";
    
    if ($tableExists) {
        // Test SubscriptionPlan model
        $count = App\Models\SubscriptionPlan::count();
        echo "✅ SubscriptionPlan model works: {$count} records\n";
        
        // Test Module model
        $moduleCount = App\Models\Module::count();
        echo "✅ Module model works: {$moduleCount} records\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
