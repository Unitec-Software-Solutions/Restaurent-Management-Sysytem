<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Organization;
use App\Models\ItemMaster;
use App\Models\InventoryItem;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\ItemCategory;
use App\Models\MenuCategory;

class SampleInventoryMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding sample inventory and menu data...');

        // Get all organizations
        $organizations = Organization::with('branches')->where('is_active', true)->get();

        foreach ($organizations as $organization) {
            $this->command->info("Seeding data for: {$organization->name}");

            // Create inventory items for each organization
            $this->seedInventoryItems($organization);

            // Create menus and menu items for each organization
            $this->seedMenus($organization);
        }
    }

    private function seedInventoryItems($organization)
    {
        // Get first active branch for the organization
        $branch = $organization->branches()->where('is_active', true)->first();
        if (!$branch) {
            $this->command->warn("No active branch found for {$organization->name}, skipping inventory");
            return;
        }

        // Create sample item categories if they don't exist
        $categories = [
            ['name' => 'Vegetables', 'description' => 'Fresh vegetables'],
            ['name' => 'Meat & Poultry', 'description' => 'Fresh meat and poultry'],
            ['name' => 'Seafood', 'description' => 'Fresh seafood'],
            ['name' => 'Dairy', 'description' => 'Dairy products'],
            ['name' => 'Spices & Seasonings', 'description' => 'Spices and seasonings'],
            ['name' => 'Beverages', 'description' => 'Beverages and drinks'],
        ];

        foreach ($categories as $categoryData) {
            ItemCategory::firstOrCreate(
                ['name' => $categoryData['name'], 'organization_id' => $organization->id],
                ['description' => $categoryData['description'], 'is_active' => true]
            );
        }

        $vegetableCategory = ItemCategory::where('name', 'Vegetables')->where('organization_id', $organization->id)->first();
        $meatCategory = ItemCategory::where('name', 'Meat & Poultry')->where('organization_id', $organization->id)->first();
        $seafoodCategory = ItemCategory::where('name', 'Seafood')->where('organization_id', $organization->id)->first();
        $dairyCategory = ItemCategory::where('name', 'Dairy')->where('organization_id', $organization->id)->first();
        $spicesCategory = ItemCategory::where('name', 'Spices & Seasonings')->where('organization_id', $organization->id)->first();

        // Sample inventory items using ItemMaster
        $itemMasterData = [
            // Vegetables
            ['name' => 'Tomatoes', 'unit_of_measurement' => 'kg', 'buying_price' => 3.50, 'current_stock' => 25, 'minimum_stock' => 5, 'item_category_id' => $vegetableCategory->id],
            ['name' => 'Onions', 'unit_of_measurement' => 'kg', 'buying_price' => 2.80, 'current_stock' => 30, 'minimum_stock' => 10, 'item_category_id' => $vegetableCategory->id],
            ['name' => 'Carrots', 'unit_of_measurement' => 'kg', 'buying_price' => 4.20, 'current_stock' => 15, 'minimum_stock' => 5, 'item_category_id' => $vegetableCategory->id],
            ['name' => 'Potatoes', 'unit_of_measurement' => 'kg', 'buying_price' => 2.50, 'current_stock' => 50, 'minimum_stock' => 15, 'item_category_id' => $vegetableCategory->id],

            // Meat & Poultry
            ['name' => 'Chicken Breast', 'unit_of_measurement' => 'kg', 'buying_price' => 12.50, 'current_stock' => 20, 'minimum_stock' => 5, 'item_category_id' => $meatCategory->id],
            ['name' => 'Beef Chuck', 'unit_of_measurement' => 'kg', 'buying_price' => 18.75, 'current_stock' => 15, 'minimum_stock' => 3, 'item_category_id' => $meatCategory->id],
            ['name' => 'Pork Tenderloin', 'unit_of_measurement' => 'kg', 'buying_price' => 16.25, 'current_stock' => 8, 'minimum_stock' => 2, 'item_category_id' => $meatCategory->id],

            // Seafood
            ['name' => 'Salmon Fillet', 'unit_of_measurement' => 'kg', 'buying_price' => 28.50, 'current_stock' => 10, 'minimum_stock' => 2, 'item_category_id' => $seafoodCategory->id],
            ['name' => 'Prawns', 'unit_of_measurement' => 'kg', 'buying_price' => 22.75, 'current_stock' => 5, 'minimum_stock' => 1, 'item_category_id' => $seafoodCategory->id],

            // Dairy
            ['name' => 'Milk', 'unit_of_measurement' => 'liters', 'buying_price' => 1.50, 'current_stock' => 40, 'minimum_stock' => 10, 'item_category_id' => $dairyCategory->id],
            ['name' => 'Cheese (Cheddar)', 'unit_of_measurement' => 'kg', 'buying_price' => 8.50, 'current_stock' => 5, 'minimum_stock' => 1, 'item_category_id' => $dairyCategory->id],
            ['name' => 'Butter', 'unit_of_measurement' => 'kg', 'buying_price' => 6.25, 'current_stock' => 8, 'minimum_stock' => 2, 'item_category_id' => $dairyCategory->id],

            // Spices
            ['name' => 'Salt', 'unit_of_measurement' => 'kg', 'buying_price' => 1.25, 'current_stock' => 20, 'minimum_stock' => 5, 'item_category_id' => $spicesCategory->id],
            ['name' => 'Black Pepper', 'unit_of_measurement' => 'kg', 'buying_price' => 15.50, 'current_stock' => 2, 'minimum_stock' => 1, 'item_category_id' => $spicesCategory->id],
            ['name' => 'Curry Powder', 'unit_of_measurement' => 'kg', 'buying_price' => 8.75, 'current_stock' => 3, 'minimum_stock' => 1, 'item_category_id' => $spicesCategory->id],
        ];

        foreach ($itemMasterData as $itemData) {
            $itemMaster = ItemMaster::create([
                'name' => $itemData['name'],
                'unit_of_measurement' => $itemData['unit_of_measurement'],
                'buying_price' => $itemData['buying_price'],
                'selling_price' => $itemData['buying_price'] * 1.3, // 30% markup
                'item_category_id' => $itemData['item_category_id'],
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'description' => "Fresh {$itemData['name']} for restaurant use",
                'is_active' => true,
                'reorder_level' => $itemData['minimum_stock'],
                'item_code' => 'ITM' . rand(1000, 9999),
            ]);

            // Create corresponding InventoryItem record
            InventoryItem::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'item_master_id' => $itemMaster->id,
                'current_stock' => $itemData['current_stock'],
                'reorder_level' => $itemData['minimum_stock'],
                'max_stock' => $itemData['current_stock'] * 2,
                'cost_price' => $itemData['buying_price'],
                'selling_price' => $itemData['buying_price'] * 1.3,
                'status' => 'active',
            ]);

            echo "Created item: {$itemMaster->name} with stock: {$itemData['current_stock']}\n";
        }

        $this->command->info("   Created " . count($itemMasterData) . " inventory items");
    }

    private function seedMenus($organization)
    {
        $branch = $organization->branches()->first();
        
        // Create sample menu categories
        $menuCategories = [
            ['name' => 'Appetizers', 'description' => 'Starters and small plates'],
            ['name' => 'Main Courses', 'description' => 'Main course dishes'],
            ['name' => 'Desserts', 'description' => 'Sweet desserts'],
            ['name' => 'Beverages', 'description' => 'Drinks and beverages'],
            ['name' => 'Specials', 'description' => 'Chef specials and seasonal items'],
        ];

        foreach ($menuCategories as $categoryData) {
            MenuCategory::firstOrCreate(
                ['name' => $categoryData['name'], 'organization_id' => $organization->id, 'branch_id' => $branch->id],
                ['description' => $categoryData['description'], 'is_active' => true]
            );
        }

        $appetizerCategory = MenuCategory::where('name', 'Appetizers')->where('organization_id', $organization->id)->where('branch_id', $branch->id)->first();
        $mainCategory = MenuCategory::where('name', 'Main Courses')->where('organization_id', $organization->id)->where('branch_id', $branch->id)->first();
        $dessertCategory = MenuCategory::where('name', 'Desserts')->where('organization_id', $organization->id)->where('branch_id', $branch->id)->first();
        $beverageCategory = MenuCategory::where('name', 'Beverages')->where('organization_id', $organization->id)->where('branch_id', $branch->id)->first();

        // Create main menu
        $mainMenu = Menu::create([
            'name' => 'Main Menu',
            'description' => 'Our signature main menu with popular dishes',
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'date_from' => now()->format('Y-m-d'),
            'date_to' => now()->addYears(10)->format('Y-m-d'), // Valid for 10 years
            'is_active' => true,
            'menu_type' => 'regular',
            'auto_activate' => true,
        ]);

        // Create menu items
        $menuItems = [
            // Appetizers
            ['name' => 'Caesar Salad', 'description' => 'Fresh romaine lettuce with caesar dressing', 'price' => 12.50, 'menu_category_id' => $appetizerCategory->id],
            ['name' => 'Garlic Bread', 'description' => 'Toasted bread with garlic butter', 'price' => 8.75, 'menu_category_id' => $appetizerCategory->id],
            ['name' => 'Soup of the Day', 'description' => 'Daily fresh soup selection', 'price' => 9.25, 'menu_category_id' => $appetizerCategory->id],

            // Main Courses
            ['name' => 'Grilled Chicken Breast', 'description' => 'Seasoned grilled chicken with vegetables', 'price' => 24.50, 'menu_category_id' => $mainCategory->id],
            ['name' => 'Beef Steak', 'description' => 'Prime beef steak cooked to perfection', 'price' => 32.75, 'menu_category_id' => $mainCategory->id],
            ['name' => 'Salmon Teriyaki', 'description' => 'Fresh salmon with teriyaki glaze', 'price' => 28.25, 'menu_category_id' => $mainCategory->id],
            ['name' => 'Vegetarian Pasta', 'description' => 'Fresh pasta with seasonal vegetables', 'price' => 18.50, 'menu_category_id' => $mainCategory->id],
            ['name' => 'Fish and Chips', 'description' => 'Beer battered fish with crispy chips', 'price' => 22.75, 'menu_category_id' => $mainCategory->id],

            // Desserts
            ['name' => 'Chocolate Cake', 'description' => 'Rich chocolate cake with vanilla ice cream', 'price' => 9.50, 'menu_category_id' => $dessertCategory->id],
            ['name' => 'Fruit Salad', 'description' => 'Fresh seasonal fruit salad', 'price' => 7.25, 'menu_category_id' => $dessertCategory->id],
            ['name' => 'Ice Cream Sundae', 'description' => 'Vanilla ice cream with toppings', 'price' => 8.75, 'menu_category_id' => $dessertCategory->id],

            // Beverages
            ['name' => 'Fresh Orange Juice', 'description' => 'Freshly squeezed orange juice', 'price' => 4.50, 'menu_category_id' => $beverageCategory->id],
            ['name' => 'Coffee', 'description' => 'Freshly brewed coffee', 'price' => 3.25, 'menu_category_id' => $beverageCategory->id],
            ['name' => 'Tea', 'description' => 'Selection of fine teas', 'price' => 2.75, 'menu_category_id' => $beverageCategory->id],
            ['name' => 'Soft Drinks', 'description' => 'Assorted soft drinks', 'price' => 3.50, 'menu_category_id' => $beverageCategory->id],
        ];

        foreach ($menuItems as $itemData) {
            $menuItem = MenuItem::create(array_merge($itemData, [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'is_available' => true,
                'preparation_time' => rand(10, 30), // Random prep time between 10-30 minutes
            ]));
            
            // Create the menu-item relationship in the pivot table
            DB::table('menu_menu_items')->insert([
                'menu_id' => $mainMenu->id,
                'menu_item_id' => $menuItem->id,
                'is_available' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("   Created menu with " . count($menuItems) . " items");
    }
}
