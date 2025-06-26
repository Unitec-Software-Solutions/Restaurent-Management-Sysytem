# Restaurant Management System - Comprehensive Setup & Testing Commands

# 1. Fresh database migration with optimized seeders
Write-Host "🚀 Starting comprehensive system setup..." -ForegroundColor Green

# Reset database
php artisan migrate:fresh

# Run comprehensive seeding
Write-Host "📊 Seeding optimized data..." -ForegroundColor Yellow
php artisan db:seed --class=ComprehensiveSystemSeeder

# 2. Run specific test validations
Write-Host "🧪 Running system validations..." -ForegroundColor Yellow

# Test subscription features
php artisan tinker --execute="
\$basicOrg = App\Models\Organization::whereHas('plan', function(\$q) { \$q->where('name', 'Basic'); })->first();
echo 'Basic org has split_billing: ' . (\$basicOrg->hasFeature('split_billing') ? 'YES' : 'NO') . PHP_EOL;

\$proOrg = App\Models\Organization::whereHas('plan', function(\$q) { \$q->where('name', 'Pro'); })->first();
echo 'Pro org has split_billing: ' . (\$proOrg->hasFeature('split_billing') ? 'YES' : 'NO') . PHP_EOL;
"

# Test branch limits
php artisan tinker --execute="
\$basicOrg = App\Models\Organization::whereHas('plan', function(\$q) { \$q->where('name', 'Basic'); })->first();
echo 'Basic org branches: ' . \$basicOrg->branches()->count() . '/' . (\$basicOrg->plan->max_branches ?? 'unlimited') . PHP_EOL;
echo 'Can add branches: ' . (\$basicOrg->canAddBranches() ? 'YES' : 'NO') . PHP_EOL;
"

# 3. Test inventory alerts
Write-Host "📦 Testing inventory alerts..." -ForegroundColor Yellow
php artisan tinker --execute="
\$lowStockItems = App\Models\InventoryItem::whereRaw('quantity <= (par_level * 0.1)')->with('itemMaster')->get();
echo 'Low stock items: ' . \$lowStockItems->count() . PHP_EOL;
\$lowStockItems->each(function(\$item) {
    echo '- ' . \$item->itemMaster->name . ': ' . \$item->quantity . '/' . \$item->par_level . PHP_EOL;
});
"

# 4. Test order workflow
Write-Host "🧾 Testing order workflow..." -ForegroundColor Yellow
php artisan tinker --execute="
\$orders = App\Models\Order::with('orderItems.menuItem')->get();
echo 'Test orders created: ' . \$orders->count() . PHP_EOL;
\$orders->each(function(\$order) {
    echo '- Order #' . \$order->order_number . ' (' . \$order->orderItems->count() . ' items, LKR ' . \$order->total_amount . ')' . PHP_EOL;
});
"

# 5. Test role permissions
Write-Host "👥 Testing role permissions..." -ForegroundColor Yellow
php artisan tinker --execute="
\$users = App\Models\User::with('roles')->take(5)->get();
echo 'Test users with roles: ' . \$users->count() . PHP_EOL;
\$users->each(function(\$user) {
    \$roles = \$user->roles->pluck('name')->implode(', ');
    echo '- ' . \$user->name . ': ' . (\$roles ?: 'No roles') . PHP_EOL;
});
"

# 6. Display system summary
Write-Host "📊 System Summary:" -ForegroundColor Green
php artisan tinker --execute="
echo '=== RESTAURANT MANAGEMENT SYSTEM SUMMARY ===' . PHP_EOL;
echo 'Organizations: ' . App\Models\Organization::count() . PHP_EOL;
echo 'Branches: ' . App\Models\Branch::count() . PHP_EOL;
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Roles: ' . App\Models\Role::count() . PHP_EOL;
echo 'Menu Items: ' . App\Models\MenuItem::count() . PHP_EOL;
echo 'Tables: ' . App\Models\Table::count() . PHP_EOL;
echo 'Reservations: ' . App\Models\Reservation::count() . PHP_EOL;
echo 'Inventory Items: ' . App\Models\InventoryItem::count() . PHP_EOL;
echo 'Active Subscriptions: ' . App\Models\Subscription::where('is_active', true)->count() . PHP_EOL;
echo '=============================================' . PHP_EOL;
"

# 7. Run feature tests
Write-Host "🧪 Running feature tests..." -ForegroundColor Yellow
# php artisan test tests/Feature/ModuleActivationTest.php --verbose

# 8. Start development server
Write-Host "🌐 Starting development server..." -ForegroundColor Green
Write-Host "Access the application at: http://localhost:8000" -ForegroundColor Cyan
Write-Host "Login credentials:" -ForegroundColor Cyan
Write-Host "  Super Admin: superadmin@rms.com / password" -ForegroundColor White
Write-Host "  Spice Garden: admin@spicegarden.lk / password123" -ForegroundColor White
Write-Host "  Ocean View: admin@oceanview.lk / password123" -ForegroundColor White
Write-Host "  Mountain Peak: admin@mountainpeak.lk / password123" -ForegroundColor White

# Optionally start the server (uncomment if needed)
# php artisan serve

Write-Host "✅ System setup and testing completed!" -ForegroundColor Green
