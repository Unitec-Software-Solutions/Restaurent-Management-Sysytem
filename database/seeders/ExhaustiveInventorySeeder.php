<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\ItemMaster;
use App\Models\Supplier;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GrnMaster;
use App\Models\GrnItem;
use App\Models\GoodsTransferNote;
use App\Models\GoodsTransferItem;
use App\Models\StockMovement;
use App\Models\InventoryAdjustment; 
use App\Models\InventoryAudit;
use App\Models\InventoryAuditItem;
use Carbon\Carbon;

class ExhaustiveInventorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('  📦 Creating inventory management scenarios...');

        $organizations = Organization::with(['branches', 'suppliers'])->get();

        foreach ($organizations as $org) {
            $this->createInventoryForOrganization($org);
        }

        $this->command->info("  ✅ Created comprehensive inventory scenarios");
    }

    private function createInventoryForOrganization(Organization $org): void
    {
        foreach ($org->branches as $branch) {
            // Create base inventory items
            $this->createBaseInventoryItems($org, $branch);
            
            // Create edge case scenarios
            // $this->createLowStockScenarios($org, $branch);
            // $this->createInventoryAdjustments($org, $branch);
            // $this->createSupplierDeliveries($org, $branch);
            // $this->createInterBranchTransfers($org, $branch);
            $this->createBatchTrackingScenarios($org, $branch);
            // $this->createSeasonalFluctuations($org, $branch);
            $this->createEmergencyReplenishment($org, $branch);
            // $this->createInventoryAudits($org, $branch);
            $this->createAutomatedReorderScenarios($org, $branch);
        }
    }

    private function createBaseInventoryItems(Organization $org, Branch $branch): void
    {
        $inventoryCategories = [
            'fresh_produce' => [
                'vegetables' => ['Tomatoes', 'Onions', 'Carrots', 'Bell Peppers', 'Lettuce'],
                'fruits' => ['Lemons', 'Apples', 'Bananas', 'Oranges'],
                'herbs' => ['Basil', 'Cilantro', 'Parsley', 'Mint']
            ],
            'proteins' => [
                'meat' => ['Chicken Breast', 'Beef Tenderloin', 'Pork Chops', 'Lamb Leg'],
                'seafood' => ['Salmon Fillet', 'Shrimp', 'Tuna', 'Crab Meat'],
                'dairy' => ['Milk', 'Cheese', 'Butter', 'Yogurt', 'Eggs']
            ],
            'pantry_items' => [
                'grains' => ['Rice', 'Pasta', 'Quinoa', 'Bread Flour'],
                'spices' => ['Salt', 'Black Pepper', 'Cumin', 'Paprika', 'Garlic Powder'],
                'oils' => ['Olive Oil', 'Vegetable Oil', 'Coconut Oil']
            ],
            'beverages' => [
                'alcoholic' => ['Wine', 'Beer', 'Whiskey', 'Vodka'],
                'non_alcoholic' => ['Coffee Beans', 'Tea Leaves', 'Fruit Juices', 'Soft Drinks']
            ],
            'supplies' => [
                'cleaning' => ['Dish Soap', 'Sanitizer', 'Paper Towels'],
                'packaging' => ['Take-out Containers', 'Plastic Bags', 'Aluminum Foil']
            ]
        ];

        foreach ($inventoryCategories as $categoryName => $subcategories) {
            foreach ($subcategories as $subcategoryName => $items) {
                foreach ($items as $itemName) {
                    $this->createInventoryItem($org, $branch, $itemName, $categoryName, $subcategoryName);
                }
            }
        }
    }

    private function createInventoryItem(Organization $org, Branch $branch, string $itemName, string $category, string $subcategory): InventoryItem
    {
        // Get or create ItemMaster
        $itemMaster = ItemMaster::firstOrCreate([
            'organization_id' => $org->id,
            'branch_id' => $branch->id,
            'name' => $itemName,
        ], [
            'sku' => $this->generateSKU($itemName),
            'category' => $category,
            'subcategory' => $subcategory,
            'unit_of_measure' => $this->getUnitOfMeasure($itemName),
            'reorder_level' => $this->getReorderLevel($category),
            'max_stock_level' => $this->getMaxStockLevel($category),
            'cost_price' => $this->getCostPrice($itemName),
            'supplier_id' => $this->getRandomSupplier($org)?->id,
        ]);

        return InventoryItem::create([
            'organization_id' => $org->id,
            'branch_id' => $branch->id,
            'item_master_id' => $itemMaster->id,
            'current_stock' => $this->getInitialStock($category),
            'allocated_stock' => rand(0, 50),
            'available_stock' => function() use ($itemMaster) {
                return $itemMaster->current_stock - $itemMaster->allocated_stock;
            },
            'unit_cost' => $itemMaster->cost_price,
            'total_value' => function() use ($itemMaster) {
                return $itemMaster->current_stock * $itemMaster->unit_cost;
            },
            'last_restocked_at' => Carbon::now()->subDays(rand(1, 30)),
            'expiry_date' => $this->getExpiryDate($category),
            'batch_number' => $this->generateBatchNumber(),
            'storage_location' => $this->getStorageLocation($category),
            'valuation_method' => $this->getValuationMethod(),
        ]);
    }

    // private function createLowStockScenarios(Organization $org, Branch $branch): void
    // {
    //     // Create critical low stock items
    //     $criticalItems = ['Salt', 'Olive Oil', 'Chicken Breast', 'Rice'];
        
    //     foreach ($criticalItems as $itemName) {
    //         $itemMaster = ItemMaster::where('organization_id', $org->id)
    //             ->where('name', $itemName)
    //             ->first();
                
    //         if ($itemMaster) {
    //             $inventoryItem = InventoryItem::where('item_master_id', $itemMaster->id)
    //                 ->where('branch_id', $branch->id)
    //                 ->first();
                    
    //             if ($inventoryItem) {
    //                 $inventoryItem->update([
    //                     'current_stock' => rand(1, $itemMaster->reorder_level - 1),
    //                     'stock_status' => 'critical_low',
    //                     'low_stock_alert_sent' => true,
    //                     'last_low_stock_alert' => Carbon::now()->subHours(rand(1, 24)),
    //                 ]);
                    
    //                 // Create stock movement for consumption
    //                 $this->createStockMovement($inventoryItem, 'consumption', rand(50, 200), 'High usage during peak hours');
    //             }
    //         }
    //     }

    //     // Create out-of-stock scenarios
    //     $outOfStockItems = ['Salmon Fillet', 'Wine'];
        
    //     foreach ($outOfStockItems as $itemName) {
    //         $itemMaster = ItemMaster::where('organization_id', $org->id)
    //             ->where('name', $itemName)
    //             ->first();
                
    //         if ($itemMaster) {
    //             $inventoryItem = InventoryItem::where('item_master_id', $itemMaster->id)
    //                 ->where('branch_id', $branch->id)
    //                 ->first();
                    
    //             if ($inventoryItem) {
    //                 $inventoryItem->update([
    //                     'current_stock' => 0,
    //                     'stock_status' => 'out_of_stock',
    //                     'out_of_stock_since' => Carbon::now()->subDays(rand(1, 5)),
    //                     'emergency_order_placed' => true,
    //                 ]);
    //             }
    //         }
    //     }
    // }

    // private function createInventoryAdjustments(Organization $org, Branch $branch): void
    // {
    //     $adjustmentTypes = [
    //         'damage' => 'Damaged goods due to handling',
    //         'theft' => 'Stock theft detected during audit',
    //         'waste' => 'Expired items disposed',
    //         'spillage' => 'Product spilled during handling',
    //         'correction' => 'Inventory count correction',
    //         'promotion_sample' => 'Used for customer sampling',
    //         'staff_consumption' => 'Staff meal consumption',
    //     ];

    //     foreach ($adjustmentTypes as $type => $reason) {
    //         for ($i = 0; $i < rand(2, 5); $i++) {
    //             $inventoryItem = InventoryItem::where('branch_id', $branch->id)
    //                 ->where('current_stock', '>', 10)
    //                 ->inRandomOrder()
    //                 ->first();
                    
    //             if ($inventoryItem) {
    //                 $adjustmentQuantity = rand(1, min(20, $inventoryItem->current_stock / 2));
                    
    //                 InventoryAdjustment::create([
    //                     'organization_id' => $org->id,
    //                     'branch_id' => $branch->id,
    //                     'inventory_item_id' => $inventoryItem->id,
    //                     'adjustment_type' => $type,
    //                     'quantity_adjusted' => -$adjustmentQuantity,
    //                     'reason' => $reason,
    //                     'reference_number' => $this->generateAdjustmentNumber($branch),
    //                     'adjusted_by' => $this->getRandomUser($org)?->id,
    //                     'adjustment_date' => Carbon::now()->subDays(rand(1, 10)),
    //                     'unit_cost' => $inventoryItem->unit_cost,
    //                     'total_value_impact' => -($adjustmentQuantity * $inventoryItem->unit_cost),
    //                     'approved_by' => $this->getRandomManager($org)?->id,
    //                     'notes' => "Inventory adjustment due to {$reason}",
    //                 ]);
                    
    //                 // Update inventory item
    //                 $inventoryItem->update([
    //                     'current_stock' => $inventoryItem->current_stock - $adjustmentQuantity,
    //                     'last_adjustment_date' => Carbon::now(),
    //                 ]);
                    
    //                 // Create stock movement
    //                 $this->createStockMovement($inventoryItem, 'adjustment', -$adjustmentQuantity, $reason);
    //             }
    //         }
    //     }
    // }

    // private function createSupplierDeliveries(Organization $org, Branch $branch): void
    // {
    //     $deliveryScenarios = [
    //         'on_time_full' => 'Complete delivery on schedule',
    //         'late_full' => 'Complete delivery but delayed',
    //         'on_time_partial' => 'Partial delivery on time',
    //         'late_partial' => 'Partial delivery and delayed',
    //         'damaged_goods' => 'Delivery with damaged items',
    //         'wrong_items' => 'Delivery with incorrect items',
    //         'overage' => 'Delivery with extra quantities',
    //         'quality_issues' => 'Delivery with quality concerns',
    //     ];

    //     foreach ($deliveryScenarios as $scenario => $description) {
    //         $supplier = $this->getRandomSupplier($org);
    //         if (!$supplier) continue;
            
    //         // Create purchase order
    //         $po = PurchaseOrder::create([
    //             'organization_id' => $org->id,
    //             'branch_id' => $branch->id,
    //             'supplier_id' => $supplier->id,
    //             'po_number' => $this->generatePONumber($branch),
    //             'order_date' => Carbon::now()->subDays(rand(1, 14)),
    //             'expected_delivery_date' => Carbon::now()->subDays(rand(0, 7)),
    //             'status' => $this->getPOStatus($scenario),
    //             'total_amount' => 0, // Will be calculated
    //             'notes' => "PO for scenario: {$scenario}",
    //         ]);
            
    //         // Add items to PO and create delivery scenario
    //         $this->addItemsToPO($po, $branch, $scenario);
    //         $this->createGRNForDelivery($po, $scenario, $description);
    //     }
    // }

    // private function createInterBranchTransfers(Organization $org, Branch $branch): void
    // {
    //     $otherBranches = $org->branches->where('id', '!=', $branch->id);
        
    //     if ($otherBranches->isEmpty()) return;
        
    //     $transferTypes = [
    //         'emergency' => 'Emergency stock transfer',
    //         'routine' => 'Routine inventory balancing',
    //         'seasonal' => 'Seasonal stock redistribution',
    //         'promotion' => 'Transfer for promotional event',
    //         'excess_stock' => 'Transfer of excess inventory',
    //     ];

    //     foreach ($transferTypes as $type => $description) {
    //         for ($i = 0; $i < rand(1, 3); $i++) {
    //             $targetBranch = $otherBranches->random();
                
    //             $transfer = GoodsTransferNote::create([
    //                 'organization_id' => $org->id,
    //                 'from_branch_id' => $branch->id,
    //                 'to_branch_id' => $targetBranch->id,
    //                 'transfer_number' => $this->generateTransferNumber($branch),
    //                 'transfer_date' => Carbon::now()->subDays(rand(1, 7)),
    //                 'transfer_type' => $type,
    //                 'status' => $this->getTransferStatus(),
    //                 'notes' => $description,
    //                 'requested_by' => $this->getRandomUser($org)?->id,
    //                 'approved_by' => $this->getRandomManager($org)?->id,
    //             ]);
                
    //             $this->addItemsToTransfer($transfer, $branch);
    //         }
    //     }
    // }

    private function createBatchTrackingScenarios(Organization $org, Branch $branch): void
    {
        $perishableItems = InventoryItem::where('branch_id', $branch->id)
            ->whereNotNull('expiry_date')
            ->get();
            
        foreach ($perishableItems as $item) {
            // Create multiple batches with different expiry dates
            for ($i = 0; $i < rand(2, 4); $i++) {
                $batchQuantity = rand(10, 50);
                
                InventoryItem::create([
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                    'item_master_id' => $item->item_master_id,
                    'current_stock' => $batchQuantity,
                    'batch_number' => $this->generateBatchNumber(),
                    'expiry_date' => Carbon::now()->addDays(rand(7, 90)),
                    'unit_cost' => $item->unit_cost + (rand(-50, 50) / 100), // Price variations
                    'received_date' => Carbon::now()->subDays(rand(1, 30)),
                    'supplier_batch_number' => 'SUP-' . rand(1000, 9999),
                    'quality_check_status' => $this->getQualityStatus(),
                    'storage_conditions' => $this->getStorageConditions(),
                ]);
            }
            
            // Create near-expiry alerts
            if (rand(0, 1)) {
                $nearExpiryDate = Carbon::now()->addDays(rand(1, 7));
                $item->update([
                    'expiry_date' => $nearExpiryDate,
                    'near_expiry_alert' => true,
                    'expiry_alert_sent' => Carbon::now()->subDays(rand(1, 3)),
                ]);
            }
        }
    }

    // private function createSeasonalFluctuations(Organization $org, Branch $branch): void
    // {
    //     $seasonalItems = [
    //         'summer' => ['Ice Cream', 'Cold Beverages', 'Salad Ingredients'],
    //         'winter' => ['Hot Beverages', 'Soup Ingredients', 'Comfort Food Items'],
    //         'holiday' => ['Special Wines', 'Premium Ingredients', 'Festive Decorations'],
    //         'tourist_season' => ['Local Specialties', 'Exotic Ingredients', 'Tourist Favorites'],
    //     ];

    //     $currentSeason = $this->getCurrentSeason();
    //     $seasonItems = $seasonalItems[$currentSeason] ?? [];

    //     foreach ($seasonItems as $itemName) {
    //         $inventoryItem = InventoryItem::whereHas('itemMaster', function($query) use ($itemName) {
    //             $query->where('name', 'LIKE', "%{$itemName}%");
    //         })->where('branch_id', $branch->id)->first();

    //         if ($inventoryItem) {
    //             // Increase stock for seasonal items
    //             $seasonalIncrease = rand(50, 200);
    //             $inventoryItem->update([
    //                 'current_stock' => $inventoryItem->current_stock + $seasonalIncrease,
    //                 'seasonal_stock_increase' => $seasonalIncrease,
    //                 'seasonal_period' => $currentSeason,
    //                 'max_stock_level' => $inventoryItem->max_stock_level + $seasonalIncrease,
    //             ]);

    //             $this->createStockMovement($inventoryItem, 'seasonal_adjustment', $seasonalIncrease, "Seasonal increase for {$currentSeason}");
    //         }
    //     }
    // }

    private function createEmergencyReplenishment(Organization $org, Branch $branch): void
    {
        $emergencyItems = ['Salt', 'Cooking Oil', 'Chicken', 'Rice'];
        
        foreach ($emergencyItems as $itemName) {
            $inventoryItem = InventoryItem::whereHas('itemMaster', function($query) use ($itemName) {
                $query->where('name', 'LIKE', "%{$itemName}%");
            })->where('branch_id', $branch->id)->first();

            if ($inventoryItem && $inventoryItem->current_stock < $inventoryItem->reorder_level) {
                // Create emergency purchase order
                $emergencyPO = PurchaseOrder::create([
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                    'supplier_id' => $this->getRandomSupplier($org)?->id,
                    'po_number' => 'EMRG-' . $this->generatePONumber($branch),
                    'order_date' => Carbon::now(),
                    'expected_delivery_date' => Carbon::now()->addHours(rand(2, 24)),
                    'priority' => 'emergency',
                    'status' => 'urgent',
                    'rush_delivery_fee' => rand(500, 2000) / 100,
                    'notes' => "Emergency replenishment for {$itemName} - Critical stock shortage",
                ]);

                // Add emergency quantity
                $emergencyQuantity = $inventoryItem->max_stock_level - $inventoryItem->current_stock;
                
                PurchaseOrderItem::create([
                    'purchase_order_id' => $emergencyPO->id,
                    'item_master_id' => $inventoryItem->item_master_id,
                    'quantity_ordered' => $emergencyQuantity,
                    'unit_price' => $inventoryItem->unit_cost * 1.2, // Emergency pricing
                    'total_price' => $emergencyQuantity * $inventoryItem->unit_cost * 1.2,
                    'urgency_level' => 'critical',
                ]);
            }
        }
    }

    // private function createInventoryAudits(Organization $org, Branch $branch): void
    // {
    //     // Create different types of audits
    //     $auditTypes = [
    //         'monthly_cycle' => 'Regular monthly inventory count',
    //         'spot_check' => 'Random spot check audit',
    //         'pre_delivery' => 'Pre-delivery verification audit',
    //         'year_end' => 'Year-end comprehensive audit',
    //         'discrepancy_investigation' => 'Audit to investigate discrepancies',
    //     ];

    //     foreach ($auditTypes as $type => $description) {
    //         $auditDate = Carbon::now()->subDays(rand(1, 30));
            
    //         $audit = InventoryAudit::create([
    //             'organization_id' => $org->id,
    //             'branch_id' => $branch->id,
    //             'audit_number' => $this->generateAuditNumber($branch),
    //             'audit_type' => $type,
    //             'audit_date' => $auditDate,
    //             'status' => $this->getAuditStatus(),
    //             'audited_by' => $this->getRandomUser($org)?->id,
    //             'supervisor' => $this->getRandomManager($org)?->id,
    //             'notes' => $description,
    //         ]);

    //         // Create audit line items with discrepancies
    //         $auditItems = InventoryItem::where('branch_id', $branch->id)
    //             ->inRandomOrder()
    //             ->take(rand(5, 15))
    //             ->get();

    //         foreach ($auditItems as $item) {
    //             $systemCount = $item->current_stock;
    //             $physicalCount = $this->getPhysicalCount($systemCount);
    //             $variance = $physicalCount - $systemCount;

    //             InventoryAuditItem::create([
    //                 'inventory_audit_id' => $audit->id,
    //                 'inventory_item_id' => $item->id,
    //                 'system_count' => $systemCount,
    //                 'physical_count' => $physicalCount,
    //                 'variance' => $variance,
    //                 'variance_percentage' => $systemCount > 0 ? ($variance / $systemCount) * 100 : 0,
    //                 'variance_value' => $variance * $item->unit_cost,
    //                 'explanation' => $this->getVarianceExplanation($variance),
    //                 'action_required' => abs($variance) > 5,
    //             ]);

    //             // Update inventory if variance is significant
    //             if (abs($variance) > 2) {
    //                 $item->update(['current_stock' => $physicalCount]);
    //                 $this->createStockMovement($item, 'audit_adjustment', $variance, "Audit adjustment - {$type}");
    //             }
    //         }
    //     }
    // }

    private function createAutomatedReorderScenarios(Organization $org, Branch $branch): void
    {
        $inventoryItems = InventoryItem::where('branch_id', $branch->id)->get();

        foreach ($inventoryItems as $item) {
            if ($item->current_stock <= $item->reorder_level) {
                // Create automatic reorder
                $reorderQuantity = $item->max_stock_level - $item->current_stock;
                
                $autoPO = PurchaseOrder::create([
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                    'supplier_id' => $this->getRandomSupplier($org)?->id,
                    'po_number' => 'AUTO-' . $this->generatePONumber($branch),
                    'order_date' => Carbon::now(),
                    'expected_delivery_date' => Carbon::now()->addDays(rand(2, 7)),
                    'status' => 'pending',
                    'auto_generated' => true,
                    'reorder_trigger' => 'low_stock_alert',
                    'notes' => "Automatically generated reorder for {$item->itemMaster->name}",
                ]);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $autoPO->id,
                    'item_master_id' => $item->item_master_id,
                    'quantity_ordered' => $reorderQuantity,
                    'unit_price' => $item->unit_cost,
                    'total_price' => $reorderQuantity * $item->unit_cost,
                    'auto_calculated' => true,
                ]);

                // Update item with reorder information
                $item->update([
                    'last_reorder_date' => Carbon::now(),
                    'auto_reorder_triggered' => true,
                    'expected_restock_date' => Carbon::now()->addDays(rand(2, 7)),
                ]);
            }
        }
    }

    // // Helper methods for creating related records
    // private function createStockMovement(InventoryItem $item, string $type, int $quantity, string $reason): void
    // {
    //     StockMovement::create([
    //         'organization_id' => $item->organization_id,
    //         'branch_id' => $item->branch_id,
    //         'inventory_item_id' => $item->id,
    //         'movement_type' => $type,
    //         'quantity' => $quantity,
    //         'unit_cost' => $item->unit_cost,
    //         'total_value' => $quantity * $item->unit_cost,
    //         'reference_type' => $this->getMovementReferenceType($type),
    //         'reference_id' => rand(1, 100),
    //         'reason' => $reason,
    //         'movement_date' => Carbon::now(),
    //         'created_by' => $this->getRandomUser($item->organization)?->id,
    //     ]);
    // }

    // Helper methods for data generation
    private function generateSKU(string $itemName): string
    {
        return strtoupper(substr(str_replace(' ', '', $itemName), 0, 3)) . rand(1000, 9999);
    }

    private function getUnitOfMeasure(string $itemName): string
    {
        $units = [
            'kg', 'g', 'ltr', 'ml', 'pcs', 'box', 'dozen', 'bottle', 'can', 'pack'
        ];
        return $units[array_rand($units)];
    }

    private function getReorderLevel(string $category): int
    {
        $levels = [
            'fresh_produce' => rand(20, 50),
            'proteins' => rand(15, 30),
            'pantry_items' => rand(50, 100),
            'beverages' => rand(10, 25),
            'supplies' => rand(25, 50),
        ];
        return $levels[$category] ?? 30;
    }

    private function getMaxStockLevel(string $category): int
    {
        return $this->getReorderLevel($category) * rand(3, 6);
    }

    private function getCostPrice(string $itemName): float
    {
        return rand(100, 5000) / 100; // $1.00 to $50.00
    }

    private function getInitialStock(string $category): int
    {
        $stockLevels = [
            'fresh_produce' => rand(50, 200),
            'proteins' => rand(30, 100),
            'pantry_items' => rand(100, 500),
            'beverages' => rand(25, 150),
            'supplies' => rand(50, 300),
        ];
        return $stockLevels[$category] ?? 100;
    }

    private function getExpiryDate(string $category): ?Carbon
    {
        $perishableCategories = ['fresh_produce', 'proteins'];
        
        if (in_array($category, $perishableCategories)) {
            return Carbon::now()->addDays(rand(3, 30));
        }
        
        return null;
    }

    private function generateBatchNumber(): string
    {
        return 'BTH' . date('Ymd') . rand(100, 999);
    }

    private function getStorageLocation(string $category): string
    {
        $locations = [
            'fresh_produce' => 'Cold Storage A',
            'proteins' => 'Freezer Section',
            'pantry_items' => 'Dry Storage',
            'beverages' => 'Beverage Cooler',
            'supplies' => 'Storage Room',
        ];
        return $locations[$category] ?? 'General Storage';
    }

    private function getValuationMethod(): string
    {
        return ['FIFO', 'LIFO', 'Weighted Average'][array_rand(['FIFO', 'LIFO', 'Weighted Average'])];
    }

    private function getRandomSupplier(Organization $org): ?Supplier
    {
        return $org->suppliers->random();
    }

    private function getRandomUser(Organization $org): ?User
    {
        return $org->users->random();
    }

    private function getRandomManager(Organization $org): ?User
    {
        return $org->users->where('is_admin', true)->random();
    }

    private function getCurrentSeason(): string
    {
        $month = date('n');
        
        if (in_array($month, [12, 1, 2])) return 'summer';
        if (in_array($month, [3, 4, 5])) return 'winter';
        if (in_array($month, [6, 7, 8])) return 'holiday';
        
        return 'tourist_season';
    }

    private function generateAdjustmentNumber(Branch $branch): string
    {
        return 'ADJ-' . $branch->id . '-' . date('Ymd') . '-' . rand(100, 999);
    }

    private function generatePONumber(Branch $branch): string
    {
        return 'PO-' . $branch->id . '-' . date('Ymd') . '-' . rand(100, 999);
    }

    private function generateTransferNumber(Branch $branch): string
    {
        return 'TRF-' . $branch->id . '-' . date('Ymd') . '-' . rand(100, 999);
    }

    private function generateAuditNumber(Branch $branch): string
    {
        return 'AUD-' . $branch->id . '-' . date('Ymd') . '-' . rand(100, 999);
    }

    private function getPOStatus(string $scenario): string
    {
        $statuses = [
            'on_time_full' => 'completed',
            'late_full' => 'completed',
            'on_time_partial' => 'partial',
            'late_partial' => 'partial',
            'damaged_goods' => 'issues',
            'wrong_items' => 'disputed',
            'overage' => 'completed',
            'quality_issues' => 'quality_check',
        ];
        
        return $statuses[$scenario] ?? 'pending';
    }

    private function getTransferStatus(): string
    {
        return ['pending', 'in_transit', 'received', 'partial'][array_rand(['pending', 'in_transit', 'received', 'partial'])];
    }

    private function getQualityStatus(): string
    {
        return ['passed', 'failed', 'conditional', 'pending'][array_rand(['passed', 'failed', 'conditional', 'pending'])];
    }

    private function getStorageConditions(): string
    {
        return ['Refrigerated', 'Frozen', 'Room Temperature', 'Controlled Humidity'][array_rand(['Refrigerated', 'Frozen', 'Room Temperature', 'Controlled Humidity'])];
    }

    private function getAuditStatus(): string
    {
        return ['completed', 'in_progress', 'discrepancies_found', 'approved'][array_rand(['completed', 'in_progress', 'discrepancies_found', 'approved'])];
    }

    private function getPhysicalCount(int $systemCount): int
    {
        // Simulate realistic counting discrepancies
        $variance = rand(-5, 5);
        $percentageVariance = rand(-10, 10) / 100;
        
        return max(0, $systemCount + $variance + ($systemCount * $percentageVariance));
    }

    private function getVarianceExplanation(int $variance): string
    {
        if ($variance > 0) {
            return 'Found additional stock - possible receiving not recorded';
        } elseif ($variance < 0) {
            return 'Missing stock - possible consumption not recorded';
        }
        
        return 'Stock count matches system';
    }

    private function getMovementReferenceType(string $type): string
    {
        $types = [
            'consumption' => 'order',
            'adjustment' => 'adjustment',
            'seasonal_adjustment' => 'seasonal',
            'audit_adjustment' => 'audit',
        ];
        
        return $types[$type] ?? 'general';
    }

    private function addItemsToPO(PurchaseOrder $po, Branch $branch, string $scenario): void
    {
        $items = InventoryItem::where('branch_id', $branch->id)
            ->inRandomOrder()
            ->take(rand(3, 8))
            ->get();
            
        $totalAmount = 0;
        
        foreach ($items as $item) {
            $quantity = $this->getScenarioQuantity($scenario, rand(20, 100));
            $unitPrice = $item->unit_cost * (1 + rand(-10, 20) / 100); // Price variance
            
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'item_master_id' => $item->item_master_id,
                'quantity_ordered' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $quantity * $unitPrice,
            ]);
            
            $totalAmount += $quantity * $unitPrice;
        }
        
        $po->update(['total_amount' => $totalAmount]);
    }

    private function getScenarioQuantity(string $scenario, int $baseQuantity): int
    {
        switch ($scenario) {
            case 'on_time_partial':
            case 'late_partial':
                return round($baseQuantity * 0.6); // 60% delivery
            case 'overage':
                return round($baseQuantity * 1.2); // 120% delivery
            default:
                return $baseQuantity;
        }
    }

    private function createGRNForDelivery(PurchaseOrder $po, string $scenario, string $description): void
    {
        $deliveryDate = $this->getDeliveryDate($scenario, $po->expected_delivery_date);
        
        $grn = GrnMaster::create([
            'organization_id' => $po->organization_id,
            'branch_id' => $po->branch_id,
            'purchase_order_id' => $po->id,
            'supplier_id' => $po->supplier_id,
            'grn_number' => $this->generateGRNNumber($po->branch),
            'delivery_date' => $deliveryDate,
            'received_by' => $this->getRandomUser($po->organization)?->id,
            'status' => $this->getGRNStatus($scenario),
            'delivery_note_number' => 'DN-' . rand(10000, 99999),
            'vehicle_number' => $this->generateVehicleNumber(),
            'driver_name' => $this->getRandomDriverName(),
            'notes' => $description,
        ]);
        
        foreach ($po->items as $poItem) {
            $receivedQuantity = $this->getReceivedQuantity($scenario, $poItem->quantity_ordered);
            $qualityStatus = $this->getDeliveryQualityStatus($scenario);
            
            GrnItem::create([
                'grn_master_id' => $grn->id,
                'purchase_order_item_id' => $poItem->id,
                'item_master_id' => $poItem->item_master_id,
                'quantity_ordered' => $poItem->quantity_ordered,
                'quantity_received' => $receivedQuantity,
                'quantity_accepted' => $qualityStatus === 'accepted' ? $receivedQuantity : round($receivedQuantity * 0.8),
                'quantity_rejected' => $qualityStatus === 'rejected' ? round($receivedQuantity * 0.2) : 0,
                'unit_price' => $poItem->unit_price,
                'total_value' => $receivedQuantity * $poItem->unit_price,
                'quality_status' => $qualityStatus,
                'expiry_date' => Carbon::now()->addDays(rand(30, 180)),
                'batch_number' => $this->generateBatchNumber(),
                'notes' => $this->getGRNItemNotes($scenario),
            ]);
        }
    }

    // private function addItemsToTransfer(GoodsTransferNote $transfer, Branch $fromBranch): void
    // {
    //     $items = InventoryItem::where('branch_id', $fromBranch->id)
    //         ->where('current_stock', '>', 10)
    //         ->inRandomOrder()
    //         ->take(rand(2, 5))
    //         ->get();
            
    //     foreach ($items as $item) {
    //         $transferQuantity = rand(5, min(20, $item->current_stock / 2));
            
    //         GoodsTransferItem::create([
    //             'goods_transfer_note_id' => $transfer->id,
    //             'item_master_id' => $item->item_master_id,
    //             'quantity_transferred' => $transferQuantity,
    //             'unit_cost' => $item->unit_cost,
    //             'total_value' => $transferQuantity * $item->unit_cost,
    //             'batch_number' => $item->batch_number,
    //             'expiry_date' => $item->expiry_date,
    //         ]);
            
    //         // Update source inventory
    //         $item->update(['current_stock' => $item->current_stock - $transferQuantity]);
            
    //         // Create stock movement
    //         $this->createStockMovement($item, 'transfer_out', -$transferQuantity, "Transfer to branch {$transfer->to_branch_id}");
    //     }
    // }

    private function getDeliveryDate(string $scenario, Carbon $expectedDate): Carbon
    {
        if (strpos($scenario, 'late') !== false) {
            return $expectedDate->copy()->addDays(rand(1, 5));
        }
        
        return $expectedDate;
    }

    private function getGRNStatus(string $scenario): string
    {
        $statuses = [
            'on_time_full' => 'completed',
            'late_full' => 'completed',
            'on_time_partial' => 'partial',
            'late_partial' => 'partial',
            'damaged_goods' => 'quality_issues',
            'wrong_items' => 'discrepancy',
            'overage' => 'overage',
            'quality_issues' => 'quality_check',
        ];
        
        return $statuses[$scenario] ?? 'pending';
    }

    private function getReceivedQuantity(string $scenario, int $orderedQuantity): int
    {
        switch ($scenario) {
            case 'on_time_partial':
            case 'late_partial':
                return round($orderedQuantity * (rand(50, 80) / 100));
            case 'overage':
                return round($orderedQuantity * (rand(110, 130) / 100));
            default:
                return $orderedQuantity;
        }
    }

    private function getDeliveryQualityStatus(string $scenario): string
    {
        if (strpos($scenario, 'damaged') !== false || strpos($scenario, 'quality') !== false) {
            return ['rejected', 'conditional', 'partial_accept'][array_rand(['rejected', 'conditional', 'partial_accept'])];
        }
        
        return 'accepted';
    }

    private function generateGRNNumber(Branch $branch): string
    {
        return 'GRN-' . $branch->id . '-' . date('Ymd') . '-' . rand(100, 999);
    }

    private function generateVehicleNumber(): string
    {
        return 'ABC-' . rand(1000, 9999);
    }

    private function getRandomDriverName(): string
    {
        $names = ['Sunil Perera', 'Kamal Silva', 'Nimal Fernando', 'Anil Kumara', 'Ranjith Mendis'];
        return $names[array_rand($names)];
    }

    private function getGRNItemNotes(string $scenario): string
    {
        $notes = [
            'damaged_goods' => 'Some items damaged during transport',
            'wrong_items' => 'Incorrect items received, different specifications',
            'quality_issues' => 'Quality concerns noted, items under review',
            'overage' => 'Received more than ordered quantity',
            'late_partial' => 'Partial delivery due to supply chain issues',
        ];
        
        return $notes[$scenario] ?? 'Standard delivery received';
    }
}
