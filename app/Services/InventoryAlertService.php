<?php

namespace App\Services;

use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\Branch;
use App\Events\LowStockAlert;
use App\Events\OutOfStockAlert;
use Illuminate\Support\Collection;

class InventoryAlertService
{
    const LOW_STOCK_THRESHOLD = 0.10; // 10% of par level
    const CRITICAL_STOCK_THRESHOLD = 0.05; // 5% of par level

    /**
     * Check all items for low stock alerts
     */
    public function checkAllItems(Branch $branch): Collection
    {
        $alerts = collect();
        
        $items = ItemMaster::where('organization_id', $branch->organization_id)
            ->where('is_active', true)
            ->whereNotNull('par_level')
            ->where('par_level', '>', 0)
            ->get();

        foreach ($items as $item) {
            $stockLevel = ItemTransaction::stockOnHand($item->id, $branch->id);
            $threshold = $item->par_level * self::LOW_STOCK_THRESHOLD;
            $criticalThreshold = $item->par_level * self::CRITICAL_STOCK_THRESHOLD;

            if ($stockLevel <= 0) {
                $alert = $this->createOutOfStockAlert($item, $branch, $stockLevel);
                $alerts->push($alert);
                event(new OutOfStockAlert($item, $branch, $stockLevel));
            } elseif ($stockLevel <= $criticalThreshold) {
                $alert = $this->createCriticalStockAlert($item, $branch, $stockLevel);
                $alerts->push($alert);
            } elseif ($stockLevel <= $threshold) {
                $alert = $this->createLowStockAlert($item, $branch, $stockLevel);
                $alerts->push($alert);
                event(new LowStockAlert($item, $branch, $stockLevel));
            }
        }

        return $alerts;
    }

    /**
     * Check specific item for stock level
     */
    public function checkItem(ItemMaster $item, Branch $branch): ?array
    {
        $stockLevel = ItemTransaction::stockOnHand($item->id, $branch->id);
        
        if (!$item->par_level || $item->par_level <= 0) {
            return null;
        }

        $threshold = $item->par_level * self::LOW_STOCK_THRESHOLD;
        $criticalThreshold = $item->par_level * self::CRITICAL_STOCK_THRESHOLD;

        if ($stockLevel <= 0) {
            return $this->createOutOfStockAlert($item, $branch, $stockLevel);
        } elseif ($stockLevel <= $criticalThreshold) {
            return $this->createCriticalStockAlert($item, $branch, $stockLevel);
        } elseif ($stockLevel <= $threshold) {
            return $this->createLowStockAlert($item, $branch, $stockLevel);
        }

        return null;
    }

    /**
     * Get real-time stock alerts for dashboard
     */
    public function getDashboardAlerts(Branch $branch): Collection
    {
        return $this->checkAllItems($branch)
            ->sortByDesc('priority')
            ->take(10);
    }

    /**
     * Check if item can fulfill order quantity
     */
    public function canFulfillOrder(ItemMaster $item, Branch $branch, int $quantity): array
    {
        $currentStock = ItemTransaction::stockOnHand($item->id, $branch->id);
        $canFulfill = $currentStock >= $quantity;
        
        $result = [
            'can_fulfill' => $canFulfill,
            'current_stock' => $currentStock,
            'requested_quantity' => $quantity,
            'remaining_after_order' => $currentStock - $quantity,
            'item_name' => $item->name
        ];

        if (!$canFulfill) {
            $result['shortage'] = $quantity - $currentStock;
            $result['alert_type'] = 'insufficient_stock';
        } elseif (($currentStock - $quantity) <= ($item->par_level * self::LOW_STOCK_THRESHOLD)) {
            $result['alert_type'] = 'will_trigger_low_stock';
            $result['warning'] = "This order will trigger low stock alert for {$item->name}";
        }

        return $result;
    }

    /**
     * Batch check for multiple items (for order validation)
     */
    public function batchCheckOrder(array $orderItems, Branch $branch): array
    {
        $results = [];
        $canProceed = true;
        $warnings = [];

        foreach ($orderItems as $orderItem) {
            $item = ItemMaster::find($orderItem['item_id']);
            if (!$item) continue;

            $check = $this->canFulfillOrder($item, $branch, $orderItem['quantity']);
            $results[] = $check;

            if (!$check['can_fulfill']) {
                $canProceed = false;
            } elseif (isset($check['alert_type']) && $check['alert_type'] === 'will_trigger_low_stock') {
                $warnings[] = $check['warning'];
            }
        }

        return [
            'can_proceed' => $canProceed,
            'warnings' => $warnings,
            'details' => $results
        ];
    }

    private function createLowStockAlert(ItemMaster $item, Branch $branch, float $stockLevel): array
    {
        return [
            'type' => 'low_stock',
            'priority' => 2,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'current_stock' => $stockLevel,
            'par_level' => $item->par_level,
            'threshold' => $item->par_level * self::LOW_STOCK_THRESHOLD,
            'percentage' => round(($stockLevel / $item->par_level) * 100, 1),
            'message' => "Low stock alert: {$item->name} at {$branch->name}",
            'suggested_action' => 'Reorder recommended',
            'alert_time' => now()
        ];
    }

    private function createCriticalStockAlert(ItemMaster $item, Branch $branch, float $stockLevel): array
    {
        return [
            'type' => 'critical_stock',
            'priority' => 3,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'current_stock' => $stockLevel,
            'par_level' => $item->par_level,
            'threshold' => $item->par_level * self::CRITICAL_STOCK_THRESHOLD,
            'percentage' => round(($stockLevel / $item->par_level) * 100, 1),
            'message' => "CRITICAL: Very low stock for {$item->name} at {$branch->name}",
            'suggested_action' => 'Immediate reorder required',
            'alert_time' => now()
        ];
    }

    private function createOutOfStockAlert(ItemMaster $item, Branch $branch, float $stockLevel): array
    {
        return [
            'type' => 'out_of_stock',
            'priority' => 4,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'current_stock' => $stockLevel,
            'par_level' => $item->par_level,
            'threshold' => 0,
            'percentage' => 0,
            'message' => "OUT OF STOCK: {$item->name} at {$branch->name}",
            'suggested_action' => 'Emergency reorder - Item unavailable',
            'alert_time' => now()
        ];
    }
}
