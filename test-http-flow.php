<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🌐 TESTING ACTUAL HTTP LOGIN FLOW\n";
echo "==================================\n\n";

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

// Test 1: GET request to login page
echo "1. TESTING GET /admin/login (Login Page)\n";
echo "========================================\n";

try {
    // Simulate a GET request to the login page
    $getRequest = Request::create('/admin/login', 'GET');
    $getRequest->setLaravelSession(app('session.store'));
    
    // Handle the request through Laravel's router
    $getResponse = app()->handle($getRequest);
    
    echo "✅ GET Request Status: {$getResponse->getStatusCode()}\n";
    
    if ($getResponse->getStatusCode() === 200) {
        $content = $getResponse->getContent();
        
        // Check if the page contains expected elements
        $hasLoginForm = str_contains($content, '<form') && str_contains($content, 'password');
        $hasCsrfToken = str_contains($content, '_token') || str_contains($content, 'csrf');
        $hasEmailField = str_contains($content, 'email');
        $hasPasswordField = str_contains($content, 'password');
        
        echo "   ✅ Contains login form: " . ($hasLoginForm ? 'YES' : 'NO') . "\n";
        echo "   ✅ Contains CSRF token: " . ($hasCsrfToken ? 'YES' : 'NO') . "\n";
        echo "   ✅ Contains email field: " . ($hasEmailField ? 'YES' : 'NO') . "\n";
        echo "   ✅ Contains password field: " . ($hasPasswordField ? 'YES' : 'NO') . "\n";
        
        // Extract CSRF token for next request
        $csrfToken = null;
        if (preg_match('/name="_token"[^>]*value="([^"]*)"/', $content, $matches)) {
            $csrfToken = $matches[1];
            echo "   ✅ CSRF Token extracted: " . substr($csrfToken, 0, 10) . "...\n";
        } else {
            echo "   ❌ Could not extract CSRF token from form\n";
        }
        
    } else {
        echo "   ❌ Failed to load login page\n";
        echo "   Response: " . $getResponse->getContent() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ GET request error: {$e->getMessage()}\n";
}

echo "\n2. TESTING POST /admin/login (Form Submission)\n";
echo "===============================================\n";

try {
    // Create a new session for the POST request
    $sessionStore = app('session.store');
    $sessionStore->start();
    $csrfToken = $sessionStore->token();
    
    // Simulate a POST request with form data
    $postRequest = Request::create('/admin/login', 'POST', [
        'email' => 'superadmin@rms.com',
        'password' => 'password',
        '_token' => $csrfToken
    ]);
    
    $postRequest->setLaravelSession($sessionStore);
    
    // Set headers that a browser would send
    $postRequest->headers->set('Content-Type', 'application/x-www-form-urlencoded');
    $postRequest->headers->set('X-Requested-With', 'XMLHttpRequest'); // Simulate AJAX if needed
    
    echo "✅ POST Request created:\n";
    echo "   - Email: superadmin@rms.com\n";
    echo "   - Password: [PROVIDED]\n";
    echo "   - CSRF Token: " . substr($csrfToken, 0, 10) . "...\n";
    
    // Handle the request
    $postResponse = app()->handle($postRequest);
    
    echo "✅ POST Request Status: {$postResponse->getStatusCode()}\n";
    
    // Check response type
    if ($postResponse->getStatusCode() === 302) {
        $location = $postResponse->headers->get('Location');
        echo "   ✅ Redirect response to: {$location}\n";
        
        // Check if redirected to dashboard
        if (str_contains($location, 'dashboard')) {
            echo "   ✅ Redirected to dashboard: SUCCESS\n";
        } elseif (str_contains($location, 'login')) {
            echo "   ❌ Redirected back to login: FAILED\n";
        } else {
            echo "   ⚠️ Redirected to unexpected location\n";
        }
        
    } elseif ($postResponse->getStatusCode() === 200) {
        echo "   ⚠️ No redirect (200 response)\n";
        $content = $postResponse->getContent();
        
        // Check for error messages
        if (str_contains($content, 'error') || str_contains($content, 'invalid')) {
            echo "   ❌ Response contains error messages\n";
        } else {
            echo "   ⚠️ Response without redirect or error\n";
        }
        
    } else {
        echo "   ❌ Unexpected response status\n";
        echo "   Response content: " . substr($postResponse->getContent(), 0, 200) . "...\n";
    }
    
} catch (Exception $e) {
    echo "❌ POST request error: {$e->getMessage()}\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n3. TESTING AUTHENTICATION STATE\n";
echo "================================\n";

use Illuminate\Support\Facades\Auth;

// Check if authentication persisted
if (Auth::guard('admin')->check()) {
    $user = Auth::guard('admin')->user();
    echo "✅ Authentication persisted after request\n";
    echo "   - User: {$user->email}\n";
    echo "   - Session ID: " . session()->getId() . "\n";
} else {
    echo "❌ Authentication did not persist\n";
}

echo "\n4. TESTING DASHBOARD ACCESS\n";
echo "============================\n";

try {
    // Test access to dashboard after login
    $dashboardRequest = Request::create('/admin/dashboard', 'GET');
    $dashboardRequest->setLaravelSession(app('session.store'));
    
    $dashboardResponse = app()->handle($dashboardRequest);
    
    echo "✅ Dashboard request status: {$dashboardResponse->getStatusCode()}\n";
    
    if ($dashboardResponse->getStatusCode() === 200) {
        echo "   ✅ Dashboard accessible: SUCCESS\n";
    } elseif ($dashboardResponse->getStatusCode() === 302) {
        $location = $dashboardResponse->headers->get('Location');
        echo "   ❌ Dashboard redirected to: {$location}\n";
    } else {
        echo "   ❌ Dashboard access failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Dashboard test error: {$e->getMessage()}\n";
}

echo "\n5. CHECKING COMMON BROWSER ISSUES\n";
echo "==================================\n";

// Check session configuration for browser compatibility
$sessionConfig = [
    'driver' => config('session.driver'),
    'lifetime' => config('session.lifetime'),
    'expire_on_close' => config('session.expire_on_close'),
    'encrypt' => config('session.encrypt'),
    'files' => config('session.files'),
    'connection' => config('session.connection'),
    'table' => config('session.table'),
    'store' => config('session.store'),
    'lottery' => config('session.lottery'),
    'cookie' => config('session.cookie'),
    'path' => config('session.path'),
    'domain' => config('session.domain'),
    'secure' => config('session.secure'),
    'http_only' => config('session.http_only'),
    'same_site' => config('session.same_site'),
];

echo "Session configuration:\n";
foreach ($sessionConfig as $key => $value) {
    if (is_array($value)) {
        $value = json_encode($value);
    } elseif (is_bool($value)) {
        $value = $value ? 'true' : 'false';
    }
    echo "   - {$key}: {$value}\n";
}

// Check for potential CSRF issues
echo "\nCSRF configuration:\n";
echo "   - CSRF token: " . (csrf_token() ? 'Generated' : 'Failed') . "\n";
echo "   - App key set: " . (config('app.key') ? 'YES' : 'NO') . "\n";

echo "\n🎯 HTTP FLOW TEST SUMMARY\n";
echo "==========================\n";

$flowIssues = [];

if (!isset($getResponse) || $getResponse->getStatusCode() !== 200) {
    $flowIssues[] = "GET /admin/login not accessible";
}

if (!isset($postResponse) || $postResponse->getStatusCode() !== 302) {
    $flowIssues[] = "POST /admin/login not redirecting properly";
}

if (!Auth::guard('admin')->check()) {
    $flowIssues[] = "Authentication not persisting";
}

if (!isset($dashboardResponse) || $dashboardResponse->getStatusCode() !== 200) {
    $flowIssues[] = "Dashboard not accessible after login";
}

if (empty($flowIssues)) {
    echo "🎉 HTTP LOGIN FLOW WORKING PERFECTLY!\n";
    echo "\n✅ Complete login process verified:\n";
    echo "   1. Login page loads correctly\n";
    echo "   2. Form submission processes successfully\n";
    echo "   3. Authentication persists\n";
    echo "   4. Dashboard is accessible\n";
    echo "\n🌐 Test in browser at: http://localhost:8000/admin/login\n";
} else {
    echo "⚠️ HTTP FLOW ISSUES DETECTED:\n";
    foreach ($flowIssues as $issue) {
        echo "   ❌ {$issue}\n";
    }
}

echo "\n🏁 HTTP flow testing completed!\n";
