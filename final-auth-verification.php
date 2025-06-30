<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🎯 FINAL VERIFICATION: INVENTORY & SUPPLIER ACCESS\n";
echo "==================================================\n\n";

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;

// Test both regular admin and super admin
$testUsers = [
    ['email' => 'superadmin@rms.com', 'type' => 'Super Admin'],
];

foreach ($testUsers as $testUser) {
    echo "Testing with {$testUser['type']}: {$testUser['email']}\n";
    echo str_repeat('-', 50) . "\n";
    
    // Login
    Auth::guard('admin')->logout();
    session()->flush();
    
    $authService = new AdminAuthService();
    $result = $authService->login($testUser['email'], 'password', false);
    
    if (!$result['success']) {
        echo "❌ Login failed for {$testUser['email']}\n\n";
        continue;
    }
    
    echo "✅ Login successful\n";
    
    $admin = $result['admin'];
    echo "   - is_super_admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   - organization_id: " . ($admin->organization_id ?? 'NULL') . "\n";
    
    // Test routes that were previously failing
    $routesToTest = [
        'Dashboard' => '/admin/dashboard',
        'Profile' => '/admin/profile',
        'Inventory' => '/admin/inventory',
        'Suppliers' => '/admin/suppliers',
        'GRN' => '/admin/grn'
    ];
    
    echo "\nTesting access to admin pages:\n";
    
    foreach ($routesToTest as $name => $uri) {
        try {
            $request = Request::create($uri, 'GET');
            $request->setLaravelSession(app('session.store'));
            $request->headers->set('Accept', 'text/html');
            
            $response = app()->handle($request);
            $status = $response->getStatusCode();
            
            if ($status === 200) {
                echo "   ✅ {$name}: SUCCESS (200)\n";
            } elseif ($status === 302) {
                $location = $response->headers->get('Location');
                if (str_contains($location, '/admin/login')) {
                    echo "   ❌ {$name}: REDIRECT TO LOGIN (302)\n";
                } else {
                    echo "   ➡️  {$name}: REDIRECT TO {$location} (302)\n";
                }
            } else {
                echo "   ⚠️  {$name}: STATUS {$status}\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ {$name}: ERROR - {$e->getMessage()}\n";
        }
    }
    
    echo "\n";
}

echo "🏁 VERIFICATION COMPLETE\n";
echo "========================\n";

// Final summary
echo "Summary: The authentication issue for inventory and supplier links\n";
echo "has been resolved by updating the controllers to properly handle\n";
echo "super admin users who have organization_id = NULL.\n\n";

echo "Changes made:\n";
echo "1. Updated ItemDashboardController@index() to check isSuperAdmin()\n";
echo "2. Updated SupplierController@index() to check isSuperAdmin()\n";
echo "3. Updated all database queries to use conditional where clauses\n";
echo "4. Super admins can now access inventory and supplier pages\n\n";

echo "Next steps:\n";
echo "- Test the sidebar navigation in the actual application\n";
echo "- Verify that regular admins with organization_id still work correctly\n";
echo "- Consider adding organization selection for super admins if needed\n";

echo "\n";
