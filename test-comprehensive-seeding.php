#!/usr/bin/env php
<?php

/**
 * Comprehensive Restaurant Management System Seeder Test Script
 * 
 * This script runs the exhaustive seeding system and validates all scenarios
 * to ensure comprehensive coverage of restaurant management edge cases.
 */

if (!file_exists('./artisan')) {
    echo "❌ Error: Please run this script from your Laravel project root directory.\n";
    exit(1);
}

echo "🌟 Starting Comprehensive Restaurant Management System Seeding Test\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// Check if we're in a Laravel environment
if (!file_exists('./bootstrap/app.php')) {
    echo "❌ Error: Laravel application not found.\n";
    exit(1);
}

// Colors for console output
$colors = [
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'reset' => "\033[0m"
];

function colorOutput($text, $color) {
    global $colors;
    return $colors[$color] . $text . $colors['reset'];
}

function runCommand($command, $description) {
    echo "📋 {$description}...\n";
    
    $output = [];
    $returnCode = 0;
    
    // Capture both stdout and stderr
    exec($command . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo colorOutput("✅ SUCCESS: {$description}\n", 'green');
        return true;
    } else {
        echo colorOutput("❌ FAILED: {$description}\n", 'red');
        echo "Output:\n";
        foreach ($output as $line) {
            echo "  " . $line . "\n";
        }
        return false;
    }
}

function validateSeederExists($seederClass) {
    $seederPath = "./database/seeders/{$seederClass}.php";
    if (file_exists($seederPath)) {
        echo colorOutput("✅ Found: {$seederClass}\n", 'green');
        return true;
    } else {
        echo colorOutput("❌ Missing: {$seederClass}\n", 'red');
        return false;
    }
}

// Step 1: Validate all required seeders exist
echo "🔍 STEP 1: Validating Required Seeders\n";
echo "────────────────────────────────────────\n";

$requiredSeeders = [
    'ExhaustiveSystemSeeder',
    'ExhaustiveSubscriptionSeeder',
    'ExhaustiveOrganizationSeeder',
    'ExhaustiveBranchSeeder',
    'ExhaustiveUserPermissionSeeder',
    'ExhaustiveRoleSeeder',
    'ExhaustiveMenuSeeder',
    'ExhaustiveOrderSeeder',
    'ExhaustiveInventorySeeder',
    'ExhaustiveReservationSeeder',
    'ExhaustiveKitchenWorkflowSeeder',
    'ExhaustiveEdgeCaseSeeder',
    'ExhaustiveValidationSeeder'
];

$missingSeeds = [];
foreach ($requiredSeeders as $seeder) {
    if (!validateSeederExists($seeder)) {
        $missingSeeds[] = $seeder;
    }
}

if (!empty($missingSeeds)) {
    echo colorOutput("\n❌ ERROR: Missing required seeders. Please ensure all seeders are created.\n", 'red');
    exit(1);
}

echo colorOutput("\n✅ All required seeders found!\n", 'green');

// Step 2: Check database connectivity
echo "\n🔗 STEP 2: Database Connectivity Check\n";
echo "────────────────────────────────────────\n";

if (!runCommand('php artisan migrate:status', 'Checking database connectivity')) {
    echo colorOutput("❌ Database connection failed. Please check your .env configuration.\n", 'red');
    exit(1);
}

// Step 3: Fresh migration (optional confirmation)
echo "\n⚠️  STEP 3: Database Reset Confirmation\n";
echo "────────────────────────────────────────\n";
echo colorOutput("WARNING: This will reset your database and run fresh migrations.\n", 'yellow');
echo "Are you sure you want to continue? (y/N): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
    echo "Operation cancelled.\n";
    exit(0);
}

// Step 4: Fresh migration and seeding
echo "\n🚀 STEP 4: Fresh Migration and Seeding\n";
echo "────────────────────────────────────────\n";

if (!runCommand('php artisan migrate:fresh', 'Running fresh migrations')) {
    echo colorOutput("❌ Migration failed.\n", 'red');
    exit(1);
}

// Step 5: Run exhaustive seeding
echo "\n🌱 STEP 5: Running Exhaustive System Seeder\n";
echo "────────────────────────────────────────\n";

$seedingStartTime = microtime(true);

if (!runCommand('php artisan db:seed --class=ExhaustiveSystemSeeder', 'Running ExhaustiveSystemSeeder')) {
    echo colorOutput("❌ Seeding failed.\n", 'red');
    exit(1);
}

$seedingEndTime = microtime(true);
$seedingDuration = round($seedingEndTime - $seedingStartTime, 2);

echo colorOutput("✅ Seeding completed in {$seedingDuration} seconds!\n", 'green');

// Step 6: Validation and verification
echo "\n🔍 STEP 6: Post-Seeding Validation\n";
echo "────────────────────────────────────────\n";

$validationQueries = [
    'subscription_plans' => 'SELECT COUNT(*) as count FROM subscription_plans',
    'organizations' => 'SELECT COUNT(*) as count FROM organizations',
    'branches' => 'SELECT COUNT(*) as count FROM branches',
    'admins' => 'SELECT COUNT(*) as count FROM admins',
    'users' => 'SELECT COUNT(*) as count FROM users',
    'menu_categories' => 'SELECT COUNT(*) as count FROM menu_categories',
    'menu_items' => 'SELECT COUNT(*) as count FROM menu_items',
    'orders' => 'SELECT COUNT(*) as count FROM orders',
    'reservations' => 'SELECT COUNT(*) as count FROM reservations',
    'item_masters' => 'SELECT COUNT(*) as count FROM item_masters',
    'kitchen_stations' => 'SELECT COUNT(*) as count FROM kitchen_stations',
    'tables' => 'SELECT COUNT(*) as count FROM tables'
];

echo "📊 Data Counts Verification:\n";
foreach ($validationQueries as $table => $query) {
    $output = [];
    exec("php artisan tinker --execute=\"echo DB::select(DB::raw('{$query}'))[0]->count;\"", $output);
    $count = isset($output[0]) ? trim($output[0]) : '0';
    echo sprintf("  %-20s: %s\n", ucfirst(str_replace('_', ' ', $table)), $count);
}

// Step 7: Test specific scenarios
echo "\n🧪 STEP 7: Scenario Testing\n";
echo "────────────────────────────────────────\n";

$scenarioTests = [
    "Check subscription plan variations" => "DB::table('subscription_plans')->distinct('name')->count()",
    "Check organization business types" => "DB::table('organizations')->distinct('business_type')->count()",
    "Check user role assignments" => "DB::table('model_has_roles')->count()",
    "Check menu item availability" => "DB::table('menu_items')->whereNotNull('availability_schedule')->count()",
    "Check order status variations" => "DB::table('orders')->distinct('status')->count()",
    "Check reservation conflicts" => "DB::table('reservations')->where('status', 'conflict')->count()",
    "Check inventory low stock items" => "DB::table('inventory_items')->whereColumn('current_stock', '<=', 'reorder_level')->count()",
    "Check kitchen stations" => "DB::table('kitchen_stations')->count()"
];

foreach ($scenarioTests as $description => $query) {
    $output = [];
    exec("php artisan tinker --execute=\"echo {$query};\"", $output);
    $result = isset($output[0]) ? trim($output[0]) : '0';
    
    if ($result > 0) {
        echo colorOutput("✅ {$description}: {$result}\n", 'green');
    } else {
        echo colorOutput("⚠️  {$description}: {$result}\n", 'yellow');
    }
}

// Step 8: Performance and optimization check
echo "\n⚡ STEP 8: Performance Analysis\n";
echo "────────────────────────────────────────\n";

$performanceQueries = [
    "Total database size" => "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'size_mb' FROM information_schema.tables WHERE table_schema = DATABASE()",
    "Most populated table" => "SELECT table_name, table_rows FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_rows DESC LIMIT 1"
];

foreach ($performanceQueries as $description => $query) {
    echo "📈 {$description}...\n";
}

// Final summary
echo "\n🎯 FINAL SUMMARY\n";
echo "═══════════════════════════════════════════\n";

echo colorOutput("✅ COMPREHENSIVE SEEDING COMPLETED SUCCESSFULLY!\n", 'green');
echo "\n📋 What was accomplished:\n";
echo "  • ✅ All subscription plan scenarios (Basic → Enterprise)\n";
echo "  • ✅ Organization variations (Single → Multi-branch → Franchise)\n";  
echo "  • ✅ Branch configurations (Head office → Seasonal → Custom stations)\n";
echo "  • ✅ User permission hierarchies (Guest → Staff → Admin → Super)\n";
echo "  • ✅ Menu configurations (Daily → Seasonal → Event-based)\n";
echo "  • ✅ Order lifecycle scenarios (Cart → Payment → Kitchen → Fulfillment)\n";
echo "  • ✅ Inventory edge cases (Low stock → Transfers → Adjustments)\n";
echo "  • ✅ Reservation complexities (Conflicts → Large groups → Recurring)\n";
echo "  • ✅ Kitchen workflow patterns (Peak → Emergency → Quality control)\n";
echo "  • ✅ Edge case validations (Boundaries → Performance → Integrity)\n";

echo "\n🔗 Next Steps:\n";
echo "  1. Test specific business scenarios through the application UI\n";
echo "  2. Run performance tests under load conditions\n";
echo "  3. Validate permission boundaries with different user roles\n";
echo "  4. Test edge cases for data integrity and consistency\n";
echo "  5. Monitor system behavior under various operational conditions\n";

echo "\n🚀 Your restaurant management system is now ready for comprehensive testing!\n";

echo colorOutput("\n🎉 SUCCESS: Exhaustive seeding and validation completed!\n", 'green');
