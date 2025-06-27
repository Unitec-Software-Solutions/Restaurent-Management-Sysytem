<?php

echo "=== Menu Deactivation Frontend Testing ===\n\n";

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$app->boot();

echo "1. Testing actual HTTP request simulation:\n";

// Find an active menu
$activeMenu = \App\Models\Menu::where('is_active', true)->first();

if (!$activeMenu) {
    echo "No active menu found. Creating one...\n";
    
    $organization = \App\Models\Organization::first();
    $branch = \App\Models\Branch::first();
    $admin = \App\Models\Admin::first();
    
    $today = \Carbon\Carbon::now()->format('Y-m-d');
    $dayName = strtolower(\Carbon\Carbon::now()->format('l'));
    
    $activeMenu = \App\Models\Menu::create([
        'name' => 'HTTP Test Menu ' . time(),
        'description' => 'Test menu for HTTP testing',
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

echo "Testing with menu: {$activeMenu->name} (ID: {$activeMenu->id})\n";
echo "Current status: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";

echo "\n2. Simulating HTTP POST request:\n";

try {
    // Create a request to the deactivate endpoint
    $url = "/menus/{$activeMenu->id}/deactivate";
    echo "POST URL: {$url}\n";
    
    // Create a new request
    $request = \Illuminate\Http\Request::create($url, 'POST', [], [], [], [
        'HTTP_X_CSRF_TOKEN' => csrf_token(),
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
    ]);
    
    // Add CSRF token to request
    $request->headers->set('X-CSRF-TOKEN', csrf_token());
    
    echo "CSRF token: " . csrf_token() . "\n";
    
    // Process the request through Laravel
    $response = $app->handle($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
    // Check if menu was actually deactivated
    $activeMenu->refresh();
    echo "Menu status after request: " . ($activeMenu->is_active ? 'Active' : 'Inactive') . "\n";
    
} catch (Exception $e) {
    echo "✗ Error during HTTP simulation: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n3. Testing CSRF token validation:\n";

try {
    // Test without CSRF token
    $requestWithoutCSRF = \Illuminate\Http\Request::create(
        "/menus/{$activeMenu->id}/deactivate", 
        'POST', 
        [], [], [], []
    );
    
    echo "Testing request without CSRF token...\n";
    $responseWithoutCSRF = $app->handle($requestWithoutCSRF);
    echo "Response without CSRF: " . $responseWithoutCSRF->getStatusCode() . "\n";
    
    if ($responseWithoutCSRF->getStatusCode() === 419) {
        echo "✓ CSRF protection is working (419 status)\n";
    }
    
} catch (Exception $e) {
    echo "CSRF test error: " . $e->getMessage() . "\n";
}

echo "\n4. Testing authentication middleware:\n";

try {
    // Check if admin authentication is required
    echo "Checking authentication...\n";
    
    if (auth('admin')->check()) {
        $admin = auth('admin')->user();
        echo "✓ Admin authenticated: {$admin->name}\n";
    } else {
        echo "⚠ No admin authenticated\n";
        
        // Try to authenticate an admin for testing
        $admin = \App\Models\Admin::first();
        if ($admin) {
            auth('admin')->login($admin);
            echo "✓ Authenticated admin for testing: {$admin->name}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Authentication test error: " . $e->getMessage() . "\n";
}

echo "\n5. Testing common error scenarios:\n";

// Test with non-existent menu
try {
    $nonExistentId = 999999;
    $badRequest = \Illuminate\Http\Request::create(
        "/menus/{$nonExistentId}/deactivate", 
        'POST', 
        [], [], [], [
            'HTTP_X_CSRF_TOKEN' => csrf_token()
        ]
    );
    $badRequest->headers->set('X-CSRF-TOKEN', csrf_token());
    
    echo "Testing with non-existent menu ID ({$nonExistentId})...\n";
    $badResponse = $app->handle($badRequest);
    echo "Response for non-existent menu: " . $badResponse->getStatusCode() . "\n";
    
    if ($badResponse->getStatusCode() === 404) {
        echo "✓ Proper 404 handling for non-existent menu\n";
    }
    
} catch (Exception $e) {
    echo "Non-existent menu test: " . $e->getMessage() . "\n";
}

echo "\n6. Checking JavaScript path compatibility:\n";

$jsPath = "/menus/{$activeMenu->id}/deactivate";
$routePath = route('admin.menus.deactivate', ['menu' => $activeMenu->id]);

echo "JavaScript path: {$jsPath}\n";
echo "Laravel route: {$routePath}\n";

if (str_contains($routePath, $jsPath)) {
    echo "✓ JavaScript path matches Laravel route\n";
} else {
    echo "✗ JavaScript path does not match Laravel route\n";
    echo "This could be the issue!\n";
}

echo "\n=== Frontend Testing Complete ===\n";
