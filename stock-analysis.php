<?php
/**
 * Stock Analysis Script for Order Issues
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\Branch;

echo "📦 STOCK ANALYSIS FOR ORDER ISSUES\n";
echo "==================================\n\n";

$branches = Branch::where('is_active', true)->get();
$menuItems = ItemMaster::where('is_menu_item', true)->where('is_active', true)->get();

echo "Found {$branches->count()} active branches and {$menuItems->count()} menu items\n\n";

foreach ($branches as $branch) {
    echo "🏢 Branch: {$branch->name} (ID: {$branch->id})\n";
    echo "   Organization ID: {$branch->organization_id}\n";
    
    $lowStockItems = 0;
    $outOfStockItems = 0;
    $availableItems = 0;
    
    foreach ($menuItems as $item) {
        $stock = ItemTransaction::stockOnHand($item->id, $branch->id);
        
        if ($stock <= 0) {
            $outOfStockItems++;
        } elseif ($stock <= ($item->reorder_level ?? 10)) {
            $lowStockItems++;
        } else {
            $availableItems++;
        }
        
        // Show first few items as examples
        if ($menuItems->search($item) < 5) {
            echo "   • {$item->name}: Stock = $stock, Price = LKR {$item->selling_price}\n";
        }
    }
    
    echo "   📊 Stock Summary:\n";
    echo "      Available: $availableItems items\n";
    echo "      Low Stock: $lowStockItems items\n";
    echo "      Out of Stock: $outOfStockItems items\n";
    
    if ($outOfStockItems > 0) {
        echo "   ⚠️  This branch has $outOfStockItems items out of stock!\n";
    }
    
    echo "\n";
}

// Check if items have valid buying/selling prices
echo "💰 PRICE VALIDATION:\n";
$itemsWithoutSellingPrice = ItemMaster::where('is_menu_item', true)
    ->where('is_active', true)
    ->where(function($q) {
        $q->whereNull('selling_price')->orWhere('selling_price', '<=', 0);
    })
    ->get();

if ($itemsWithoutSellingPrice->count() > 0) {
    echo "❌ Items without valid selling prices:\n";
    foreach ($itemsWithoutSellingPrice as $item) {
        echo "   • {$item->name} (Price: {$item->selling_price})\n";
    }
} else {
    echo "✅ All menu items have valid selling prices\n";
}

// Check organization assignments
echo "\n🏢 ORGANIZATION ASSIGNMENTS:\n";
$branchesWithoutOrg = Branch::whereNull('organization_id')->get();
$itemsWithoutOrg = ItemMaster::where('is_menu_item', true)
    ->where('is_active', true)
    ->whereNull('organization_id')
    ->get();

if ($branchesWithoutOrg->count() > 0) {
    echo "❌ Branches without organization:\n";
    foreach ($branchesWithoutOrg as $branch) {
        echo "   • {$branch->name}\n";
    }
} else {
    echo "✅ All branches have organization assignments\n";
}

if ($itemsWithoutOrg->count() > 0) {
    echo "❌ Menu items without organization:\n";
    foreach ($itemsWithoutOrg as $item) {
        echo "   • {$item->name}\n";
    }
} else {
    echo "✅ All menu items have organization assignments\n";
}

echo "\n🔧 RECOMMENDATIONS TO FIX ORDER ISSUES:\n";
echo "1. Add organization_id to Order model creation (CRITICAL)\n";
echo "2. Add stock to menu items using ItemTransaction\n";
echo "3. Ensure all items have valid selling prices\n";
echo "4. Test order creation with proper organization context\n";
