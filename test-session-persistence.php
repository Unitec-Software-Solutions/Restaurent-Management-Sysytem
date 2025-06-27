<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 TESTING SESSION PERSISTENCE ACROSS REQUESTS\n";
echo "===============================================\n\n";

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Test 1: Create a shared session store
echo "1. CREATING SHARED SESSION CONTEXT\n";
echo "===================================\n";

// Start a session store that we'll reuse
$sessionStore = app('session.store');
$sessionStore->start();
$sessionId = $sessionStore->getId();

echo "✅ Session started: {$sessionId}\n";
echo "✅ Session data before login: " . count($sessionStore->all()) . " items\n";

// Test 2: Perform login with this session
echo "\n2. PERFORMING LOGIN WITH SHARED SESSION\n";
echo "========================================\n";

// Create login request
$loginRequest = Request::create('/admin/login', 'POST', [
    'email' => 'superadmin@rms.com',
    'password' => 'password',
    '_token' => $sessionStore->token()
]);

// Attach the session
$loginRequest->setLaravelSession($sessionStore);

// Add headers that browser would send
$loginRequest->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
$loginRequest->headers->set('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');

echo "Sending login request...\n";
try {
    $loginResponse = app()->handle($loginRequest);
    
    echo "✅ Login response status: {$loginResponse->getStatusCode()}\n";
    
    if ($loginResponse->getStatusCode() === 302) {
        $redirectLocation = $loginResponse->headers->get('Location');
        echo "   - Redirected to: {$redirectLocation}\n";
        
        // Check session after login
        echo "\n✅ Session data after login: " . count($sessionStore->all()) . " items\n";
        
        // List session contents
        foreach ($sessionStore->all() as $key => $value) {
            if (is_string($value) && strlen($value) > 50) {
                $value = substr($value, 0, 50) . '...';
            } elseif (is_array($value) || is_object($value)) {
                $value = '[' . gettype($value) . ']';
            }
            echo "   - {$key}: {$value}\n";
        }
        
        // Check authentication state
        $authCheck = Auth::guard('admin')->check();
        echo "\n✅ Auth state after login: " . ($authCheck ? 'AUTHENTICATED' : 'NOT AUTHENTICATED') . "\n";
        
        if ($authCheck) {
            $user = Auth::guard('admin')->user();
            echo "   - Authenticated user: {$user->email}\n";
        }
        
    } else {
        echo "❌ Login failed with status {$loginResponse->getStatusCode()}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Login request error: {$e->getMessage()}\n";
}

// Test 3: Test dashboard access with the SAME session
echo "\n3. TESTING DASHBOARD ACCESS WITH SAME SESSION\n";
echo "==============================================\n";

if (isset($loginResponse) && $loginResponse->getStatusCode() === 302) {
    // Create dashboard request with the SAME session store
    $dashboardRequest = Request::create('/admin/dashboard', 'GET');
    $dashboardRequest->setLaravelSession($sessionStore);
    $dashboardRequest->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    echo "Sending dashboard request with same session...\n";
    echo "   - Session ID: {$sessionStore->getId()}\n";
    echo "   - Session data count: " . count($sessionStore->all()) . "\n";
    
    // Check auth state before dashboard request
    $authBeforeDash = Auth::guard('admin')->check();
    echo "   - Auth state before dashboard: " . ($authBeforeDash ? 'AUTHENTICATED' : 'NOT AUTHENTICATED') . "\n";
    
    try {
        $dashboardResponse = app()->handle($dashboardRequest);
        
        echo "✅ Dashboard response status: {$dashboardResponse->getStatusCode()}\n";
        
        if ($dashboardResponse->getStatusCode() === 200) {
            echo "   ✅ Dashboard accessible: SUCCESS!\n";
        } elseif ($dashboardResponse->getStatusCode() === 302) {
            $dashRedirect = $dashboardResponse->headers->get('Location');
            echo "   ❌ Dashboard redirected to: {$dashRedirect}\n";
            
            // Check why it redirected
            $authAfterDash = Auth::guard('admin')->check();
            echo "   - Auth state during dashboard request: " . ($authAfterDash ? 'AUTHENTICATED' : 'NOT AUTHENTICATED') . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Dashboard request error: {$e->getMessage()}\n";
    }
}

// Test 4: Check database session storage
echo "\n4. CHECKING DATABASE SESSION STORAGE\n";
echo "=====================================\n";

try {
    $dbSession = DB::table('sessions')->where('id', $sessionId)->first();
    
    if ($dbSession) {
        echo "✅ Session found in database:\n";
        echo "   - ID: {$dbSession->id}\n";
        echo "   - User ID: " . ($dbSession->user_id ?? 'NULL') . "\n";
        echo "   - IP: {$dbSession->ip_address}\n";
        echo "   - User Agent: " . substr($dbSession->user_agent, 0, 50) . "...\n";
        echo "   - Last Activity: " . date('Y-m-d H:i:s', $dbSession->last_activity) . "\n";
        
        // Decode session payload to see what's stored
        $payload = $dbSession->payload;
        if ($payload) {
            $decoded = base64_decode($payload);
            echo "   - Payload size: " . strlen($decoded) . " bytes\n";
            
            // Try to unserialize and check for auth data
            $unserialized = @unserialize($decoded);
            if ($unserialized) {
                echo "   - Unserialized data keys: " . implode(', ', array_keys($unserialized)) . "\n";
                
                // Look for auth-related keys
                foreach ($unserialized as $key => $value) {
                    if (str_contains($key, 'login_') || str_contains($key, 'auth')) {
                        echo "   - Auth key found: {$key}\n";
                    }
                }
            } else {
                echo "   - Could not unserialize payload\n";
            }
        }
    } else {
        echo "❌ Session not found in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database session check error: {$e->getMessage()}\n";
}

// Test 5: Direct middleware test with session
echo "\n5. DIRECT MIDDLEWARE TEST WITH SESSION\n";
echo "=======================================\n";

if (Auth::guard('admin')->check()) {
    echo "Testing middleware with authenticated user...\n";
    
    $testRequest = Request::create('/admin/dashboard', 'GET');
    $testRequest->setLaravelSession($sessionStore);
    
    $middleware = new \App\Http\Middleware\EnhancedAdminAuth();
    
    try {
        $middlewareResponse = $middleware->handle($testRequest, function ($req) {
            return response()->json(['status' => 'middleware_success', 'user' => Auth::guard('admin')->user()->email]);
        });
        
        echo "✅ Middleware response status: {$middlewareResponse->getStatusCode()}\n";
        
        if ($middlewareResponse->getStatusCode() === 200) {
            $content = $middlewareResponse->getContent();
            echo "   - Response: {$content}\n";
        } else {
            echo "   - Middleware blocked access\n";
            if ($middlewareResponse->getStatusCode() === 302) {
                echo "   - Redirect to: {$middlewareResponse->headers->get('Location')}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Middleware test error: {$e->getMessage()}\n";
    }
} else {
    echo "❌ No authenticated user for middleware test\n";
}

echo "\n🎯 SESSION PERSISTENCE TEST SUMMARY\n";
echo "====================================\n";

$sessionIssues = [];

if (!isset($loginResponse) || $loginResponse->getStatusCode() !== 302) {
    $sessionIssues[] = "Login not working";
}

if (!isset($dashboardResponse) || $dashboardResponse->getStatusCode() !== 200) {
    $sessionIssues[] = "Dashboard not accessible after login";
}

if (!Auth::guard('admin')->check()) {
    $sessionIssues[] = "Authentication state not maintained";
}

if (empty($sessionIssues)) {
    echo "🎉 SESSION PERSISTENCE WORKING!\n";
    echo "The login system should now work correctly in browsers.\n";
} else {
    echo "⚠️ SESSION PERSISTENCE ISSUES:\n";
    foreach ($sessionIssues as $issue) {
        echo "   ❌ {$issue}\n";
    }
}

echo "\n🏁 Session persistence testing completed!\n";
