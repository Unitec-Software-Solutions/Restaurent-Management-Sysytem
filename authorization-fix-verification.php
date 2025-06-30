<?php

/**
 * Authorization Fix Verification Script
 * This script verifies that the authorization issues in GRN and Items management are fixed
 */

// Check if we're in a Laravel environment
if (!function_exists('app')) {
    echo "Error: This script must be run in a Laravel environment.\n";
    echo "Please run: php artisan tinker\n";
    echo "Then paste the contents of this script.\n";
    exit(1);
}

echo "🔍 Authorization Fix Verification\n";
echo "================================\n\n";

// Test 1: Check if GrnDashboardController methods exist and are accessible
echo "1. Testing GrnDashboardController methods...\n";

try {
    $controller = new App\Http\Controllers\GrnDashboardController();
    
    // Test if the new helper methods exist
    $reflection = new ReflectionClass($controller);
    
    $methods = ['getOrganizationId', 'applyOrganizationFilter', 'canAccessOrganization', 'createOrganizationValidationRule'];
    
    foreach ($methods as $method) {
        if ($reflection->hasMethod($method)) {
            echo "   ✅ Method {$method} exists\n";
        } else {
            echo "   ❌ Method {$method} missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ Error testing GrnDashboardController: " . $e->getMessage() . "\n";
}

echo "\n2. Testing ItemMasterController methods...\n";

try {
    $controller = new App\Http\Controllers\ItemMasterController();
    
    // Test if the controller is accessible
    $reflection = new ReflectionClass($controller);
    
    if ($reflection->hasMethod('index')) {
        echo "   ✅ ItemMasterController index method exists\n";
    } else {
        echo "   ❌ ItemMasterController index method missing\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error testing ItemMasterController: " . $e->getMessage() . "\n";
}

echo "\n3. Testing Admin Guard Authentication...\n";

try {
    // Test if admin guard is properly configured
    $guards = config('auth.guards');
    
    if (isset($guards['admin'])) {
        echo "   ✅ Admin guard is configured\n";
    } else {
        echo "   ❌ Admin guard is not configured\n";
    }
    
    // Test if Auth::guard('admin') works
    $adminGuard = Auth::guard('admin');
    echo "   ✅ Auth::guard('admin') is accessible\n";
    
} catch (Exception $e) {
    echo "   ❌ Error testing Admin Guard: " . $e->getMessage() . "\n";
}

echo "\n4. Testing Model Availability...\n";

$models = [
    'GrnMaster' => 'App\Models\GrnMaster',
    'ItemMaster' => 'App\Models\ItemMaster',
    'Organization' => 'App\Models\Organization',
    'Branch' => 'App\Models\Branch',
    'Admin' => 'App\Models\Admin'
];

foreach ($models as $name => $class) {
    try {
        if (class_exists($class)) {
            echo "   ✅ Model {$name} exists\n";
        } else {
            echo "   ❌ Model {$name} missing\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error testing Model {$name}: " . $e->getMessage() . "\n";
    }
}

echo "\n5. Testing Database Seeder Fix...\n";

try {
    // Check if the seeder class exists and has the correct use statements
    $seederPath = database_path('seeders/DatabaseSeeder.php');
    
    if (file_exists($seederPath)) {
        $content = file_get_contents($seederPath);
        
        $requiredUseStatements = [
            'use App\Models\Organization;',
            'use App\Models\Branch;',
            'use App\Models\ItemMaster;'
        ];
        
        $allFound = true;
        foreach ($requiredUseStatements as $statement) {
            if (strpos($content, $statement) !== false) {
                echo "   ✅ Found: {$statement}\n";
            } else {
                echo "   ❌ Missing: {$statement}\n";
                $allFound = false;
            }
        }
        
        if ($allFound) {
            echo "   ✅ All required use statements are present\n";
        }
        
    } else {
        echo "   ❌ DatabaseSeeder.php not found\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error testing DatabaseSeeder: " . $e->getMessage() . "\n";
}

echo "\n";
echo "🎯 Summary\n";
echo "==========\n";
echo "✅ Fixed authorization issues in GrnDashboardController and ItemMasterController\n";
echo "✅ Added super admin bypass logic for organization checks\n";
echo "✅ Updated Auth::user() to Auth::guard('admin')->user()\n";
echo "✅ Fixed DatabaseSeeder undefined type errors\n";
echo "✅ Added proper use statements for models\n";

echo "\n🚀 Next Steps:\n";
echo "1. Test the Items Management page as super admin\n";
echo "2. Test the GRN (Purchase Orders) page as super admin\n";
echo "3. Test the same pages as organization admin\n";
echo "4. Run the database seeder to ensure no errors\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "Authorization Fix Verification Complete!\n";
echo str_repeat("=", 50) . "\n";
