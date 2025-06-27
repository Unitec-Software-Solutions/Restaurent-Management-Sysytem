<?php

echo "=== Simple Menu Deactivation Test ===\n\n";

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$app->boot();

echo "1. Direct controller testing (bypassing middleware):\n";

// Find an active menu
$activeMenu = \App\Models\Menu::where('is_active', true)->first();

if (!$activeMenu) {
    echo "Creating test menu...\n";
    
    $organization = \App\Models\Organization::first();
    $branch = \App\Models\Branch::first();
    $admin = \App\Models\Admin::first();
    
    $today = \Carbon\Carbon::now()->format('Y-m-d');
    $dayName = strtolower(\Carbon\Carbon::now()->format('l'));
    
    $activeMenu = \App\Models\Menu::create([
        'name' => 'Direct Test Menu ' . time(),
        'description' => 'Test menu for direct testing',
        'date_from' => $today,
        'date_to' => $today,
        'valid_from' => $today,
        'valid_until' => $today,
        'available_days' => [$dayName],
        'start_time' => '00:00',
        'end_time' => '23:59',
        'type' => 'all_day',
        'is_active' => true,
        'menu_type' => 'regular',
        'branch_id' => $branch->id,
        'organization_id' => $organization->id,
        'priority' => 1,
        'auto_activate' => false,
        'created_by' => $admin->id
    ]);
}

echo "Testing with menu: {$activeMenu->name}\n";
echo "Initial status: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";

// Test 1: Direct model method
echo "\n2. Testing model deactivate method:\n";
$originalStatus = $activeMenu->is_active;

try {
    $result = $activeMenu->deactivate();
    echo "Deactivate result: " . ($result ? 'Success' : 'Failed') . "\n";
    
    $activeMenu->refresh();
    echo "Status after deactivate(): " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";
    
    // Reset for next test
    $activeMenu->update(['is_active' => true]);
    
} catch (Exception $e) {
    echo "Error in model deactivate: " . $e->getMessage() . "\n";
}

// Test 2: Controller method (without middleware)
echo "\n3. Testing controller method directly:\n";

try {
    // Instantiate controller
    $controller = app()->make(\App\Http\Controllers\Admin\MenuController::class);
    
    // Call deactivate method
    $response = $controller->deactivate($activeMenu);
    
    echo "Controller response status: " . $response->getStatusCode() . "\n";
    $responseData = json_decode($response->getContent(), true);
    echo "Controller response data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
    $activeMenu->refresh();
    echo "Status after controller deactivate: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";
    
} catch (Exception $e) {
    echo "Error in controller deactivate: " . $e->getMessage() . "\n";
}

echo "\n4. Testing potential issues:\n";

// Check if there are any database triggers or events
echo "Checking for potential database issues...\n";

try {
    // Test direct update
    $directUpdate = \App\Models\Menu::where('id', $activeMenu->id)->update(['is_active' => false]);
    echo "Direct update result: " . ($directUpdate ? 'Success' : 'Failed') . "\n";
    
    $menuCheck = \App\Models\Menu::find($activeMenu->id);
    echo "Status after direct update: " . ($menuCheck->is_active ? 'Active' : 'Inactive') . "\n";
    
} catch (Exception $e) {
    echo "Error in direct update: " . $e->getMessage() . "\n";
}

echo "\n5. Recommendations based on findings:\n";

if ($activeMenu->is_active) {
    echo "⚠ Menu is still active after all tests\n";
    echo "Possible issues:\n";
    echo "- Database constraint preventing update\n";
    echo "- Model observer interfering with update\n";
    echo "- Middleware blocking the request in browser\n";
} else {
    echo "✓ Deactivation is working at the model/controller level\n";
    echo "If users report issues, it's likely:\n";
    echo "- Authentication/session problems\n";
    echo "- CSRF token issues\n";
    echo "- JavaScript not handling responses correctly\n";
}

echo "\nSUGGESTED FIXES:\n";
echo "1. ✅ Improved JavaScript error handling (already applied)\n";
echo "2. ✅ Fixed route paths (already applied)\n";
echo "3. ✅ Added proper response validation\n";
echo "4. Check browser console for JavaScript errors\n";
echo "5. Verify user is properly authenticated\n";
echo "6. Check network tab for actual HTTP responses\n";

echo "\n=== Test Complete ===\n";
