<?php
/**
 * OrderController Fix Analysis and Implementation
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ItemMaster;
use App\Models\MenuItem;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

echo "🔧 ANALYZING ORDER CONTROLLER ISSUES\n";
echo "===================================\n\n";

// Check the relationship between ItemMaster and MenuItem
echo "1. Checking ItemMaster to MenuItem relationship:\n";
$itemMasterCount = ItemMaster::where('is_menu_item', true)->count();
$menuItemCount = MenuItem::count();
echo "   - ItemMaster with is_menu_item=true: {$itemMasterCount}\n";
echo "   - MenuItem records: {$menuItemCount}\n\n";

// Check if MenuItem has item_master_id
$sampleMenuItem = MenuItem::with('itemMaster')->first();
if ($sampleMenuItem) {
    echo "2. Sample MenuItem structure:\n";
    echo "   - ID: {$sampleMenuItem->id}\n";
    echo "   - Name: {$sampleMenuItem->name}\n";
    echo "   - Price: {$sampleMenuItem->price}\n";
    echo "   - item_master_id: " . ($sampleMenuItem->item_master_id ?? 'null') . "\n";
    echo "   - Has ItemMaster relation: " . ($sampleMenuItem->itemMaster ? 'Yes' : 'No') . "\n\n";
}

// Check what the actual issue is with the OrderController structure
echo "3. OrderController Issues to Fix:\n";
echo "   ❌ Uses ItemMaster::find(\$item['item_id']) instead of MenuItem::find()\n";
echo "   ❌ Missing order_date in Order::create\n";
echo "   ❌ Missing item_name in OrderItem::create\n";
echo "   ❌ Uses different field names (total_price vs subtotal)\n";
echo "   ❌ References inventory_item_id instead of menu_item_id\n\n";

echo "4. Required Fixes:\n";
echo "   ✅ Change ItemMaster::find() to MenuItem::find()\n";
echo "   ✅ Add order_date to all Order::create calls\n";
echo "   ✅ Add item_name to all OrderItem::create calls\n";
echo "   ✅ Use menu_item_id consistently\n";
echo "   ✅ Use subtotal field name consistently\n";
echo "   ✅ Update stock checking to use MenuItem relationships\n\n";

echo "5. Stock Calculation Strategy:\n";
echo "   - For MenuItem with item_master_id: Use ItemTransaction::stockOnHand(\$menuItem->item_master_id)\n";
echo "   - For MenuItem without item_master_id (KOT items): Always available\n\n";

echo "🚀 Ready to implement fixes!\n";
