<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Events\Dispatcher;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\KitchenStation;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application to trigger observers
$app->boot();

echo "Testing Direct Branch Creation Through Eloquent ORM\n";
echo "================================================\n\n";

try {
    // Get latest organization for testing
    $organization = Organization::latest()->first();
    
    if (!$organization) {
        echo "❌ No organization found. Please run the main test script first.\n";
        exit(1);
    }
    
    echo "1. Using organization: {$organization->name} (ID: {$organization->id})\n";
    
    // Clean up any previous test data
    $existingBranch = Branch::where('name', 'Direct Test Branch')->first();
    if ($existingBranch) {
        echo "2. Cleaning up existing test branch...\n";
        $existingBranch->delete();
    }
    
    // Create branch using Eloquent create (should trigger observer)
    echo "3. Creating branch using Eloquent::create...\n";
    $branch = Branch::create([
        'name' => 'Direct Test Branch',
        'organization_id' => $organization->id,
        'type' => 'cafe',
        'address' => '456 Test Avenue',
        'city' => 'Test City',
        'state' => 'TC',
        'zip' => '54321',
        'phone' => '555-0199',
        'email' => 'directtest@test.com',
        'is_active' => true,
        'is_head_office' => false,
    ]);
    
    echo "✓ Branch created: {$branch->name} (ID: {$branch->id})\n";
    
    // Wait a moment for observer to execute
    sleep(1);
    
    // Check if branch admin was created
    $branchAdmin = Admin::where('branch_id', $branch->id)->first();
    if ($branchAdmin) {
        echo "✓ Branch admin created: {$branchAdmin->email}\n";
    } else {
        echo "❌ Branch admin not created!\n";
    }
    
    // Check if kitchen stations were created
    $kitchenStations = KitchenStation::where('branch_id', $branch->id)->get();
    if ($kitchenStations->count() > 0) {
        echo "✓ Kitchen stations created: {$kitchenStations->count()} stations\n";
        foreach ($kitchenStations as $station) {
            echo "  - {$station->name} ({$station->type}) - Priority: {$station->order_priority}\n";
        }
    } else {
        echo "❌ No kitchen stations created!\n";
    }
    
    echo "\n✅ Direct branch creation test completed!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
