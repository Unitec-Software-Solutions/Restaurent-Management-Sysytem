<?php
/**
 * Add Stock for Testing Order Placement
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\Branch;

echo "📦 ADDING STOCK FOR ORDER TESTING\n";
echo "=================================\n\n";

$branches = Branch::where('is_active', true)->get();
$menuItems = ItemMaster::where('is_menu_item', true)->where('is_active', true)->get();

echo "Adding stock to {$menuItems->count()} menu items across {$branches->count()} branches...\n\n";

foreach ($branches as $branch) {
    echo "🏢 Branch: {$branch->name}\n";
    
    foreach ($menuItems as $item) {
        // Add 100 units of stock for each item in each branch
        ItemTransaction::create([
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'transaction_type' => 'opening_stock',
            'quantity' => 100,
            'cost_price' => $item->buying_price ?? ($item->selling_price * 0.7),
            'unit_price' => $item->selling_price,
            'source_type' => 'System',
            'created_by_user_id' => 1,
            'notes' => 'Initial stock for order testing',
            'is_active' => true,
        ]);
        
        echo "   ✅ Added 100 units of {$item->name}\n";
    }
    echo "\n";
}

echo "✅ Stock addition completed!\n";
echo "\nNow checking stock levels...\n";

foreach ($branches->take(2) as $branch) {
    echo "\n🏢 {$branch->name} Stock Levels:\n";
    foreach ($menuItems->take(5) as $item) {
        $stock = ItemTransaction::stockOnHand($item->id, $branch->id);
        echo "   • {$item->name}: {$stock} units\n";
    }
}

echo "\n🎉 Orders should now be placeable!\n";
