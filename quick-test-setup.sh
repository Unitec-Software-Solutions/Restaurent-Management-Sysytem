echo "🚀 Starting Restaurant Management System Comprehensive Test Setup..."
echo ""

echo "🧹 Step 1: Refreshing database with optimized data..."
php artisan migrate:fresh --force
php artisan db:seed --class=ComprehensiveTestSeeder

echo ""
echo "📊 Step 2: Validating seeded data..."

echo "Organizations created:"
php artisan tinker --execute="echo App\Models\Organization::count();"

echo "Subscription plans:"
php artisan tinker --execute="echo App\Models\SubscriptionPlan::count();"

echo "Active subscriptions:"
php artisan tinker --execute="echo App\Models\Subscription::where('is_active', true)->count();"

echo "Branches (should be 6 - 2 per org):"
php artisan tinker --execute="echo App\Models\Branch::count();"

echo "Users created:"
php artisan tinker --execute="echo App\Models\User::count();"

echo ""
echo "🧪 Step 3: Testing key workflows..."

echo "Testing low stock alerts:"
php artisan tinker --execute="
\$lowStock = App\Models\InventoryItem::join('item_masters', 'inventory_items.item_master_id', '=', 'item_masters.id')
    ->whereRaw('inventory_items.current_stock <= item_masters.reorder_level')
    ->count();
echo 'Low stock items: ' . \$lowStock;
"

echo "Testing subscription limits:"
php artisan tinker --execute="
App\Models\Organization::with('currentSubscription.plan')->get()->each(function(\$org) {
    \$plan = \$org->currentSubscription?->plan;
    if(\$plan) {
        echo \$org->name . ' (' . \$plan->name . '): ' . \$org->branches->count() . '/' . \$plan->max_branches . ' branches' . PHP_EOL;
    }
});
"

echo ""
echo "✅ SETUP COMPLETE!"
echo ""
echo "🔐 Login Credentials:"
echo "  Super Admin: superadmin@rms.com / password123"
echo "  Basic Org: admin3@hillkitchen.com / password123"
echo "  Pro Org: admin2@oceanview.com / password123"
echo "  Enterprise Org: admin1@spicegarden.com / password123"
echo ""
echo "🏢 Test Organizations:"
echo "  1. Hill Country Kitchen (Basic Plan) - Limited features"
echo "  2. Ocean View Cafe (Pro Plan) - Most features"
echo "  3. Spice Garden Restaurant (Enterprise Plan) - All features"
echo ""
echo "🎯 Next: Start server with 'php artisan serve'"
