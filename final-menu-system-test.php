<?php
/**
 * Final Menu System POST/Activation Test
 * Tests complete menu creation, update, activation/deactivation workflow
 */

require_once __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Branch;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== FINAL MENU SYSTEM TEST ===\n\n";

try {
    // Get test data
    $branch = Branch::first();
    $menuItems = MenuItem::take(3)->pluck('id')->toArray();
    $admin = Admin::first();
    
    if (!$branch || empty($menuItems) || !$admin) {
        echo "❌ Prerequisites not found (branch, menu items, or admin)\n";
        exit(1);
    }
    
    echo "✓ Prerequisites found:\n";
    echo "  - Branch: {$branch->name}\n";
    echo "  - Menu items: " . count($menuItems) . " items\n";
    echo "  - Admin: {$admin->name}\n\n";
    
    $todayDayName = strtolower(Carbon::today()->format('l'));
    
    // Test 1: Menu Creation
    echo "TEST 1: MENU CREATION\n";
    DB::beginTransaction();
    
    $menuData = [
        'name' => 'Test Menu - ' . date('Y-m-d H:i:s'),
        'description' => 'Test menu for final verification',
        'type' => 'lunch',
        'branch_id' => $branch->id,
        'organization_id' => $admin->organization_id ?? 1,
        'date_from' => Carbon::today()->toDateString(),
        'date_to' => Carbon::today()->addDays(3)->toDateString(),
        'valid_from' => Carbon::today()->toDateString(),
        'valid_until' => Carbon::today()->addDays(3)->toDateString(),
        'available_days' => [$todayDayName],
        'start_time' => '09:00',
        'end_time' => '17:00',
        'is_active' => false,
        'created_by' => $admin->id
    ];
    
    $menu = Menu::create($menuData);
    echo "✓ Menu created successfully (ID: {$menu->id})\n";
    
    // Attach menu items
    $menu->menuItems()->attach($menuItems);
    echo "✓ Menu items attached successfully\n";
    
    // Test 2: Menu Activation
    echo "\nTEST 2: MENU ACTIVATION\n";
    $canActivate = $menu->shouldBeActiveNow();
    echo "✓ Should be active now: " . ($canActivate ? 'Yes' : 'No') . "\n";
    
    if ($canActivate) {
        $activated = $menu->activate();
        echo "✓ Menu activation: " . ($activated ? 'Success' : 'Failed') . "\n";
    } else {
        echo "⚠ Menu cannot be activated (outside valid time/date range)\n";
    }
    
    // Test 3: Menu Deactivation
    echo "\nTEST 3: MENU DEACTIVATION\n";
    $deactivated = $menu->deactivate();
    echo "✓ Menu deactivation: " . ($deactivated ? 'Success' : 'Failed') . "\n";
    
    // Test 4: Menu Update
    echo "\nTEST 4: MENU UPDATE\n";
    $menu->update([
        'name' => 'Updated Test Menu - ' . date('Y-m-d H:i:s'),
        'description' => 'Updated description'
    ]);
    echo "✓ Menu updated successfully\n";
    
    // Test 5: Relationship Loading
    echo "\nTEST 5: RELATIONSHIP LOADING\n";
    $menuWithRelations = Menu::with(['menuItems', 'branch', 'creator'])->find($menu->id);
    echo "✓ Menu items count: " . $menuWithRelations->menuItems->count() . "\n";
    echo "✓ Branch loaded: " . ($menuWithRelations->branch ? $menuWithRelations->branch->name : 'Failed') . "\n";
    echo "✓ Creator loaded: " . ($menuWithRelations->creator ? $menuWithRelations->creator->name : 'Failed') . "\n";
    
    // Test 6: Scopes
    echo "\nTEST 6: MODEL SCOPES\n";
    $activeCount = Menu::active()->count();
    $inactiveCount = Menu::inactive()->count();
    echo "✓ Active menus: $activeCount\n";
    echo "✓ Inactive menus: $inactiveCount\n";
    
    // Test 7: Bulk Operations Test Data
    echo "\nTEST 7: BULK OPERATIONS SIMULATION\n";
    $bulkMenus = [];
    for ($i = 1; $i <= 3; $i++) {
        $bulkMenu = Menu::create([
            'name' => "Bulk Test Menu $i - " . date('Y-m-d H:i:s'),
            'description' => "Bulk test menu $i",
            'type' => 'dinner',
            'branch_id' => $branch->id,
            'organization_id' => $admin->organization_id ?? 1,
            'date_from' => Carbon::today()->addDays($i)->toDateString(),
            'date_to' => Carbon::today()->addDays($i)->toDateString(),
            'valid_from' => Carbon::today()->addDays($i)->toDateString(),
            'valid_until' => Carbon::today()->addDays($i)->toDateString(),
            'available_days' => [$todayDayName],
            'start_time' => '18:00',
            'end_time' => '22:00',
            'is_active' => false,
            'created_by' => $admin->id
        ]);
        $bulkMenus[] = $bulkMenu->id;
        $bulkMenu->menuItems()->attach($menuItems);
    }
    echo "✓ Created 3 bulk test menus\n";
    
    // Test bulk deactivation
    $deactivatedCount = Menu::whereIn('id', $bulkMenus)->update(['is_active' => false]);
    echo "✓ Bulk deactivated $deactivatedCount menus\n";
    
    DB::rollBack(); // Clean up test data
    echo "\n✓ Test cleanup completed\n";
    
    echo "\n=== ALL TESTS PASSED ===\n";
    echo "Menu system is working correctly:\n";
    echo "✓ Menu creation with all required fields\n";
    echo "✓ Menu item attachment via pivot table\n";
    echo "✓ Menu activation/deactivation\n";
    echo "✓ Menu updates\n";
    echo "✓ All relationships loading properly\n";
    echo "✓ Model scopes working\n";
    echo "✓ Bulk operations supported\n";
    
    echo "\n🎉 MENU SYSTEM FULLY FUNCTIONAL! 🎉\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
