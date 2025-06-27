<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 DIRECT SUPPLIER CONTROLLER TEST\n";
echo "=================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;
use App\Http\Controllers\SupplierController;

// Login
Auth::guard('admin')->logout();
$authService = new AdminAuthService();
$result = $authService->login('superadmin@rms.com', 'password', false);

if ($result['success']) {
    echo "✅ Login successful\n\n";
} else {
    echo "❌ Login failed\n";
    exit(1);
}

// Test supplier controller directly
echo "Testing SupplierController@index directly:\n";

try {
    $controller = new SupplierController();
    $request = new Request();
    
    $response = $controller->index($request);
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "   - ❌ Redirect to: {$response->getTargetUrl()}\n";
    } elseif ($response instanceof \Illuminate\View\View) {
        echo "   - ✅ View returned successfully\n";
        echo "     View name: {$response->getName()}\n";
    } else {
        echo "   - Response type: " . get_class($response) . "\n";
    }
    
} catch (Exception $e) {
    echo "   - ❌ Exception: {$e->getMessage()}\n";
    echo "     File: {$e->getFile()}:{$e->getLine()}\n";
    
    // Get more details
    if ($e->getPrevious()) {
        echo "     Previous: {$e->getPrevious()->getMessage()}\n";
        echo "     Previous file: {$e->getPrevious()->getFile()}:{$e->getPrevious()->getLine()}\n";
    }
}

echo "\n";
