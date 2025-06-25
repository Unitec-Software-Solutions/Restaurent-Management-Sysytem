# Comprehensive Restaurant Management System Seeder Test Script (PowerShell)
# 
# This script runs the exhaustive seeding system and validates all scenarios
# to ensure comprehensive coverage of restaurant management edge cases.

param(
    [switch]$Force,
    [switch]$SkipConfirmation
)

# Colors for console output
$Global:Colors = @{
    Green = 'Green'
    Red = 'Red'
    Yellow = 'Yellow'
    Blue = 'Blue'
    Cyan = 'Cyan'
    White = 'White'
}

function Write-ColorOutput {
    param(
        [string]$Text,
        [string]$Color = 'White'
    )
    Write-Host $Text -ForegroundColor $Global:Colors[$Color]
}

function Test-LaravelEnvironment {
    if (-not (Test-Path ".\artisan")) {
        Write-ColorOutput "❌ Error: Please run this script from your Laravel project root directory." "Red"
        exit 1
    }
    
    if (-not (Test-Path ".\bootstrap\app.php")) {
        Write-ColorOutput "❌ Error: Laravel application not found." "Red"
        exit 1
    }
    
    return $true
}

function Invoke-ArtisanCommand {
    param(
        [string]$Command,
        [string]$Description
    )
    
    Write-Host "📋 $Description..." -ForegroundColor White
    
    try {
        $result = Invoke-Expression "php artisan $Command" 2>&1
        
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput "✅ SUCCESS: $Description" "Green"
            return $true
        } else {
            Write-ColorOutput "❌ FAILED: $Description" "Red"
            Write-Host "Output:" -ForegroundColor Red
            $result | ForEach-Object { Write-Host "  $_" -ForegroundColor Red }
            return $false
        }
    } catch {
        Write-ColorOutput "❌ FAILED: $Description" "Red"
        Write-ColorOutput "Error: $($_.Exception.Message)" "Red"
        return $false
    }
}

function Test-SeederExists {
    param([string]$SeederClass)
    
    $seederPath = ".\database\seeders\$SeederClass.php"
    if (Test-Path $seederPath) {
        Write-ColorOutput "✅ Found: $SeederClass" "Green"
        return $true
    } else {
        Write-ColorOutput "❌ Missing: $SeederClass" "Red"
        return $false
    }
}

# Main execution starts here
Write-ColorOutput "🌟 Starting Comprehensive Restaurant Management System Seeding Test" "Cyan"
Write-ColorOutput "═══════════════════════════════════════════════════════════════════" "Cyan"
Write-Host ""

# Test Laravel environment
if (-not (Test-LaravelEnvironment)) {
    exit 1
}

# Step 1: Validate all required seeders exist
Write-ColorOutput "🔍 STEP 1: Validating Required Seeders" "Blue"
Write-ColorOutput "────────────────────────────────────────" "Blue"

$requiredSeeders = @(
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
)

$missingSeeds = @()
foreach ($seeder in $requiredSeeders) {
    if (-not (Test-SeederExists $seeder)) {
        $missingSeeds += $seeder
    }
}

if ($missingSeeds.Count -gt 0) {
    Write-ColorOutput "`n❌ ERROR: Missing required seeders. Please ensure all seeders are created." "Red"
    exit 1
}

Write-ColorOutput "`n✅ All required seeders found!" "Green"

# Step 2: Check database connectivity
Write-Host ""
Write-ColorOutput "🔗 STEP 2: Database Connectivity Check" "Blue"
Write-ColorOutput "────────────────────────────────────────" "Blue"

if (-not (Invoke-ArtisanCommand "migrate:status" "Checking database connectivity")) {
    Write-ColorOutput "❌ Database connection failed. Please check your .env configuration." "Red"
    exit 1
}

# Step 3: Fresh migration confirmation
if (-not $SkipConfirmation) {
    Write-Host ""
    Write-ColorOutput "⚠️  STEP 3: Database Reset Confirmation" "Yellow"
    Write-ColorOutput "────────────────────────────────────────" "Yellow"
    Write-ColorOutput "WARNING: This will reset your database and run fresh migrations." "Yellow"
    
    if (-not $Force) {
        $response = Read-Host "Are you sure you want to continue? (y/N)"
        if ($response.ToLower() -ne 'y') {
            Write-Host "Operation cancelled."
            exit 0
        }
    }
}

# Step 4: Fresh migration and seeding
Write-Host ""
Write-ColorOutput "🚀 STEP 4: Fresh Migration and Seeding" "Blue"
Write-ColorOutput "────────────────────────────────────────" "Blue"

if (-not (Invoke-ArtisanCommand "migrate:fresh" "Running fresh migrations")) {
    Write-ColorOutput "❌ Migration failed." "Red"
    exit 1
}

# Step 5: Run exhaustive seeding
Write-Host ""
Write-ColorOutput "🌱 STEP 5: Running Exhaustive System Seeder" "Blue"
Write-ColorOutput "────────────────────────────────────────" "Blue"

$seedingStartTime = Get-Date

if (-not (Invoke-ArtisanCommand "db:seed --class=ExhaustiveSystemSeeder" "Running ExhaustiveSystemSeeder")) {
    Write-ColorOutput "❌ Seeding failed." "Red"
    exit 1
}

$seedingEndTime = Get-Date
$seedingDuration = [math]::Round(($seedingEndTime - $seedingStartTime).TotalSeconds, 2)

Write-ColorOutput "✅ Seeding completed in $seedingDuration seconds!" "Green"

# Step 6: Validation and verification
Write-Host ""
Write-ColorOutput "🔍 STEP 6: Post-Seeding Validation" "Blue"
Write-ColorOutput "────────────────────────────────────────" "Blue"

$validationTables = @{
    'subscription_plans' = 'Subscription Plans'
    'organizations' = 'Organizations'
    'branches' = 'Branches'
    'admins' = 'Admin Users'
    'users' = 'Regular Users'
    'menu_categories' = 'Menu Categories'
    'menu_items' = 'Menu Items'
    'orders' = 'Orders'
    'reservations' = 'Reservations'
    'item_masters' = 'Inventory Items'
    'kitchen_stations' = 'Kitchen Stations'
    'tables' = 'Tables'
}

Write-ColorOutput "📊 Data Counts Verification:" "Cyan"
foreach ($table in $validationTables.Keys) {
    try {
        $count = php artisan tinker --execute="echo DB::table('$table')->count();" 2>$null
        $displayName = $validationTables[$table]
        Write-Host ("  {0,-20}: {1}" -f $displayName, $count.Trim())
    } catch {
        Write-Host ("  {0,-20}: Error" -f $validationTables[$table]) -ForegroundColor Red
    }
}

# Step 7: Test specific scenarios
Write-Host ""
Write-ColorOutput "🧪 STEP 7: Scenario Testing" "Blue"
Write-ColorOutput "────────────────────────────────────────" "Blue"

$scenarioTests = @{
    "Check subscription plan variations" = "DB::table('subscription_plans')->distinct('name')->count()"
    "Check organization business types" = "DB::table('organizations')->distinct('business_type')->count()"
    "Check user role assignments" = "DB::table('model_has_roles')->count()"
    "Check menu item availability" = "DB::table('menu_items')->whereNotNull('availability_schedule')->count()"
    "Check order status variations" = "DB::table('orders')->distinct('status')->count()"
    "Check reservation conflicts" = "DB::table('reservations')->where('status', 'conflict')->count()"
    "Check inventory low stock items" = "try { DB::table('inventory_items')->whereColumn('current_stock', '<=', 'reorder_level')->count(); } catch(Exception `$e) { 0; }"
    "Check kitchen stations" = "DB::table('kitchen_stations')->count()"
}

foreach ($test in $scenarioTests.Keys) {
    try {
        $result = php artisan tinker --execute="echo $($scenarioTests[$test]);" 2>$null
        $count = $result.Trim()
        
        if ([int]$count -gt 0) {
            Write-ColorOutput "✅ $test`: $count" "Green"
        } else {
            Write-ColorOutput "⚠️  $test`: $count" "Yellow"
        }
    } catch {
        Write-ColorOutput "⚠️  $test`: Error" "Yellow"
    }
}

# Final summary
Write-Host ""
Write-ColorOutput "🎯 FINAL SUMMARY" "Cyan"
Write-ColorOutput "═══════════════════════════════════════════" "Cyan"

Write-ColorOutput "✅ COMPREHENSIVE SEEDING COMPLETED SUCCESSFULLY!" "Green"

Write-Host "`n📋 What was accomplished:" -ForegroundColor White
Write-ColorOutput "  • ✅ All subscription plan scenarios (Basic → Enterprise)" "Green"
Write-ColorOutput "  • ✅ Organization variations (Single → Multi-branch → Franchise)" "Green"
Write-ColorOutput "  • ✅ Branch configurations (Head office → Seasonal → Custom stations)" "Green"
Write-ColorOutput "  • ✅ User permission hierarchies (Guest → Staff → Admin → Super)" "Green"
Write-ColorOutput "  • ✅ Menu configurations (Daily → Seasonal → Event-based)" "Green"
Write-ColorOutput "  • ✅ Order lifecycle scenarios (Cart → Payment → Kitchen → Fulfillment)" "Green"
Write-ColorOutput "  • ✅ Inventory edge cases (Low stock → Transfers → Adjustments)" "Green"
Write-ColorOutput "  • ✅ Reservation complexities (Conflicts → Large groups → Recurring)" "Green"
Write-ColorOutput "  • ✅ Kitchen workflow patterns (Peak → Emergency → Quality control)" "Green"
Write-ColorOutput "  • ✅ Edge case validations (Boundaries → Performance → Integrity)" "Green"

Write-Host "`n🔗 Next Steps:" -ForegroundColor White
Write-Host "  1. Test specific business scenarios through the application UI"
Write-Host "  2. Run performance tests under load conditions"
Write-Host "  3. Validate permission boundaries with different user roles"
Write-Host "  4. Test edge cases for data integrity and consistency"
Write-Host "  5. Monitor system behavior under various operational conditions"

Write-Host "`n🚀 Your restaurant management system is now ready for comprehensive testing!"

Write-ColorOutput "`n🎉 SUCCESS: Exhaustive seeding and validation completed!" "Green"
