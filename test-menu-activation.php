<?php

echo "=== Testing Menu Activation with Current Date ===\n\n";

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$app->boot();

$now = \Carbon\Carbon::now();
$today = $now->format('Y-m-d');
$dayName = strtolower($now->format('l'));

echo "Current date: {$today}\n";
echo "Current day: {$dayName}\n";
echo "Current time: " . $now->format('H:i:s') . "\n\n";

// Find or create a menu that should be active today
$menu = \App\Models\Menu::where('date_from', '<=', $today)
                       ->where('date_to', '>=', $today)
                       ->first();

if (!$menu) {
    echo "No suitable menu found. Creating a test menu for today...\n";
    
    $organization = \App\Models\Organization::first();
    $branch = \App\Models\Branch::first();
    $admin = \App\Models\Admin::first();
    
    if (!$organization || !$branch || !$admin) {
        echo "✗ Missing required data (organization, branch, or admin)\n";
        exit;
    }
    
    $menu = \App\Models\Menu::create([
        'name' => 'Test Menu ' . $today,
        'description' => 'Test menu for activation',
        'date_from' => $today,
        'date_to' => $today,
        'valid_from' => $today,
        'valid_until' => $today,
        'available_days' => [$dayName],
        'start_time' => '00:00',
        'end_time' => '23:59',
        'type' => 'all_day',
        'is_active' => false,
        'menu_type' => 'regular',
        'branch_id' => $branch->id,
        'organization_id' => $organization->id,
        'priority' => 1,
        'auto_activate' => true,
        'created_by' => $admin->id
    ]);
    
    echo "✓ Created test menu: {$menu->name}\n";
}

echo "Testing menu: {$menu->name}\n";
echo "Menu details:\n";
echo "  - Date from: " . ($menu->date_from ? $menu->date_from->format('Y-m-d') : 'Not set') . "\n";
echo "  - Date to: " . ($menu->date_to ? $menu->date_to->format('Y-m-d') : 'Not set') . "\n";
echo "  - Available days: " . (is_array($menu->available_days) ? implode(', ', $menu->available_days) : 'Not set') . "\n";
echo "  - Current status: " . ($menu->is_active ? 'Active' : 'Inactive') . "\n";

// Test activation logic
echo "\nTesting activation logic:\n";

$shouldBeActive = $menu->shouldBeActiveNow();
echo "Should be active now: " . ($shouldBeActive ? 'Yes' : 'No') . "\n";

if ($shouldBeActive) {
    echo "Attempting to activate menu...\n";
    
    try {
        $result = $menu->activate();
        echo "Activation result: " . ($result ? 'Success' : 'Failed') . "\n";
        
        if ($result) {
            $menu->refresh();
            echo "Menu is now: " . ($menu->is_active ? 'Active' : 'Inactive') . "\n";
            
            // Test deactivation
            echo "\nTesting deactivation...\n";
            $deactivateResult = $menu->deactivate();
            echo "Deactivation result: " . ($deactivateResult ? 'Success' : 'Failed') . "\n";
            
            if ($deactivateResult) {
                $menu->refresh();
                echo "Menu is now: " . ($menu->is_active ? 'Active' : 'Inactive') . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "Error during activation: " . $e->getMessage() . "\n";
    }
} else {
    echo "Cannot test activation - menu should not be active now\n";
    
    // Show why it can't be activated
    if (!$menu->isValidForDate($now)) {
        echo "Reason: Menu is not valid for current date\n";
        
        if ($menu->date_from && $now->lt($menu->date_from)) {
            echo "  - Current date is before start date\n";
        }
        
        if ($menu->date_to && $now->gt($menu->date_to)) {
            echo "  - Current date is after end date\n";
        }
        
        if (!empty($menu->available_days) && is_array($menu->available_days)) {
            if (!in_array($dayName, $menu->available_days)) {
                echo "  - Current day ({$dayName}) not in available days\n";
            }
        }
    }
}

echo "\n=== Test Complete ===\n";
