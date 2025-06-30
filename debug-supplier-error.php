<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 CHECKING SUPPLIER CONTROLLER ERROR\n";
echo "====================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;

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

// Test supplier route with error handling
echo "Testing /admin/suppliers route:\n";

try {
    $request = Request::create('/admin/suppliers', 'GET');
    $request->setLaravelSession(app('session.store'));
    $request->headers->set('Accept', 'text/html');
    
    $response = app()->handle($request);
    
    echo "   - Status: {$response->getStatusCode()}\n";
    
    if ($response->getStatusCode() === 200) {
        echo "   - ✅ Success\n";
    }
    
} catch (Exception $e) {
    echo "   - ❌ Exception: {$e->getMessage()}\n";
    echo "     File: {$e->getFile()}:{$e->getLine()}\n";
    echo "     Stack trace:\n";
    $trace = $e->getTrace();
    foreach (array_slice($trace, 0, 5) as $i => $frame) {
        echo "       #{$i} {$frame['file']}:{$frame['line']} {$frame['function']}()\n";
    }
}

echo "\n";
