<?php

/**
 * Create Sample Menu Data for Phase 2 Testing
 * Generates menus and menu items for demonstration and testing purposes
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Branch;
use App\Models\InventoryItem;
use Carbon\Carbon;

echo "🍽️ CREATING SAMPLE MENU DATA\n";
echo "============================\n\n";

try {
    DB::beginTransaction();
    
    // Get first available branch
    $branch = Branch::with('organization')->first();
    if (!$branch) {
        echo "❌ No branches found. Please run branch seeder first.\n";
        exit(1);
    }
    
    echo "📍 Using branch: {$branch->name} (ID: {$branch->id})\n";
    echo "📍 Organization: {$branch->organization->name} (ID: {$branch->organization_id})\n\n";
    
    // Create main menu for today
    $todayMenu = Menu::create([
        'name' => 'Daily Menu - ' . Carbon::today()->format('M d, Y'),
        'description' => 'Our fresh daily selection of authentic dishes',
        'branch_id' => $branch->id,
        'organization_id' => $branch->organization_id,
        'date_from' => Carbon::today(),
        'date_to' => Carbon::today()->addDays(30),
        'is_active' => true,
        'menu_type' => 'regular',
        'days_of_week' => json_encode([1, 2, 3, 4, 5, 6, 7]), // All days
        'activation_time' => '07:00:00',
        'deactivation_time' => '22:00:00',
        'priority' => 1,
        'auto_activate' => true
    ]);
    
    echo "✅ Created main menu: {$todayMenu->name}\n";
    
    // Create special weekend menu
    $weekendMenu = Menu::create([
        'name' => 'Weekend Special Menu',
        'description' => 'Special weekend dishes and premium selections',
        'branch_id' => $branch->id,
        'organization_id' => $branch->organization_id,
        'date_from' => Carbon::today(),
        'date_to' => Carbon::today()->addDays(60),
        'is_active' => true,
        'menu_type' => 'special',
        'days_of_week' => json_encode([6, 7]), // Saturday and Sunday
        'activation_time' => '11:00:00',
        'deactivation_time' => '21:00:00',
        'priority' => 2,
        'auto_activate' => true,
        'special_occasion' => 'Weekend Premium Selection'
    ]);
    
    echo "✅ Created weekend menu: {$weekendMenu->name}\n\n";
    
    // Menu items data
    $menuItems = [
        // Main dishes
        [
            'name' => 'Chicken Rice & Curry',
            'description' => 'Traditional Sri Lankan rice and curry with chicken, vegetables, and papadam',
            'price' => 1200.00,
            'category' => 'main_course',
            'is_available' => true,
            'preparation_time' => 15
        ],
        [
            'name' => 'Fish Curry with Rice',
            'description' => 'Fresh fish curry served with steamed rice and mixed vegetables',
            'price' => 1400.00,
            'category' => 'main_course',
            'is_available' => true,
            'preparation_time' => 20
        ],
        [
            'name' => 'Vegetable Fried Rice',
            'description' => 'Aromatic fried rice with mixed vegetables and cashews',
            'price' => 950.00,
            'category' => 'main_course',
            'is_available' => true,
            'preparation_time' => 12
        ],
        
        // Appetizers
        [
            'name' => 'Chicken Deviled',
            'description' => 'Spicy chicken pieces with bell peppers and onions',
            'price' => 800.00,
            'category' => 'appetizer',
            'is_available' => true,
            'preparation_time' => 10
        ],
        [
            'name' => 'Fish Cutlets',
            'description' => 'Crispy fried fish cutlets served with chili sauce',
            'price' => 650.00,
            'category' => 'appetizer',
            'is_available' => true,
            'preparation_time' => 8
        ],
        
        // Beverages
        [
            'name' => 'Fresh Lime Juice',
            'description' => 'Refreshing lime juice with mint',
            'price' => 300.00,
            'category' => 'beverage',
            'is_available' => true,
            'preparation_time' => 3
        ],
        [
            'name' => 'King Coconut Water',
            'description' => 'Fresh king coconut water',
            'price' => 250.00,
            'category' => 'beverage',
            'is_available' => true,
            'preparation_time' => 2
        ],
        
        // Desserts
        [
            'name' => 'Curd with Treacle',
            'description' => 'Traditional buffalo curd served with kithul treacle',
            'price' => 400.00,
            'category' => 'dessert',
            'is_available' => true,
            'preparation_time' => 5
        ],
        [
            'name' => 'Watalappan',
            'description' => 'Traditional coconut custard pudding with jaggery',
            'price' => 450.00,
            'category' => 'dessert',
            'is_available' => true,
            'preparation_time' => 5
        ]
    ];
    
    echo "🍜 Creating menu items...\n";
    
    foreach ($menuItems as $index => $itemData) {
        // Create for main menu
        $mainItemData = [
            'name' => $itemData['name'],
            'description' => $itemData['description'],
            'price' => $itemData['price'],
            'category' => $itemData['category'],
            'is_available' => $itemData['is_available'],
            'preparation_time' => $itemData['preparation_time'],
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'display_order' => $index + 1,
            'is_active' => true,
            'is_featured' => $index < 3, // First 3 items are featured
            'allergens' => json_encode(['gluten', 'dairy']),
            'calories' => rand(200, 800),
            'ingredients' => 'Fresh local ingredients as per traditional recipe',
            'station' => $itemData['category'] === 'beverage' ? 'bar' : 'kitchen'
        ];
        
        $mainItem = MenuItem::create($mainItemData);
        
        echo "   ✅ {$itemData['name']} (Daily Menu)\n";
        
        // Create premium versions for weekend menu (selected items)
        if (in_array($itemData['category'], ['main_course', 'dessert'])) {
            $weekendItemData = [
                'name' => 'Premium ' . $itemData['name'],
                'description' => 'Premium version: ' . $itemData['description'],
                'price' => $itemData['price'] * 1.3, // 30% premium
                'category' => $itemData['category'],
                'is_available' => $itemData['is_available'],
                'preparation_time' => $itemData['preparation_time'],
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'display_order' => $index + 1,
                'is_active' => true,
                'is_featured' => true, // All weekend items are featured
                'allergens' => json_encode(['gluten', 'dairy']),
                'calories' => rand(200, 800),
                'ingredients' => 'Premium ingredients with enhanced flavors',
                'station' => 'kitchen'
            ];
            
            $weekendItem = MenuItem::create($weekendItemData);
            
            echo "   ✅ Premium {$itemData['name']} (Weekend Menu)\n";
        }
    }
    
    // Create some basic inventory items to support order management
    $inventoryItems = [
        ['name' => 'Rice', 'unit' => 'kg', 'stock_quantity' => 100, 'min_threshold' => 20],
        ['name' => 'Chicken', 'unit' => 'kg', 'stock_quantity' => 50, 'min_threshold' => 10],
        ['name' => 'Fish', 'unit' => 'kg', 'stock_quantity' => 30, 'min_threshold' => 5],
        ['name' => 'Vegetables Mixed', 'unit' => 'kg', 'stock_quantity' => 80, 'min_threshold' => 15],
        ['name' => 'Coconut', 'unit' => 'pieces', 'stock_quantity' => 200, 'min_threshold' => 50],
    ];
    
    echo "\n📦 Creating inventory items...\n";
    
    foreach ($inventoryItems as $inventoryData) {
        try {
            InventoryItem::create(array_merge($inventoryData, [
                'branch_id' => $branch->id,
                'price_per_unit' => rand(50, 500),
                'supplier_info' => json_encode(['supplier' => 'Local Supplier']),
                'last_updated' => Carbon::now()
            ]));
            
            echo "   ✅ {$inventoryData['name']}\n";
        } catch (Exception $e) {
            echo "   ⚠️ {$inventoryData['name']} (may already exist)\n";
        }
    }
    
    DB::commit();
    
    echo "\n🎉 SAMPLE DATA CREATION COMPLETED!\n";
    echo "==================================\n";
    echo "✅ Created 2 menus\n";
    echo "✅ Created " . (count($menuItems) + 6) . " menu items\n";
    echo "✅ Created " . count($inventoryItems) . " inventory items\n";
    echo "\n🔍 You can now test:\n";
    echo "   • Guest menu viewing: /guest/menu\n";
    echo "   • Cart functionality: Add items and place orders\n";
    echo "   • Reservation system: Book tables\n";
    echo "   • Admin dashboard: View menus and orders\n";
    echo "\n🏃‍♂️ Next steps:\n";
    echo "   • Run health check: php artisan system:health\n";
    echo "   • Test guest flows: Browse to /guest/menu\n";
    echo "   • Test admin features: Login and manage system\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error creating sample data: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    exit(1);
}
