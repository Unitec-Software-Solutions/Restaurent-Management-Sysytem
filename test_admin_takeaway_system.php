<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Admin Takeaway Order System\n";
echo "===================================\n\n";

// Test 1: Check if admin exists and has branch
$admin = \App\Models\Admin::first();
if ($admin) {
    echo "✓ Admin found: {$admin->name}\n";
    if ($admin->branch) {
        echo "✓ Admin has branch: {$admin->branch->name}\n";
        echo "✓ Branch organization: {$admin->branch->organization->name}\n";
        echo "✓ Branch phone: " . ($admin->branch->phone ?? 'Not set') . "\n";
    } else {
        echo "⚠ Admin has no branch assigned\n";
    }
} else {
    echo "✗ No admin found\n";
}

echo "\n";

// Test 2: Check active menu items for branch
if ($admin && $admin->branch) {
    $branchId = $admin->branch->id;
    echo "Testing menu items for branch {$branchId}:\n";
    
    $activeMenu = \App\Models\Menu::getActiveMenuForBranch($branchId);
    if ($activeMenu) {
        echo "✓ Active menu found: {$activeMenu->name}\n";
        
        $menuItems = $activeMenu->menuItems()
            ->with(['menuCategory', 'itemMaster'])
            ->where('is_active', true)
            ->get();
            
        echo "✓ Menu items available: " . $menuItems->count() . "\n";
        
        if ($menuItems->count() > 0) {
            echo "Sample items:\n";
            foreach ($menuItems->take(3) as $item) {
                echo "  - {$item->name}: LKR {$item->price}\n";
            }
        }
    } else {
        echo "⚠ No active menu found for branch\n";
    }
}

echo "\n";

// Test 3: Check OrderNumberService
echo "Testing OrderNumberService:\n";
try {
    $branchId = $admin && $admin->branch ? $admin->branch->id : 1;
    $orderNumber = \App\Services\OrderNumberService::generate($branchId);
    $takeawayId = \App\Services\OrderNumberService::generateTakeawayId($branchId);
    
    echo "✓ Order number generated: {$orderNumber}\n";
    echo "✓ Takeaway ID generated: {$takeawayId}\n";
} catch (Exception $e) {
    echo "✗ Error generating numbers: " . $e->getMessage() . "\n";
}

echo "\nAdmin takeaway order system setup complete!\n";
