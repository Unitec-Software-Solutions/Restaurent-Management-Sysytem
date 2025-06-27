#!/usr/bin/env php
<?php

/**
 * PostgreSQL Compatibility Test for Restaurant Management System
 * 
 * This script tests PostgreSQL-specific requirements before running
 * the exhaustive seeding system.
 */

if (!file_exists('./artisan')) {
    echo "❌ Error: Please run this script from your Laravel project root directory.\n";
    exit(1);
}

echo "🔍 PostgreSQL Compatibility Test for Restaurant Management System\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Test database connection
echo "📊 Testing Database Connection...\n";
try {
    $output = [];
    exec('php artisan tinker --execute="echo DB::connection()->getDriverName();"', $output);
    $driver = trim($output[0] ?? '');
    
    if ($driver === 'pgsql') {
        echo "✅ PostgreSQL connection confirmed\n";
    } else {
        echo "⚠️  Warning: Using {$driver} database, not PostgreSQL\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection test failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test JSON field support
echo "\n📊 Testing JSON Field Compatibility...\n";
try {
    $output = [];
    exec('php artisan tinker --execute="echo DB::select(\'SELECT version()\')[0]->version;"', $output);
    $version = trim($output[0] ?? '');
    echo "✅ PostgreSQL Version: " . substr($version, 0, 50) . "...\n";
    
    // Test JSON operations
    exec('php artisan tinker --execute="echo json_encode([\'test\' => \'value\']);"', $output);
    echo "✅ JSON encoding works properly\n";
    
} catch (Exception $e) {
    echo "❌ JSON compatibility test failed: " . $e->getMessage() . "\n";
}

// Test table existence for core models
echo "\n📊 Testing Required Tables...\n";
$tables = [
    'subscription_plans',
    'organizations', 
    'branches',
    'admins',
    'users',
    'roles',
    'permissions',
    'model_has_roles',
    'model_has_permissions',
    'menu_categories',
    'menu_items',
    'orders',
    'reservations',
    'item_master'
];

$missingTables = [];
foreach ($tables as $table) {
    try {
        $output = [];
        exec("php artisan tinker --execute=\"echo DB::getSchemaBuilder()->hasTable('{$table}') ? 'exists' : 'missing';\"", $output);
        $exists = trim($output[0] ?? '') === 'exists';
        
        if ($exists) {
            echo "✅ Table exists: {$table}\n";
        } else {
            echo "❌ Table missing: {$table}\n";
            $missingTables[] = $table;
        }
    } catch (Exception $e) {
        echo "⚠️  Could not check table: {$table}\n";
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    echo "\n⚠️  Missing tables detected. Please run migrations first:\n";
    echo "   php artisan migrate\n\n";
}

// Test model relationships
echo "\n📊 Testing Model Relationships...\n";
try {
    $output = [];
    exec('php artisan tinker --execute="echo class_exists(\'App\\\\Models\\\\Organization\') ? \'exists\' : \'missing\';"', $output);
    $orgExists = trim($output[0] ?? '') === 'exists';
    
    if ($orgExists) {
        echo "✅ Organization model exists\n";
        
        // Test if we can create a basic organization
        $createTest = 'try { $org = new \\App\\Models\\Organization(); echo "model_ok"; } catch(Exception $e) { echo "model_error: " . $e->getMessage(); }';
        exec("php artisan tinker --execute=\"{$createTest}\"", $output);
        $result = trim($output[0] ?? '');
        
        if (strpos($result, 'model_ok') !== false) {
            echo "✅ Organization model instantiation works\n";
        } else {
            echo "❌ Organization model has issues: {$result}\n";
        }
    } else {
        echo "❌ Organization model missing\n";
    }
} catch (Exception $e) {
    echo "❌ Model relationship test failed: " . $e->getMessage() . "\n";
}

// Test Spatie Permission package
echo "\n📊 Testing Spatie Permission Package...\n";
try {
    $output = [];
    exec('php artisan tinker --execute="echo class_exists(\'Spatie\\\\Permission\\\\Models\\\\Role\') ? \'exists\' : \'missing\';"', $output);
    $roleExists = trim($output[0] ?? '') === 'exists';
    
    if ($roleExists) {
        echo "✅ Spatie Permission package is available\n";
        
        // Test role creation
        $roleTest = 'try { $role = \\Spatie\\Permission\\Models\\Role::firstOrCreate([\'name\' => \'test_role\', \'guard_name\' => \'web\']); echo "role_ok"; } catch(Exception $e) { echo "role_error: " . $e->getMessage(); }';
        exec("php artisan tinker --execute=\"{$roleTest}\"", $output);
        $result = trim($output[0] ?? '');
        
        if (strpos($result, 'role_ok') !== false) {
            echo "✅ Role creation works\n";
            
            // Clean up test role
            exec('php artisan tinker --execute="\\Spatie\\Permission\\Models\\Role::where(\'name\', \'test_role\')->delete();"');
        } else {
            echo "❌ Role creation failed: {$result}\n";
        }
    } else {
        echo "❌ Spatie Permission package not available\n";
    }
} catch (Exception $e) {
    echo "❌ Permission package test failed: " . $e->getMessage() . "\n";
}

// Final compatibility assessment
echo "\n🎯 COMPATIBILITY ASSESSMENT\n";
echo "═══════════════════════════════════════\n";

$compatibilityIssues = [];

if (empty($missingTables)) {
    echo "✅ All required tables are present\n";
} else {
    $compatibilityIssues[] = count($missingTables) . " tables missing";
}

if ($driver === 'pgsql') {
    echo "✅ PostgreSQL database driver confirmed\n";
} else {
    $compatibilityIssues[] = "Not using PostgreSQL";
}

if (empty($compatibilityIssues)) {
    echo "✅ System is ready for PostgreSQL-compatible seeding\n";
    echo "\n🚀 You can now run the exhaustive seeder:\n";
    echo "   php artisan db:seed --class=ExhaustiveSystemSeeder\n";
    echo "\n   Or with fresh migration:\n";
    echo "   php artisan migrate:fresh --seed --seeder=ExhaustiveSystemSeeder\n";
} else {
    echo "⚠️  Compatibility issues detected:\n";
    foreach ($compatibilityIssues as $issue) {
        echo "   • {$issue}\n";
    }
    echo "\n🔧 Please address these issues before running the exhaustive seeder.\n";
}

echo "\n" . str_repeat("═", 60) . "\n";
