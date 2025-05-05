<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\InventoryStock;
use App\Models\Branch;

class InventoryStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = InventoryItem::all();
        $branches = Branch::all();
        
        foreach ($branches as $branch) {
            foreach ($items as $item) {
                // Set random quantities appropriate to the item type
                $baseQuantity = 0;
                
                // Determine base quantity based on unit of measurement
                switch ($item->unit_of_measurement) {
                    case 'kg':
                        $baseQuantity = rand(15, 50);
                        break;
                    case 'g':
                        $baseQuantity = rand(1000, 5000);
                        break;
                    case 'ltr':
                        $baseQuantity = rand(10, 30);
                        break;
                    case 'ml':
                        $baseQuantity = rand(1000, 5000);
                        break;
                    case 'pcs':
                    case 'box':
                    case 'pack':
                    case 'bottle':
                    case 'can':
                        $baseQuantity = rand(20, 100);
                        break;
                    default:
                        $baseQuantity = rand(10, 50);
                }
                
                // Adjust quantity based on category
                if (str_contains($item->category->name, 'Beverages')) {
                    $baseQuantity *= 2; // Higher stock for beverages
                } elseif (str_contains($item->category->name, 'Packaging')) {
                    $baseQuantity *= 3; // Even higher stock for packaging items
                } elseif (str_contains($item->category->name, 'Cleaning')) {
                    $baseQuantity *= 0.5; // Lower stock for cleaning supplies
                }
                
                // For some branches, set lower stock levels
                $branchMultiplier = 1.0;
                if ($branch->id > 1) {
                    $branchMultiplier = 0.7; // Non-main branches have 70% of main branch stock
                }
                
                $currentQuantity = $baseQuantity * $branchMultiplier;
                
                // Set some items below reorder level for demo purposes
                if (rand(1, 10) > 8) {
                    $currentQuantity = $item->reorder_level * (rand(1, 50) / 100); // 1% to 50% of reorder level
                }
                
                // Create stock record
                InventoryStock::create([
                    'branch_id' => $branch->id,
                    'inventory_item_id' => $item->id,
                    'current_quantity' => $currentQuantity,
                    'committed_quantity' => rand(0, 10) > 8 ? rand(1, 5) : 0, // Occasionally set some committed stock
                    'available_quantity' => $currentQuantity, // This will be updated by the model's hooks
                    'is_active' => true,
                ]);
            }
        }

        // Update available quantities
        $stocks = InventoryStock::all();
        foreach ($stocks as $stock) {
            $stock->available_quantity = max(0, $stock->current_quantity - $stock->committed_quantity);
            $stock->save();
        }

        $this->command->info('Inventory stock seeded successfully!');
    }
} 