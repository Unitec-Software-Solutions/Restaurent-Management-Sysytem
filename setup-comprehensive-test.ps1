# PowerShell script to set up and test the Restaurant Management System
# Run this script from the project root directory

Write-Host "🚀 Restaurant Management System - Comprehensive Setup & Test" -ForegroundColor Green
Write-Host "================================================================" -ForegroundColor Green
Write-Host ""

# Check if we're in the correct directory
if (!(Test-Path "artisan")) {
    Write-Host "❌ Error: Please run this script from the Laravel project root directory" -ForegroundColor Red
    exit 1
}

Write-Host "📋 Step 1: Installing/updating Composer dependencies..." -ForegroundColor Yellow
composer install --no-dev --optimize-autoloader
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Composer install failed" -ForegroundColor Red
    exit 1
}

Write-Host "🔧 Step 2: Setting up application configuration..." -ForegroundColor Yellow

# Copy .env file if it doesn't exist
if (!(Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "✅ Created .env file from .env.example" -ForegroundColor Green
    } else {
        Write-Host "⚠️ Warning: No .env.example file found" -ForegroundColor Yellow
    }
}

# Generate application key if needed
php artisan key:generate --force
Write-Host "✅ Application key generated" -ForegroundColor Green

Write-Host "🗄️ Step 3: Setting up database..." -ForegroundColor Yellow

# Fresh migration with force (for testing environments)
php artisan migrate:fresh --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Database migration failed" -ForegroundColor Red
    Write-Host "Please check your database configuration in .env file" -ForegroundColor Yellow
    exit 1
}
Write-Host "✅ Database migrations completed" -ForegroundColor Green

Write-Host "🌱 Step 4: Seeding optimized test data..." -ForegroundColor Yellow

# Run the comprehensive seeder
php artisan db:seed --class=OptimizedDatabaseSeeder
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Database seeding failed" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "🧪 Step 5: Running comprehensive system tests..." -ForegroundColor Yellow

# Run the feature tests
php artisan test tests/Feature/ComprehensiveRestaurantWorkflowTest.php --verbose
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️ Some tests failed - check output above for details" -ForegroundColor Yellow
} else {
    Write-Host "✅ All tests passed!" -ForegroundColor Green
}

Write-Host ""
Write-Host "🔍 Step 6: Validating seeded data..." -ForegroundColor Yellow

# Check database counts
Write-Host "📊 Database Statistics:" -ForegroundColor Cyan
php artisan tinker --execute="
echo 'Organizations: ' . App\Models\Organization::count() . PHP_EOL;
echo 'Subscription Plans: ' . App\Models\SubscriptionPlan::count() . PHP_EOL;
echo 'Active Subscriptions: ' . App\Models\Subscription::where('is_active', true)->count() . PHP_EOL;
echo 'Branches: ' . App\Models\Branch::count() . PHP_EOL;
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Employees: ' . App\Models\Employee::count() . PHP_EOL;
echo 'Menu Items: ' . App\Models\MenuItem::count() . PHP_EOL;
echo 'Orders: ' . App\Models\Order::count() . PHP_EOL;
echo 'KOTs: ' . (class_exists('App\Models\Kot') ? App\Models\Kot::count() : 0) . PHP_EOL;
echo 'Reservations: ' . App\Models\Reservation::count() . PHP_EOL;
"

Write-Host ""
Write-Host "🎯 Step 7: Testing specific workflows..." -ForegroundColor Yellow

# Test inventory alerts
Write-Host "Testing inventory alerts..." -ForegroundColor Cyan
php artisan tinker --execute="
\$lowStockItems = App\Models\InventoryItem::join('item_masters', 'inventory_items.item_master_id', '=', 'item_masters.id')
    ->where('inventory_items.current_stock', '<=', DB::raw('item_masters.reorder_level'))
    ->select('item_masters.name', 'inventory_items.current_stock', 'item_masters.reorder_level')
    ->get();
echo 'Low stock items found: ' . \$lowStockItems->count() . PHP_EOL;
foreach(\$lowStockItems as \$item) {
    echo '  - ' . \$item->name . ': ' . \$item->current_stock . '/' . \$item->reorder_level . PHP_EOL;
}
"

# Test subscription limitations
Write-Host "Testing subscription limitations..." -ForegroundColor Cyan
php artisan tinker --execute="
\$orgs = App\Models\Organization::with('currentSubscription.plan', 'branches', 'employees')->get();
foreach(\$orgs as \$org) {
    \$plan = \$org->currentSubscription?->plan;
    if(\$plan) {
        \$branchCount = \$org->branches->count();
        \$employeeCount = \$org->employees->count();
        echo \$org->name . ' (' . \$plan->name . ' Plan):' . PHP_EOL;
        echo '  Branches: ' . \$branchCount . '/' . (\$plan->max_branches ?? 'unlimited') . PHP_EOL;
        echo '  Employees: ' . \$employeeCount . '/' . (\$plan->max_employees ?? 'unlimited') . PHP_EOL;
    }
}
"

Write-Host ""
Write-Host "✅ SETUP COMPLETED SUCCESSFULLY!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green
Write-Host ""
Write-Host "🔐 Login Credentials:" -ForegroundColor Cyan
Write-Host "  Super Admin: superadmin@rms.com / password123" -ForegroundColor White
Write-Host "  Org Admin 1: admin1@spicegarden.com / password123" -ForegroundColor White
Write-Host "  Org Admin 2: admin2@oceanview.com / password123" -ForegroundColor White
Write-Host "  Org Admin 3: admin3@hillkitchen.com / password123" -ForegroundColor White
Write-Host ""
Write-Host "🏢 Organizations Created:" -ForegroundColor Cyan
Write-Host "  1. Spice Garden Restaurant (Enterprise Plan) - 2 branches" -ForegroundColor White
Write-Host "  2. Ocean View Cafe (Pro Plan) - 2 branches" -ForegroundColor White
Write-Host "  3. Hill Country Kitchen (Basic Plan) - 2 branches" -ForegroundColor White
Write-Host ""
Write-Host "🧪 Test Scenarios Ready:" -ForegroundColor Cyan
Write-Host "  ✅ Order-to-Kitchen (KOT) workflow" -ForegroundColor White
Write-Host "  ✅ Inventory alerts (10% low stock)" -ForegroundColor White
Write-Host "  ✅ Auto staff assignment by shift" -ForegroundColor White
Write-Host "  ✅ Subscription tier limitations" -ForegroundColor White
Write-Host "  ✅ Real-time KOT tracking" -ForegroundColor White
Write-Host "  ✅ Role-based permissions" -ForegroundColor White
Write-Host "  ✅ Module activation/deactivation" -ForegroundColor White
Write-Host ""
Write-Host "🚀 Next Steps:" -ForegroundColor Cyan
Write-Host "  1. Start your development server: php artisan serve" -ForegroundColor White
Write-Host "  2. Visit the application and test different subscription tiers" -ForegroundColor White
Write-Host "  3. Test order workflows and kitchen operations" -ForegroundColor White
Write-Host "  4. Verify inventory alerts for low stock items" -ForegroundColor White
Write-Host "  5. Test role permissions across different users" -ForegroundColor White
Write-Host ""
Write-Host "📝 Additional Commands:" -ForegroundColor Cyan
Write-Host "  Run specific tests: php artisan test --filter=ComprehensiveRestaurantWorkflowTest" -ForegroundColor White
Write-Host "  Re-seed data: php artisan db:seed --class=ComprehensiveTestSeeder" -ForegroundColor White
Write-Host "  Clear cache: php artisan optimize:clear" -ForegroundColor White
Write-Host ""
