<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Models\ItemMaster;
use App\Models\ItemCategory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InventorySupplierSeeder extends Seeder
{
    /**
     * Populate inventory system with suppliers and stock
     */
    public function run(): void
    {
        $this->command->info('📦 Populating Inventory & Suppliers...');
        
        $organizations = Organization::with(['branches', 'subscriptionPlan'])->get();
        
        foreach ($organizations as $organization) {
            if ($this->organizationHasInventoryAccess($organization)) {
                $this->createInventorySystemForOrganization($organization);
            } else {
                $this->command->info("  ⏭️ Skipping {$organization->name} - No inventory access in subscription");
            }
        }
        
        $this->command->info('✅ Inventory & Suppliers populated successfully');
    }

    private function organizationHasInventoryAccess(Organization $organization): bool
    {
        $modules = $organization->subscriptionPlan->modules ?? [];
        return collect($modules)->contains(function ($module) {
            return isset($module['name']) && $module['name'] === 'inventory';
        });
    }

    private function createInventorySystemForOrganization(Organization $organization): void
    {
        $this->command->info("  📦 Creating inventory system for: {$organization->name}");
        
        // Create suppliers for the organization
        $suppliers = $this->createSuppliersForOrganization($organization);
        
        // Create item categories
        $categories = $this->createItemCategories($organization);
        
        // Create item masters
        $itemMasters = $this->createItemMasters($organization, $categories, $suppliers);
        
        // Create inventory items for each branch with stock levels
        $this->createInventoryItemsWithStock($organization, $itemMasters);
        
        // Trigger some low stock alerts for testing
        $this->triggerStockAlerts($organization);
        
        $this->command->info("    ✓ Created {$suppliers->count()} suppliers, {$itemMasters->count()} items across {$organization->branches->count()} branches");
    }

    private function createSuppliersForOrganization(Organization $organization)
    {
        $supplierTemplates = [
            [
                'name' => 'Fresh Produce Lanka',
                'type' => 'vegetables_fruits',
                'contact_person' => 'Sunil Perera',
                'phone' => '+94 11 234 5001',
                'email' => 'orders@freshproducelanka.lk',
                'address' => 'Pettah Market, Colombo 11',
                'payment_terms' => 'NET_30',
                'delivery_schedule' => 'daily',
                'minimum_order' => 5000.00
            ],
            [
                'name' => 'Ocean Fresh Seafood',
                'type' => 'seafood',
                'contact_person' => 'Kamala Fernando',
                'phone' => '+94 31 567 8902',
                'email' => 'supply@oceanfresh.lk',
                'address' => 'Negombo Fish Market',
                'payment_terms' => 'COD',
                'delivery_schedule' => 'tuesday_friday',
                'minimum_order' => 8000.00
            ],
            [
                'name' => 'Ceylon Meat Company',
                'type' => 'meat_poultry',
                'contact_person' => 'Ravi Silva',
                'phone' => '+94 11 789 0123',
                'email' => 'orders@ceylonmeat.lk',
                'address' => 'Kelaniya Industrial Zone',
                'payment_terms' => 'NET_15',
                'delivery_schedule' => 'monday_wednesday_friday',
                'minimum_order' => 10000.00
            ],
            [
                'name' => 'Spice Island Trading',
                'type' => 'spices_condiments',
                'contact_person' => 'Nimal Jayasinghe',
                'phone' => '+94 81 234 5678',
                'email' => 'sales@spiceisland.lk',
                'address' => 'Matale Spice Gardens',
                'payment_terms' => 'NET_45',
                'delivery_schedule' => 'weekly',
                'minimum_order' => 3000.00
            ],
            [
                'name' => 'Dairy Fresh Ltd',
                'type' => 'dairy_beverages',
                'contact_person' => 'Priya Rathnayake',
                'phone' => '+94 25 678 9012',
                'email' => 'orders@dairyfresh.lk',
                'address' => 'Kurunegala Dairy Farm',
                'payment_terms' => 'NET_7',
                'delivery_schedule' => 'daily',
                'minimum_order' => 4000.00
            ]
        ];

        $suppliers = collect();
        
        foreach ($supplierTemplates as $supplierData) {
            $supplier = Supplier::create([
                'organization_id' => $organization->id,
                'supplier_id' => 'SUP-' . $organization->id . '-' . str_pad(array_search($supplierData, $supplierTemplates) + 1, 3, '0', STR_PAD_LEFT),
                'name' => $supplierData['name'],
                'supplier_type' => $supplierData['type'],
                'contact_person' => $supplierData['contact_person'],
                'phone' => $supplierData['phone'],
                'email' => $supplierData['email'],
                'address' => $supplierData['address'],
                'has_vat_registration' => rand(0, 1),
                'vat_registration_no' => rand(0, 1) ? 'VAT-' . rand(100000000, 999999999) : null,
                'is_active' => true,
                'is_inactive' => false,
            ]);
            
            $suppliers->push($supplier);
        }

        return $suppliers;
    }

    private function createItemCategories(Organization $organization)
    {
        $categories = [
            ['name' => 'Vegetables', 'description' => 'Fresh vegetables and greens'],
            ['name' => 'Fruits', 'description' => 'Fresh fruits and seasonal produce'],
            ['name' => 'Meat & Poultry', 'description' => 'Fresh meat, chicken, and poultry products'],
            ['name' => 'Seafood', 'description' => 'Fresh fish and seafood'],
            ['name' => 'Dairy Products', 'description' => 'Milk, cheese, yogurt, and dairy items'],
            ['name' => 'Spices & Seasonings', 'description' => 'Local and imported spices'],
            ['name' => 'Beverages', 'description' => 'Soft drinks, juices, and beverages'],
            ['name' => 'Dry Goods', 'description' => 'Rice, flour, lentils, and dry ingredients'],
            ['name' => 'Oils & Fats', 'description' => 'Cooking oils and fats'],
            ['name' => 'Cleaning Supplies', 'description' => 'Kitchen and restaurant cleaning products']
        ];

        $createdCategories = collect();
        
        foreach ($categories as $categoryData) {
            $category = ItemCategory::create([
                'organization_id' => $organization->id,
                'name' => $categoryData['name'],
                'code' => strtoupper(substr(str_replace(['&', ' '], '', $categoryData['name']), 0, 5)),
                'description' => $categoryData['description'],
                'is_active' => true
            ]);
            
            $createdCategories->push($category);
        }

        return $createdCategories;
    }

    private function createItemMasters(Organization $organization, $categories, $suppliers)
    {
        $itemTemplates = [
            // Vegetables
            ['name' => 'Tomatoes', 'category' => 'Vegetables', 'unit' => 'kg', 'price' => 150.00],
            ['name' => 'Onions', 'category' => 'Vegetables', 'unit' => 'kg', 'price' => 120.00],
            ['name' => 'Carrots', 'category' => 'Vegetables', 'unit' => 'kg', 'price' => 180.00],
            ['name' => 'Green Beans', 'category' => 'Vegetables', 'unit' => 'kg', 'price' => 200.00],
            ['name' => 'Bell Peppers', 'category' => 'Vegetables', 'unit' => 'kg', 'price' => 350.00],
            
            // Fruits
            ['name' => 'Lemons', 'category' => 'Fruits', 'unit' => 'kg', 'price' => 250.00],
            ['name' => 'Limes', 'category' => 'Fruits', 'unit' => 'kg', 'price' => 300.00],
            
            // Meat & Poultry
            ['name' => 'Chicken Breast', 'category' => 'Meat & Poultry', 'unit' => 'kg', 'price' => 850.00],
            ['name' => 'Beef Steaks', 'category' => 'Meat & Poultry', 'unit' => 'kg', 'price' => 1500.00],
            ['name' => 'Pork Chops', 'category' => 'Meat & Poultry', 'unit' => 'kg', 'price' => 1200.00],
            
            // Seafood
            ['name' => 'Fresh Tuna', 'category' => 'Seafood', 'unit' => 'kg', 'price' => 1800.00],
            ['name' => 'King Prawns', 'category' => 'Seafood', 'unit' => 'kg', 'price' => 2500.00],
            ['name' => 'Red Snapper', 'category' => 'Seafood', 'unit' => 'kg', 'price' => 1200.00],
            
            // Dairy
            ['name' => 'Fresh Milk', 'category' => 'Dairy Products', 'unit' => 'liter', 'price' => 180.00],
            ['name' => 'Cheddar Cheese', 'category' => 'Dairy Products', 'unit' => 'kg', 'price' => 2200.00],
            ['name' => 'Greek Yogurt', 'category' => 'Dairy Products', 'unit' => 'kg', 'price' => 400.00],
            
            // Spices
            ['name' => 'Black Pepper', 'category' => 'Spices & Seasonings', 'unit' => 'kg', 'price' => 1800.00],
            ['name' => 'Cinnamon Sticks', 'category' => 'Spices & Seasonings', 'unit' => 'kg', 'price' => 2500.00],
            ['name' => 'Curry Powder', 'category' => 'Spices & Seasonings', 'unit' => 'kg', 'price' => 800.00],
            ['name' => 'Sea Salt', 'category' => 'Spices & Seasonings', 'unit' => 'kg', 'price' => 150.00],
            
            // Dry Goods
            ['name' => 'Basmati Rice', 'category' => 'Dry Goods', 'unit' => 'kg', 'price' => 380.00],
            ['name' => 'Wheat Flour', 'category' => 'Dry Goods', 'unit' => 'kg', 'price' => 120.00],
            ['name' => 'Red Lentils', 'category' => 'Dry Goods', 'unit' => 'kg', 'price' => 200.00],
            
            // Oils
            ['name' => 'Coconut Oil', 'category' => 'Oils & Fats', 'unit' => 'liter', 'price' => 450.00],
            ['name' => 'Olive Oil', 'category' => 'Oils & Fats', 'unit' => 'liter', 'price' => 1200.00]
        ];

        $itemMasters = collect();
        
        foreach ($itemTemplates as $itemData) {
            $category = $categories->where('name', $itemData['category'])->first();
            $supplier = $suppliers->random(); // Use random supplier since we don't have supplier_type
            
            if ($category && $supplier) {
                $itemMaster = ItemMaster::create([
                    'organization_id' => $organization->id,
                    'supplier_id' => $supplier->id,
                    'item_category_id' => $category->id,
                    'name' => $itemData['name'],
                    'sku' => 'SKU-' . Str::upper(Str::random(6)),
                    'unit' => $itemData['unit'],
                    'unit_of_measurement' => $itemData['unit'],
                    'purchase_price' => $itemData['price'],
                    'selling_price' => $itemData['price'] * 1.4, // 40% markup
                    'minimum_stock' => rand(10, 30),
                    'maximum_stock' => rand(100, 200),
                    'reorder_level' => rand(20, 50),
                    'is_active' => true,
                    'is_inventory_item' => true,
                    'description' => 'High quality ' . strtolower($itemData['name']),
                    'storage_requirements' => $this->getStorageRequirements($itemData['category']),
                    'shelf_life_days' => $this->getShelfLife($itemData['category']),
                    'item_code' => 'ITM' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)
                ]);
                
                $itemMasters->push($itemMaster);
            }
        }

        return $itemMasters;
    }

    private function createInventoryItemsWithStock(Organization $organization, $itemMasters): void
    {
        foreach ($organization->branches as $branch) {
            $this->command->info("    📦 Creating inventory for branch: {$branch->name}");
            
            foreach ($itemMasters as $itemMaster) {
                // Generate realistic stock levels
                $minStock = $itemMaster->minimum_stock;
                $maxStock = $itemMaster->maximum_stock;
                $reorderLevel = $itemMaster->reorder_level;
                
                // Some items will be low stock to trigger alerts
                $isLowStock = rand(1, 100) <= 15; // 15% chance of low stock
                
                if ($isLowStock) {
                    $currentStock = rand(0, $reorderLevel - 1); // Below reorder level
                } else {
                    $currentStock = rand($reorderLevel + 5, $maxStock);
                }
                
                // TODO: Create InventoryItem when table exists
                // $inventoryItem = InventoryItem::create([
                //     'organization_id' => $organization->id,
                //     'branch_id' => $branch->id,
                //     'item_masters_id' => $itemMaster->id,
                //     'current_stock' => $currentStock,
                //     'minimum_stock' => $minStock,
                //     'maximum_stock' => $maxStock,
                //     'reorder_level' => $reorderLevel,
                //     'unit_cost' => $itemMaster->purchase_price,
                //     'last_updated' => now()->subDays(rand(1, 7)),
                //     'last_stock_count' => now()->subDays(rand(7, 30)),
                //     'location' => $this->getStorageLocation($itemMaster->itemCategory->name),
                //     'batch_number' => 'BATCH-' . now()->format('Ymd') . '-' . rand(100, 999),
                //     'expiry_date' => $this->calculateExpiryDate($itemMaster->shelf_life_days),
                //     'is_active' => true
                // ]);

                // Note: Stock model might need to be created or we can track stock within InventoryItem
            }
        }
    }

    private function triggerStockAlerts(Organization $organization): void
    {
        try {
            // Count low stock items for reporting
            $lowStockCount = 0;
            
            foreach ($organization->branches as $branch) {
                $lowStockItems = InventoryItem::where('branch_id', $branch->id)
                    ->whereColumn('current_stock', '<=', 'reorder_level')
                    ->count();
                
                $lowStockCount += $lowStockItems;
                
                if ($lowStockItems > 0) {
                    $this->command->info("    🚨 {$lowStockItems} low stock items in {$branch->name}");
                }
            }
            
            if ($lowStockCount === 0) {
                $this->command->info("    ✅ No stock alerts generated - all items adequately stocked");
            }
        } catch (\Exception $e) {
            $this->command->warn("    ⚠️ Could not check stock levels: " . $e->getMessage());
        }
    }

    // Helper methods
    private function getStorageRequirements(string $category): string
    {
        return match($category) {
            'Vegetables', 'Fruits' => 'Cool, dry place (2-8°C)',
            'Meat & Poultry', 'Seafood' => 'Frozen storage (-18°C)',
            'Dairy Products' => 'Refrigerated (2-4°C)',
            'Spices & Seasonings' => 'Dry, airtight containers',
            default => 'Room temperature, dry storage'
        };
    }

    private function getShelfLife(string $category): int
    {
        return match($category) {
            'Vegetables' => rand(3, 7),
            'Fruits' => rand(5, 10),
            'Meat & Poultry' => rand(2, 5),
            'Seafood' => rand(1, 3),
            'Dairy Products' => rand(7, 21),
            'Spices & Seasonings' => rand(365, 730), // 1-2 years
            'Dry Goods' => rand(180, 365),
            default => 30
        };
    }

    private function getStorageLocation(string $category): string
    {
        return match($category) {
            'Vegetables', 'Fruits' => 'Cold Room A',
            'Meat & Poultry' => 'Freezer Unit 1',
            'Seafood' => 'Freezer Unit 2',
            'Dairy Products' => 'Refrigerator Section',
            'Spices & Seasonings' => 'Spice Cabinet',
            'Dry Goods' => 'Dry Storage Rack',
            'Oils & Fats' => 'Oil Storage',
            default => 'General Storage'
        };
    }

    private function calculateExpiryDate(?int $shelfLifeDays): ?Carbon
    {
        if (!$shelfLifeDays) {
            return null;
        }
        
        return now()->addDays($shelfLifeDays - rand(1, 5)); // Account for some variation
    }
}
