<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Events\LowStockAlert;
use App\Events\OutOfStockAlert;
use Illuminate\Support\Facades\Log;

class InventoryItemObserver
{
    /**
     * Handle the InventoryItem "updated" event.
     */
    public function updated(InventoryItem $inventoryItem): void
    {
        $this->checkStockLevels($inventoryItem);
    }

    /**
     * Handle the InventoryItem "created" event.
     */
    public function created(InventoryItem $inventoryItem): void
    {
        $this->checkStockLevels($inventoryItem);
    }

    /**
     * Check stock levels and trigger appropriate alerts
     */
    private function checkStockLevels(InventoryItem $inventoryItem): void
    {
        $itemMaster = $inventoryItem->itemMaster;
        
        if (!$itemMaster) {
            return;
        }

        $currentStock = $inventoryItem->current_stock;
        $reorderLevel = $itemMaster->reorder_level;
        $criticalLevel = $reorderLevel * 0.5; // 50% of reorder level is critical

        try {
            // Out of stock alert
            if ($currentStock <= 0) {
                event(new OutOfStockAlert($inventoryItem));
                Log::warning("Out of stock alert triggered", [
                    'item_id' => $itemMaster->id,
                    'item_name' => $itemMaster->name,
                    'branch_id' => $inventoryItem->branch_id,
                    'current_stock' => $currentStock
                ]);
            }
            // Critical low stock (50% of reorder level)
            elseif ($currentStock <= $criticalLevel) {
                event(new LowStockAlert($inventoryItem, 'critical'));
                Log::warning("Critical low stock alert triggered", [
                    'item_id' => $itemMaster->id,
                    'item_name' => $itemMaster->name,
                    'branch_id' => $inventoryItem->branch_id,
                    'current_stock' => $currentStock,
                    'critical_level' => $criticalLevel
                ]);
            }
            // Regular low stock (at reorder level)
            elseif ($currentStock <= $reorderLevel) {
                event(new LowStockAlert($inventoryItem, 'low'));
                Log::info("Low stock alert triggered", [
                    'item_id' => $itemMaster->id,
                    'item_name' => $itemMaster->name,
                    'branch_id' => $inventoryItem->branch_id,
                    'current_stock' => $currentStock,
                    'reorder_level' => $reorderLevel
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error triggering stock alerts", [
                'item_id' => $itemMaster->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle stock deduction when order items are processed
     */
    public function deductStock(InventoryItem $inventoryItem, int $quantity): bool
    {
        if ($inventoryItem->current_stock < $quantity) {
            Log::warning("Insufficient stock for deduction", [
                'item_id' => $inventoryItem->item_master_id,
                'requested' => $quantity,
                'available' => $inventoryItem->current_stock
            ]);
            return false;
        }

        $inventoryItem->current_stock -= $quantity;
        $inventoryItem->last_updated = now();
        $inventoryItem->save();

        Log::info("Stock deducted", [
            'item_id' => $inventoryItem->item_master_id,
            'quantity_deducted' => $quantity,
            'remaining_stock' => $inventoryItem->current_stock
        ]);

        return true;
    }

    /**
     * Handle stock addition (for GRN, returns, etc.)
     */
    public function addStock(InventoryItem $inventoryItem, int $quantity): void
    {
        $inventoryItem->current_stock += $quantity;
        $inventoryItem->last_updated = now();
        $inventoryItem->save();

        Log::info("Stock added", [
            'item_id' => $inventoryItem->item_master_id,
            'quantity_added' => $quantity,
            'new_stock' => $inventoryItem->current_stock
        ]);
    }
}
