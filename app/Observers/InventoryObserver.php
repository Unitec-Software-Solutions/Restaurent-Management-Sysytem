<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Events\LowStockAlert;

class InventoryObserver
{
    /**
     * Handle the InventoryItem "updating" event.
     */
    public function updating(InventoryItem $inventory): void
    {
        // Check if quantity is being reduced
        if ($inventory->isDirty('quantity') && $inventory->quantity < $inventory->getOriginal('quantity')) {
            $this->checkLowStock($inventory);
        }
    }

    /**
     * Handle the InventoryItem "updated" event.
     */
    public function updated(InventoryItem $inventory): void
    {
        $this->checkLowStock($inventory);
    }

    /**
     * Check if inventory is below threshold and trigger alert
     */
    protected function checkLowStock(InventoryItem $inventory): void
    {
        // Get reorder level or calculate 10% of par level
        $threshold = $inventory->reorder_level ?? ($inventory->par_level * 0.1);
        
        if ($inventory->quantity <= $threshold && $inventory->quantity > 0) {
            event(new LowStockAlert($inventory));
        }
        
        // Critical stock alert when completely out
        if ($inventory->quantity <= 0) {
            event(new \App\Events\OutOfStockAlert($inventory));
        }
    }
}
