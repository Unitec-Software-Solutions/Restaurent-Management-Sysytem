<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Organizations, Branch, ItemMaster, ItemTransaction};
use Illuminate\Support\Str;
use Carbon\Carbon;

class ItemTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->toArray();
        $orgs = Organizations::pluck('id')->toArray();
        $branches = Branch::pluck('id')->toArray();
        $items = ItemMaster::with('category')->get();

        if (empty($users) || empty($orgs) || empty($branches) || $items->isEmpty()) {
            $this->command->warn('Missing related data: cannot seed item transactions.');
            return;
        }

        $inventory = [];
        $now = Carbon::now();
        $startDate = $now->copy()->subMonths(3); // Seed data for last 3 months

        // Seed initial stock (1 month before start date)
        foreach ($branches as $branchId) {
            foreach ($items as $item) {
                $initial = $this->getInitialStockQuantity($item);
                $inventory[$branchId][$item->id] = $initial;

                ItemTransaction::create([
                    'organization_id'        => $orgs[array_rand($orgs)],
                    'branch_id'              => $branchId,
                    'inventory_item_id'      => $item->id,
                    'transaction_type'       => 'purchase_order',
                    'transfer_to_branch_id'  => null,
                    'receiver_user_id'       => $users[array_rand($users)],
                    'quantity'               => $initial,
                    'received_quantity'      => $initial,
                    'damaged_quantity'       => 0,
                    'cost_price'             => $item->buying_price,
                    'unit_price'             => $item->selling_price,
                    'source_id'              => null,
                    'source_type'            => 'InitialStock',
                    'created_by_user_id'     => $users[array_rand($users)],
                    'notes'                  => 'Initial stock setup',
                    'is_active'              => true,
                    'created_at'             => $startDate->copy()->subMonth(),
                    'updated_at'             => $startDate->copy()->subMonth(),
                ]);
            }
        }

        $transactionCount = 0;
        $totalDays = $startDate->diffInDays($now);
        
        // Create transactions spread over time
        for ($day = 0; $day <= $totalDays; $day++) {
            $date = $startDate->copy()->addDays($day);
            
            // Restaurant is open 7 days a week with varying activity
            $isWeekend = $date->isWeekend();
            $isHoliday = $this->isHoliday($date);
            
            // Base number of sales transactions per day
            $dailySales = $isHoliday ? rand(15, 25) : 
                         ($isWeekend ? rand(10, 20) : rand(5, 15));
            
            // Create sales transactions
            for ($i = 0; $i < $dailySales; $i++) {
                $branchId = $branches[array_rand($branches)];
                $item = $this->getRandomMenuItem($items);
                
                if (!$item) continue;
                
                $maxStock = $inventory[$branchId][$item->id] ?? 0;
                
                // Skip if no stock
                if ($maxStock < 1) continue;
                
                // Determine sale quantity based on item type and stock
                $qty = $this->getSaleQuantity($item, $maxStock, $isWeekend, $isHoliday);
                
                // Process the sale
                $inventory[$branchId][$item->id] -= $qty;
                
                ItemTransaction::create([
                    'organization_id'        => $orgs[array_rand($orgs)],
                    'branch_id'              => $branchId,
                    'inventory_item_id'      => $item->id,
                    'transaction_type'       => 'sales_order',
                    'transfer_to_branch_id'  => null,
                    'receiver_user_id'       => $users[array_rand($users)],
                    'quantity'               => $qty,
                    'received_quantity'      => $qty,
                    'damaged_quantity'       => 0,
                    'cost_price'             => $item->buying_price,
                    'unit_price'             => $item->selling_price,
                    'source_id'              => $transactionCount + 1,
                    'source_type'            => 'SalesOrder',
                    'created_by_user_id'     => $users[array_rand($users)],
                    'notes'                  => "Sold $qty {$item->unit_of_measurement} of {$item->name}",
                    'is_active'              => true,
                    'created_at'             => $date,
                    'updated_at'             => $date,
                ]);

                $transactionCount++;
            }
            
            // Create occasional purchase orders (only 20% chance daily)
            if (rand(1, 5) === 1) {
                $branchId = $branches[array_rand($branches)];
                $item = $items->random();
                $currentStock = $inventory[$branchId][$item->id] ?? 0;
                
                // Only order if stock is below 50% of initial level
                $initial = $this->getInitialStockQuantity($item);
                if ($currentStock > ($initial * 0.5)) continue;
                
                $qty = $this->getPurchaseQuantity($item);
                $received = $qty - ($item->is_perishable ? rand(0, 2) : 0);
                $damaged = $qty - $received;
                
                $inventory[$branchId][$item->id] += $received;
                
                ItemTransaction::create([
                    'organization_id'        => $orgs[array_rand($orgs)],
                    'branch_id'              => $branchId,
                    'inventory_item_id'      => $item->id,
                    'transaction_type'       => 'purchase_order',
                    'transfer_to_branch_id'  => null,
                    'receiver_user_id'       => $users[array_rand($users)],
                    'quantity'               => $qty,
                    'received_quantity'      => $received,
                    'damaged_quantity'       => $damaged,
                    'cost_price'             => $item->buying_price,
                    'unit_price'             => $item->selling_price,
                    'source_id'              => $transactionCount + 1,
                    'source_type'            => 'PurchaseOrder',
                    'created_by_user_id'     => $users[array_rand($users)],
                    'notes'                  => "Purchased $qty {$item->unit_of_measurement} of {$item->name}",
                    'is_active'              => true,
                    'created_at'             => $date,
                    'updated_at'             => $date,
                ]);

                $transactionCount++;
            }
            
            // Monthly inventory audit (on 1st of month)
            if ($date->day === 1) {
                foreach ($branches as $branchId) {
                    foreach ($items as $item) {
                        $currentStock = $inventory[$branchId][$item->id] ?? 0;
                        
                        ItemTransaction::create([
                            'organization_id'        => $orgs[array_rand($orgs)],
                            'branch_id'              => $branchId,
                            'inventory_item_id'      => $item->id,
                            'transaction_type'       => 'audit',
                            'transfer_to_branch_id'  => null,
                            'receiver_user_id'       => $users[array_rand($users)],
                            'quantity'               => $currentStock,
                            'received_quantity'      => $currentStock,
                            'damaged_quantity'       => 0,
                            'cost_price'             => $item->buying_price,
                            'unit_price'             => $item->selling_price,
                            'source_id'              => $transactionCount + 1,
                            'source_type'            => 'InventoryAudit',
                            'created_by_user_id'     => $users[array_rand($users)],
                            'notes'                  => "Monthly audit - {$item->name} count verified",
                            'is_active'              => true,
                            'created_at'             => $date,
                            'updated_at'             => $date,
                        ]);

                        $transactionCount++;
                    }
                }
            }
        }

        $this->command->info('  Total Item transactions in the database: ' . ItemTransaction::count());
        
        // Calculate total stock value
        $totalValue = 0;
        foreach ($inventory as $branchId => $branchItems) {
            foreach ($branchItems as $itemId => $quantity) {
                $item = $items->firstWhere('id', $itemId);
                $totalValue += $quantity * $item->buying_price;
            }
        }
        
        $this->command->info('  Total Stock Value: Rs. ' . number_format($totalValue, 2));
        $this->command->info('  ✅ Item transactions seeded successfully.');
    }

    /**
     * Get random menu item (prefers items marked as is_menu_item)
     */
    protected function getRandomMenuItem($items)
    {
        $menuItems = $items->filter(function($item) {
            return $item->is_menu_item;
        });
        
        if ($menuItems->isEmpty()) {
            return $items->random();
        }
        
        // 80% chance to pick a menu item, 20% chance for other items
        return rand(1, 100) <= 80 ? $menuItems->random() : $items->random();
    }

    /**
     * Determine sale quantity based on item type and stock
     */
    protected function getSaleQuantity($item, $maxStock, $isWeekend, $isHoliday)
    {
        $baseQty = 1; // Most restaurant items are sold one at a time
        
        // Some items might be sold in larger quantities (like beverages)
        if (stripos($item->category->name ?? '', 'beverage') !== false) {
            $baseQty = rand(1, $isHoliday ? 3 : 2);
        }
        
        // Don't sell more than available
        return min($baseQty, $maxStock);
    }

    /**
     * Check if date is a holiday
     */
    protected function isHoliday(Carbon $date)
    {
        // Sample holidays - adjust as needed
        $holidays = [
            $date->copy()->startOfYear()->addDays(14), // Fake holiday 1
            $date->copy()->startOfYear()->addMonths(6)->addDays(5), // Fake holiday 2
            $date->copy()->endOfYear()->subDays(10), // Fake holiday 3
        ];
        
        foreach ($holidays as $holiday) {
            if ($date->isSameDay($holiday)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine reasonable initial stock quantity based on item type
     */
    protected function getInitialStockQuantity($item)
    {
        if ($item->category && stripos($item->category->name, 'beverage') !== false) {
            return rand(50, 100); // Higher quantity for beverages
        }
        
        if ($item->is_perishable) {
            return rand(10, 30); // Lower quantity for perishables
        }
        
        if (stripos($item->unit_of_measurement, 'kg') !== false) {
            return rand(5, 20); // Bulk items
        }
        
        return rand(20, 50); // Default quantity
    }

    /**
     * Determine reasonable purchase quantity based on item type
     */
    protected function getPurchaseQuantity($item)
    {
        if ($item->category && stripos($item->category->name, 'beverage') !== false) {
            return rand(20, 50); // Larger purchases for beverages
        }
        
        if ($item->is_perishable) {
            return rand(5, 15); // Smaller purchases for perishables
        }
        
        return rand(10, 30); // Default purchase quantity
    }
}