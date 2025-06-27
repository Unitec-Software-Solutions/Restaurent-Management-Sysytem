<?php

echo "=== Menu Deactivation Diagnostic ===\n\n";

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$app->boot();

echo "1. Finding an active menu to test deactivation:\n";

// Find an active menu or create one for testing
$activeMenu = \App\Models\Menu::where('is_active', true)->first();

if (!$activeMenu) {
    echo "No active menu found. Creating a test menu...\n";
    
    $organization = \App\Models\Organization::first();
    $branch = \App\Models\Branch::first();
    $admin = \App\Models\Admin::first();
    
    if (!$organization || !$branch || !$admin) {
        echo "✗ Missing required data (organization, branch, or admin)\n";
        exit;
    }
    
    $today = \Carbon\Carbon::now()->format('Y-m-d');
    $dayName = strtolower(\Carbon\Carbon::now()->format('l'));
    
    $activeMenu = \App\Models\Menu::create([
        'name' => 'Test Deactivation Menu ' . $today,
        'description' => 'Test menu for deactivation testing',
        'date_from' => $today,
        'date_to' => $today,
        'valid_from' => $today,
        'valid_until' => $today,
        'available_days' => [$dayName],
        'start_time' => '00:00',
        'end_time' => '23:59',
        'type' => 'all_day',
        'is_active' => true, // Make it active first
        'menu_type' => 'regular',
        'branch_id' => $branch->id,
        'organization_id' => $organization->id,
        'priority' => 1,
        'auto_activate' => false,
        'created_by' => $admin->id
    ]);
    
    echo "✓ Created active test menu: {$activeMenu->name}\n";
} else {
    echo "✓ Found active menu: {$activeMenu->name}\n";
}

echo "  - Current status: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";
echo "  - Menu ID: {$activeMenu->id}\n";

echo "\n2. Testing direct model deactivation:\n";

try {
    // Test the model's deactivate method directly
    echo "Calling \$menu->deactivate()...\n";
    $result = $activeMenu->deactivate();
    echo "Result: " . ($result ? 'Success' : 'Failed') . "\n";
    
    // Check if it actually changed
    $activeMenu->refresh();
    echo "Status after deactivation: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";
    
    if (!$activeMenu->is_active) {
        echo "✓ Direct model deactivation works\n";
        
        // Reactivate for controller test
        $activeMenu->update(['is_active' => true]);
        echo "Reactivated for controller test\n";
    } else {
        echo "✗ Direct model deactivation failed\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error during direct deactivation: " . $e->getMessage() . "\n";
}

echo "\n3. Testing controller deactivation method:\n";

try {
    // Create a controller instance and test the method (skip this for now)
    echo "Testing via model update method instead...\n";
    
    $originalStatus = $activeMenu->is_active;
    echo "Testing \$activeMenu->update(['is_active' => false])...\n";
    
    $updateResult = $activeMenu->update(['is_active' => false]);
    echo "Update result: " . ($updateResult ? 'Success' : 'Failed') . "\n";
    
    // Check actual menu status
    $activeMenu->refresh();
    echo "Menu status after update: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";
    
    // Reset
    $activeMenu->update(['is_active' => $originalStatus]);
    
} catch (Exception $e) {
    echo "✗ Error during controller deactivation: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n4. Testing database update directly:\n";

try {
    echo "Testing direct database update...\n";
    
    $originalStatus = $activeMenu->is_active;
    echo "Original status: " . ($originalStatus ? 'Active' : 'Inactive') . "\n";
    
    // Direct update using Eloquent instead of DB facade
    $updated = \App\Models\Menu::where('id', $activeMenu->id)
        ->update(['is_active' => false]);
    
    echo "Database update result: " . ($updated ? 'Success' : 'Failed') . "\n";
    
    // Check result
    $menuFromDb = \App\Models\Menu::find($activeMenu->id);
    echo "Status from database: " . ($menuFromDb->is_active ? 'Active' : 'Inactive') . "\n";
    
    // Reset
    \App\Models\Menu::where('id', $activeMenu->id)
        ->update(['is_active' => $originalStatus]);
    
} catch (Exception $e) {
    echo "✗ Error during direct database update: " . $e->getMessage() . "\n";
}

echo "\n5. Testing with mass assignment protection:\n";

try {
    // Check if is_active is in fillable
    $fillable = $activeMenu->getFillable();
    echo "Fillable fields include 'is_active': " . (in_array('is_active', $fillable) ? 'Yes' : 'No') . "\n";
    
    if (!in_array('is_active', $fillable)) {
        echo "⚠ is_active not in fillable array - this could be the issue!\n";
    }
    
    // Check if is_active is guarded
    $guarded = $activeMenu->getGuarded();
    echo "Guarded fields: " . json_encode($guarded) . "\n";
    
} catch (Exception $e) {
    echo "✗ Error checking mass assignment: " . $e->getMessage() . "\n";
}

echo "\n6. Testing route accessibility:\n";

try {
    // Test if the route is accessible
    $route = route('admin.menus.deactivate', ['menu' => $activeMenu->id]);
    echo "Deactivate route URL: {$route}\n";
    
    // Check if route exists in route collection
    $routeCollection = app('router')->getRoutes();
    $foundRoute = $routeCollection->getByName('admin.menus.deactivate');
    
    if ($foundRoute) {
        echo "✓ Route exists in collection\n";
        echo "  - URI: " . $foundRoute->uri() . "\n";
        echo "  - Methods: " . implode(', ', $foundRoute->methods()) . "\n";
        echo "  - Action: " . $foundRoute->getActionName() . "\n";
    } else {
        echo "✗ Route not found in collection\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error testing route: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
