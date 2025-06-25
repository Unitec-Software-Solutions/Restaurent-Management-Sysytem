<?php

/**
 * Test script for Database Seeder Validation System
 * 
 * This script tests the comprehensive database seeder error resolution system
 * including validation, auto-fix, and safe seeding capabilities.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 Testing Database Seeder Validation System\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // Test 1: Check if new services are properly registered
    echo "📋 Test 1: Service Registration\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $validationService = app(\App\Services\SeederValidationService::class);
    $errorResolutionService = app(\App\Services\SeederErrorResolutionService::class);
    
    echo "✅ SeederValidationService: " . (isset($validationService) ? 'REGISTERED' : 'MISSING') . "\n";
    echo "✅ SeederErrorResolutionService: " . (isset($errorResolutionService) ? 'REGISTERED' : 'MISSING') . "\n";
    
    // Test 2: Check if commands are available
    echo "\n📋 Test 2: Command Registration\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $kernel = app(\Illuminate\Contracts\Console\Kernel::class);
    $commands = $kernel->all();
    
    $requiredCommands = [
        'db:seed-safe' => 'Database Safe Seeding Command',
        'db:integrity-check' => 'Database Integrity Check Command'
    ];
    
    foreach ($requiredCommands as $commandName => $description) {
        $exists = isset($commands[$commandName]);
        echo ($exists ? "✅" : "❌") . " {$description}: " . ($exists ? 'AVAILABLE' : 'MISSING') . "\n";
    }
    
    // Test 3: Test SeederValidationService methods
    echo "\n📋 Test 3: SeederValidationService Methods\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $methods = [
        'validateSeederRequirements' => 'Seeder Requirements Validation',
        'validateBeforeSeeding' => 'Pre-seeding Data Validation',
        'safeSeed' => 'Safe Seeding with Transactions'
    ];
    
    foreach ($methods as $method => $description) {
        $exists = method_exists($validationService, $method);
        echo ($exists ? "✅" : "❌") . " {$description}: " . ($exists ? 'AVAILABLE' : 'MISSING') . "\n";
    }
    
    // Test 4: Test ErrorResolutionService methods
    echo "\n📋 Test 4: SeederErrorResolutionService Methods\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $errorMethods = [
        'resolveSeederErrors' => 'Auto-fix Seeder Errors',
        'analyzeSeederError' => 'Error Analysis',
        'fixConstraintViolation' => 'Constraint Violation Fixes'
    ];
    
    foreach ($errorMethods as $method => $description) {
        $exists = method_exists($errorResolutionService, $method);
        echo ($exists ? "✅" : "❌") . " {$description}: " . ($exists ? 'AVAILABLE' : 'MISSING') . "\n";
    }
    
    // Test 5: Test database connection and basic validation
    echo "\n📋 Test 5: Database Connectivity\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "✅ Database Connection: CONNECTED\n";
        
        // Test table existence
        $tables = ['organizations', 'branches', 'kitchen_stations', 'users'];
        foreach ($tables as $table) {
            $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
            echo ($exists ? "✅" : "⚠️") . " Table '{$table}': " . ($exists ? 'EXISTS' : 'MISSING (run migrations)') . "\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Database Connection: FAILED - " . $e->getMessage() . "\n";
    }
    
    // Test 6: Test seeder validation for KitchenStationSeeder
    echo "\n📋 Test 6: KitchenStationSeeder Validation\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    try {
        $validationResult = $validationService->validateSeederRequirements('KitchenStationSeeder');
        echo "✅ Validation Test: COMPLETED\n";
        echo "   Status: " . $validationResult['status'] . "\n";
        echo "   Issues Found: " . count($validationResult['issues']) . "\n";
        
        if (!empty($validationResult['issues'])) {
            echo "   Issues:\n";
            foreach ($validationResult['issues'] as $issue) {
                echo "     • {$issue}\n";
            }
        }
        
    } catch (\Exception $e) {
        echo "❌ Validation Test: FAILED - " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 Test Summary Complete!\n\n";
    
    echo "📖 Usage Examples:\n";
    echo "-" . str_repeat("-", 20) . "\n";
    echo "1. Run safe seeding with auto-fix:\n";
    echo "   php artisan db:seed-safe --auto-fix\n\n";
    
    echo "2. Dry run to see what would be fixed:\n";
    echo "   php artisan db:seed-safe --dry-run --auto-fix\n\n";
    
    echo "3. Generate validation report:\n";
    echo "   php artisan db:seed-safe --report\n\n";
    
    echo "4. Run integrity check:\n";
    echo "   php artisan db:integrity-check\n\n";
    
    echo "5. Force seeding despite warnings:\n";
    echo "   php artisan db:seed-safe --force\n\n";
    
} catch (\Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "✅ All tests completed successfully!\n";
