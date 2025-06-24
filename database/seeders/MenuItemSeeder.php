<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\ItemMaster;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds following UI/UX guidelines.
     */
    public function run(): void
    {
        $this->command->info('🍽️ Seeding menu items with comprehensive restaurant data...');

        // Check table structure and compatibility
        $this->validateTableStructure();

        // Ensure we have the required parent records
        $organizations = Organization::take(5)->get();
        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No organizations found. Run OrganizationSeeder first.');
            return;
        }

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($organizations as $organization) {
            $branches = Branch::where('organization_id', $organization->id)->get();
            
            if ($branches->isEmpty()) {
                $this->command->warn("⚠️ No branches found for organization: {$organization->name}");
                continue;
            }

            foreach ($branches as $branch) {
                $this->command->line("  🏢 Processing branch: {$branch->name} ({$organization->name})");
                
                $result = $this->createMenuItemsForBranch($organization, $branch);
                $createdCount += $result['created'];
                $skippedCount += $result['skipped'];
            }
        }

        $this->command->info("  ✅ Menu items seeding completed:");
        $this->command->info("    • {$createdCount} items created");
        $this->command->info("    • {$skippedCount} items skipped (already exist)");
        $this->command->info("    • Total menu items: " . MenuItem::count());
        
        // Display seeding summary following UI/UX guidelines
        $this->displaySeedingSummary();
    }

    /**
     * Validate table structure compatibility
     */
    private function validateTableStructure(): void
    {
        if (!Schema::hasTable('menu_items')) {
            throw new \Exception('Menu items table does not exist. Run migrations first.');
        }

        $columns = Schema::getColumnListing('menu_items');
        $requiredColumns = [
            'name', 'description', 'price', 'organization_id', 'branch_id',
            'is_available', 'is_featured'
        ];
        
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (!empty($missingColumns)) {
            throw new \Exception('Missing required columns: ' . implode(', ', $missingColumns));
        }

        $this->command->line("  ✅ Table structure validated");
    }

    /**
     * Create menu items for a specific branch following UI/UX patterns
     */
    private function createMenuItemsForBranch(Organization $organization, Branch $branch): array
    {
        $created = 0;
        $skipped = 0;

        // Get or create menu categories
        $categories = $this->getMenuCategories();

        // Restaurant menu items with Sri Lankan and international cuisine
        $menuItems = [
            // Appetizers & Starters
            'Appetizers' => [
                [
                    'name' => 'Fish Cutlets',
                    'description' => 'Traditional Sri Lankan fish cutlets with spicy filling',
                    'price' => 350.00,
                    'is_vegetarian' => false,
                    'is_spicy' => true,
                    'allergens' => ['fish', 'gluten'],
                    'calories' => 280,
                    'preparation_time' => 15,
                    'ingredients' => 'Fish, Potato, Onion, Spices, Breadcrumbs'
                ],
                [
                    'name' => 'Vegetable Spring Rolls',
                    'description' => 'Crispy spring rolls filled with fresh vegetables',
                    'price' => 320.00,
                    'is_vegetarian' => true,
                    'is_spicy' => false,
                    'allergens' => ['gluten'],
                    'calories' => 240,
                    'preparation_time' => 12,
                    'ingredients' => 'Cabbage, Carrot, Bean Sprouts, Wrapper'
                ],
                [
                    'name' => 'Chicken Devilled',
                    'description' => 'Spicy stir-fried chicken with bell peppers',
                    'price' => 450.00,
                    'is_vegetarian' => false,
                    'is_spicy' => true,
                    'allergens' => [],
                    'calories' => 380,
                    'preparation_time' => 18,
                    'ingredients' => 'Chicken, Bell Peppers, Onion, Chili Sauce'
                ]
            ],

            // Main Courses
            'Main Courses' => [
                [
                    'name' => 'Rice & Curry (Traditional)',
                    'description' => 'Authentic Sri Lankan rice and curry with multiple curries',
                    'price' => 650.00,
                    'is_vegetarian' => false,
                    'is_spicy' => true,
                    'allergens' => [],
                    'calories' => 850,
                    'preparation_time' => 25,
                    'ingredients' => 'Rice, Dal Curry, Fish Curry, Vegetable Curry, Sambola'
                ],
                [
                    'name' => 'Chicken Fried Rice',
                    'description' => 'Fragrant fried rice with tender chicken pieces',
                    'price' => 580.00,
                    'is_vegetarian' => false,
                    'is_spicy' => false,
                    'allergens' => ['egg'],
                    'calories' => 720,
                    'preparation_time' => 20,
                    'ingredients' => 'Rice, Chicken, Egg, Vegetables, Soy Sauce'
                ],
                [
                    'name' => 'Kottu Roti (Chicken)',
                    'description' => 'Popular Sri Lankan street food with chopped roti and chicken',
                    'price' => 520.00,
                    'is_vegetarian' => false,
                    'is_spicy' => true,
                    'allergens' => ['gluten'],
                    'calories' => 680,
                    'preparation_time' => 22,
                    'ingredients' => 'Roti, Chicken, Vegetables, Curry Leaves, Spices'
                ],
                [
                    'name' => 'Vegetable Biryani',
                    'description' => 'Aromatic basmati rice with mixed vegetables and spices',
                    'price' => 480.00,
                    'is_vegetarian' => true,
                    'is_spicy' => false,
                    'allergens' => ['nuts'],
                    'calories' => 620,
                    'preparation_time' => 30,
                    'ingredients' => 'Basmati Rice, Mixed Vegetables, Cashews, Spices'
                ]
            ],

            // Seafood Specialties
            'Seafood' => [
                [
                    'name' => 'Fish Ambul Thiyal',
                    'description' => 'Traditional sour fish curry from southern Sri Lanka',
                    'price' => 750.00,
                    'is_vegetarian' => false,
                    'is_spicy' => true,
                    'allergens' => ['fish'],
                    'calories' => 420,
                    'preparation_time' => 35,
                    'ingredients' => 'Fish, Goraka, Curry Leaves, Spices'
                ],
                [
                    'name' => 'Prawn Curry',
                    'description' => 'Rich coconut-based prawn curry',
                    'price' => 850.00,
                    'is_vegetarian' => false,
                    'is_spicy' => true,
                    'allergens' => ['shellfish'],
                    'calories' => 380,
                    'preparation_time' => 25,
                    'ingredients' => 'Prawns, Coconut Milk, Curry Leaves, Spices'
                ]
            ],

            // Desserts
            'Desserts' => [
                [
                    'name' => 'Wattalappam',
                    'description' => 'Traditional Sri Lankan coconut custard dessert',
                    'price' => 280.00,
                    'is_vegetarian' => true,
                    'is_spicy' => false,
                    'allergens' => ['egg', 'dairy'],
                    'calories' => 320,
                    'preparation_time' => 45,
                    'ingredients' => 'Coconut Milk, Jaggery, Eggs, Cardamom'
                ],
                [
                    'name' => 'Chocolate Brownie',
                    'description' => 'Rich chocolate brownie served with vanilla ice cream',
                    'price' => 420.00,
                    'is_vegetarian' => true,
                    'is_spicy' => false,
                    'allergens' => ['gluten', 'dairy', 'egg'],
                    'calories' => 480,
                    'preparation_time' => 15,
                    'ingredients' => 'Chocolate, Flour, Butter, Eggs, Ice Cream'
                ]
            ],

            // Beverages
            'Beverages' => [
                [
                    'name' => 'Ceylon Tea (Hot)',
                    'description' => 'Premium Ceylon black tea',
                    'price' => 180.00,
                    'is_vegetarian' => true,
                    'is_spicy' => false,
                    'allergens' => [],
                    'calories' => 5,
                    'preparation_time' => 5,
                    'ingredients' => 'Ceylon Tea Leaves'
                ],
                [
                    'name' => 'Fresh Lime Juice',
                    'description' => 'Refreshing lime juice with mint',
                    'price' => 220.00,
                    'is_vegetarian' => true,
                    'is_spicy' => false,
                    'allergens' => [],
                    'calories' => 45,
                    'preparation_time' => 3,
                    'ingredients' => 'Lime, Mint, Sugar, Ice'
                ],
                [
                    'name' => 'King Coconut Water',
                    'description' => 'Fresh king coconut water',
                    'price' => 250.00,
                    'is_vegetarian' => true,
                    'is_spicy' => false,
                    'allergens' => [],
                    'calories' => 60,
                    'preparation_time' => 2,
                    'ingredients' => 'Fresh King Coconut'
                ]
            ]
        ];

        foreach ($menuItems as $categoryName => $items) {
            $category = $categories->firstWhere('name', $categoryName);
            
            if (!$category) {
                $this->command->warn("  ⚠️ Category '{$categoryName}' not found, skipping items");
                continue;
            }

            foreach ($items as $index => $itemData) {
                $result = $this->createMenuItem($organization, $branch, $category, $itemData, $index);
                
                if ($result) {
                    $created++;
                    $this->command->line("    ✅ Created: {$itemData['name']}");
                } else {
                    $skipped++;
                    $this->command->line("    ⏭️ Skipped: {$itemData['name']} (already exists)");
                }
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Get or create menu categories
     */
    private function getMenuCategories()
    {
        $categoryNames = ['Appetizers', 'Main Courses', 'Seafood', 'Desserts', 'Beverages'];
        $categories = collect();

        foreach ($categoryNames as $index => $name) {
            $category = MenuCategory::firstOrCreate(
                ['name' => $name],
                [
                    'description' => "Delicious {$name} for every taste",
                    'display_order' => $index + 1,
                    'is_active' => true
                ]
            );
            $categories->push($category);
        }

        return $categories;
    }

    /**
     * Create individual menu item with proper data mapping
     */
    private function createMenuItem($organization, $branch, $category, $itemData, $index): bool
    {
        // Check if item already exists
        $existing = MenuItem::where('name', $itemData['name'])
            ->where('organization_id', $organization->id)
            ->where('branch_id', $branch->id)
            ->first();

        if ($existing) {
            return false;
        }

        // Prepare data array based on actual table structure
        $columns = Schema::getColumnListing('menu_items');
        $menuItemData = [
            'name' => $itemData['name'],
            'description' => $itemData['description'],
            'price' => $itemData['price'],
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'is_available' => true,
            'is_featured' => $index < 2, // First 2 items per category are featured
        ];

        // Add category relationship if column exists
        if (in_array('menu_category_id', $columns)) {
            $menuItemData['menu_category_id'] = $category->id;
        }

        // Add optional columns if they exist
        $optionalFields = [
            'requires_preparation' => true,
            'station' => 'kitchen',
            'is_vegetarian' => $itemData['is_vegetarian'] ?? false,
            'is_spicy' => $itemData['is_spicy'] ?? false,
            'contains_alcohol' => false,
            'allergens' => $itemData['allergens'] ?? [],
            'calories' => $itemData['calories'] ?? null,
            'preparation_time' => $itemData['preparation_time'] ?? 15,
            'ingredients' => $itemData['ingredients'] ?? '',
            'is_active' => true,
            'display_order' => $index + 1,
            'image_path' => $this->generateImagePath($itemData['name']),
        ];

        foreach ($optionalFields as $field => $value) {
            if (in_array($field, $columns)) {
                $menuItemData[$field] = $value;
            }
        }

        // Handle fields with different names in existing table
        if (in_array('image_url', $columns) && !in_array('image_path', $columns)) {
            $menuItemData['image_url'] = $this->generateImagePath($itemData['name']);
        }

        if (in_array('sort_order', $columns) && !in_array('display_order', $columns)) {
            $menuItemData['sort_order'] = $index + 1;
        }

        try {
            MenuItem::create($menuItemData);
            return true;
        } catch (\Exception $e) {
            $this->command->error("    ❌ Failed to create {$itemData['name']}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate image path for menu items
     */
    private function generateImagePath(string $itemName): string
    {
        $slug = Str::slug($itemName);
        return "menu-items/{$slug}-" . Str::uuid() . ".jpg";
    }

    /**
     * Display seeding summary following UI/UX guidelines
     */
    private function displaySeedingSummary(): void
    {
        $totalItems = MenuItem::count();
        $featuredItems = MenuItem::where('is_featured', true)->count();
        $activeItems = MenuItem::where('is_active', true)->count();
        
        $this->command->newLine();
        $this->command->info('📊 Menu Items Summary:');
        $this->command->line("  📋 Total Items: {$totalItems}");
        $this->command->line("  ⭐ Featured Items: {$featuredItems}");
        $this->command->line("  ✅ Active Items: {$activeItems}");
        
        // Category breakdown
        $this->command->newLine();
        $this->command->info('📂 Items by Category:');
        
        $categories = MenuCategory::withCount('menuItems')->get();
        foreach ($categories as $category) {
            $this->command->line("  • {$category->name}: {$category->menu_items_count} items");
        }
        
        // Organization breakdown
        $this->command->newLine();
        $this->command->info('🏢 Items by Organization:');
        
        $organizations = Organization::withCount('menuItems')->take(5)->get();
        foreach ($organizations as $org) {
            $this->command->line("  • {$org->name}: {$org->menu_items_count} items");
        }
    }
}
