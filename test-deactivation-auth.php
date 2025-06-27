<?php

echo "=== Testing Deactivation with Proper Authentication ===\n\n";

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$app->boot();

echo "1. Setting up authenticated admin session:\n";

// Find and authenticate an admin
$admin = \App\Models\Admin::first();
if (!$admin) {
    echo "✗ No admin found in database\n";
    exit;
}

echo "✓ Found admin: {$admin->name} (ID: {$admin->id})\n";

// Start a session and authenticate
session()->start();
auth('admin')->login($admin);

if (auth('admin')->check()) {
    echo "✓ Admin authenticated successfully\n";
} else {
    echo "✗ Failed to authenticate admin\n";
    exit;
}

echo "\n2. Finding/creating active menu:\n";

$activeMenu = \App\Models\Menu::where('is_active', true)->first();

if (!$activeMenu) {
    $organization = \App\Models\Organization::first();
    $branch = \App\Models\Branch::first();
    
    $today = \Carbon\Carbon::now()->format('Y-m-d');
    $dayName = strtolower(\Carbon\Carbon::now()->format('l'));
    
    $activeMenu = \App\Models\Menu::create([
        'name' => 'Auth Test Menu ' . time(),
        'description' => 'Test menu with authentication',
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

echo "✓ Using menu: {$activeMenu->name} (ID: {$activeMenu->id})\n";
echo "  Current status: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";

echo "\n3. Testing authenticated request:\n";

try {
    // Create an authenticated request
    $url = "/menus/{$activeMenu->id}/deactivate";
    
    $request = \Illuminate\Http\Request::create($url, 'POST');
    $request->headers->set('X-CSRF-TOKEN', csrf_token());
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    // Set session data for authentication
    $request->setLaravelSession(session());
    
    echo "Making authenticated POST request to: {$url}\n";
    
    $response = $app->handle($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
    // Check menu status
    $activeMenu->refresh();
    echo "Menu status after request: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";
    
    if (!$activeMenu->is_active) {
        echo "✓ Deactivation successful!\n";
    } else {
        echo "✗ Deactivation failed - menu still active\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error during authenticated request: " . $e->getMessage() . "\n";
}

echo "\n4. Testing controller method directly with authentication:\n";

try {
    // Test the controller method with proper authentication context
    echo "Calling MenuController::deactivate with authenticated user...\n";
    
    // Ensure we have an active menu for this test
    if (!$activeMenu->is_active) {
        $activeMenu->update(['is_active' => true]);
    }
    
    // Create controller and call method
    $controller = app(\App\Http\Controllers\Admin\MenuController::class);
    $response = $controller->deactivate($activeMenu);
    
    echo "Controller response status: " . $response->getStatusCode() . "\n";
    $responseData = json_decode($response->getContent(), true);
    echo "Controller response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
    // Check final status
    $activeMenu->refresh();
    echo "Final menu status: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";
    
} catch (Exception $e) {
    echo "✗ Controller test error: " . $e->getMessage() . "\n";
}

echo "\n5. Testing browser session simulation:\n";

try {
    // Simulate what happens in a real browser session
    echo "Simulating browser session with cookies and headers...\n";
    
    // Ensure menu is active for this test
    if (!$activeMenu->is_active) {
        $activeMenu->update(['is_active' => true]);
    }
    
    // Create a request that mimics a browser
    $browserRequest = \Illuminate\Http\Request::create(
        "/menus/{$activeMenu->id}/deactivate",
        'POST',
        [],
        [
            'laravel_session' => session()->getId(),
            'XSRF-TOKEN' => csrf_token()
        ],
        [],
        [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Test Browser)',
            'HTTP_REFERER' => 'http://localhost/menus/list',
            'HTTP_X_CSRF_TOKEN' => csrf_token(),
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]
    );
    
    $browserRequest->setLaravelSession(session());
    
    $browserResponse = $app->handle($browserRequest);
    
    echo "Browser simulation status: " . $browserResponse->getStatusCode() . "\n";
    echo "Browser simulation response: " . $browserResponse->getContent() . "\n";
    
    $activeMenu->refresh();
    echo "Menu status after browser simulation: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";
    
} catch (Exception $e) {
    echo "✗ Browser simulation error: " . $e->getMessage() . "\n";
}

echo "\n=== Authentication Test Complete ===\n";
