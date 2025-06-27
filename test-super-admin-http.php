<?php

echo "🌐 TESTING SUPER ADMIN HTTP LOGIN FLOW\n";
echo "======================================\n";

// Initialize cURL for session persistence
$cookieJar = tempnam(sys_get_temp_dir(), 'laravel_cookies');

function makeRequest($url, $postData = null, $cookieJar = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'X-Requested-With: XMLHttpRequest'
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'httpCode' => $httpCode,
        'finalUrl' => $finalUrl
    ];
}

echo "1. GETTING LOGIN PAGE\n";
echo "=====================\n";

$loginPageResponse = makeRequest('http://127.0.0.1:8000/admin/login', null, $cookieJar);

if ($loginPageResponse['httpCode'] == 200) {
    echo "✅ Login page accessible (HTTP {$loginPageResponse['httpCode']})\n";
    
    // Extract CSRF token
    if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $loginPageResponse['response'], $matches)) {
        $csrfToken = $matches[1];
        echo "✅ CSRF token extracted: " . substr($csrfToken, 0, 10) . "...\n";
    } else if (preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', $loginPageResponse['response'], $matches)) {
        $csrfToken = $matches[1];
        echo "✅ CSRF token from input: " . substr($csrfToken, 0, 10) . "...\n";
    } else {
        $csrfToken = 'dummy-token';
        echo "⚠️  No CSRF token found, using dummy\n";
    }
    
} else {
    echo "❌ Login page not accessible (HTTP {$loginPageResponse['httpCode']})\n";
    echo "   Final URL: {$loginPageResponse['finalUrl']}\n";
    exit(1);
}

echo "\n2. SUBMITTING LOGIN FORM\n";
echo "========================\n";

$loginData = http_build_query([
    '_token' => $csrfToken,
    'email' => 'superadmin@rms.com',
    'password' => 'password123'  // Updated to correct password
]);

$loginResponse = makeRequest('http://127.0.0.1:8000/admin/login', $loginData, $cookieJar);

echo "Login response:\n";
echo "   - HTTP Code: {$loginResponse['httpCode']}\n";
echo "   - Final URL: {$loginResponse['finalUrl']}\n";

if (strpos($loginResponse['finalUrl'], '/admin/dashboard') !== false) {
    echo "✅ SUCCESS: Redirected to dashboard!\n";
} else if (strpos($loginResponse['finalUrl'], '/admin/login') !== false) {
    echo "❌ FAILED: Redirected back to login\n";
    
    // Check for error messages in response
    if (preg_match('/<div[^>]*alert[^>]*>([^<]+)<\/div>/', $loginResponse['response'], $matches)) {
        echo "   - Error message: " . trim($matches[1]) . "\n";
    }
    
    // Check for validation errors
    if (strpos($loginResponse['response'], 'invalid') !== false) {
        echo "   - Possible validation errors in response\n";
    }
    
    // Show part of the response for debugging
    echo "   - Response snippet: " . substr(strip_tags($loginResponse['response']), 0, 200) . "...\n";
    
} else {
    echo "⚠️  Unexpected redirect: {$loginResponse['finalUrl']}\n";
}

echo "\n3. TESTING DASHBOARD ACCESS\n";
echo "===========================\n";

$dashboardResponse = makeRequest('http://127.0.0.1:8000/admin/dashboard', null, $cookieJar);

echo "Dashboard response:\n";
echo "   - HTTP Code: {$dashboardResponse['httpCode']}\n";
echo "   - Final URL: {$dashboardResponse['finalUrl']}\n";

if ($dashboardResponse['httpCode'] == 200 && strpos($dashboardResponse['finalUrl'], '/admin/dashboard') !== false) {
    echo "✅ SUCCESS: Dashboard accessible!\n";
    
    // Check for dashboard content
    if (strpos($dashboardResponse['response'], 'Dashboard') !== false) {
        echo "   - Dashboard content found\n";
    }
    if (strpos($dashboardResponse['response'], 'Welcome') !== false) {
        echo "   - Welcome message found\n";
    }
    
} else if (strpos($dashboardResponse['finalUrl'], '/admin/login') !== false) {
    echo "❌ FAILED: Dashboard redirected to login\n";
} else {
    echo "⚠️  Unexpected dashboard response\n";
}

echo "\n4. CLEANUP\n";
echo "==========\n";
unlink($cookieJar);
echo "✅ Temporary files cleaned up\n";

echo "\n🏁 HTTP Test completed!\n";
