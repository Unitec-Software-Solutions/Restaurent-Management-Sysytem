<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ItemTransaction;
use App\Models\ItemMaster;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderService
{
    /**
     * Create order with stock validation and deduction
     */
    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Validate stock availability first
            $this->validateStockAvailability($data['items'], $data['branch_id']);

            // Create the order
            $order = Order::create([
                'branch_id' => $data['branch_id'],
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'order_type' => $data['order_type'] ?? Order::TYPE_TAKEAWAY_ONLINE,
                'steward_id' => $data['steward_id'] ?? null,
                'reservation_id' => $data['reservation_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => Order::STATUS_SUBMITTED
            ]);

            // Create order items and deduct stock
            $this->createOrderItems($order, $data['items']);
            
            // Calculate totals
            $order->calculateTotal();

            // Generate KOT
            $order->generateKOT();

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'total_items' => count($data['items'])
            ]);

            return $order;
        });
    }

    /**
     * Validate stock availability for all items
     */
    protected function validateStockAvailability(array $items, int $branchId)
    {
        foreach ($items as $item) {
            $itemMaster = ItemMaster::findOrFail($item['item_id']);
            $currentStock = ItemTransaction::stockOnHand($item['item_id'], $branchId);
            
            if ($currentStock < $item['quantity']) {
                throw new Exception(
                    "Insufficient stock for {$itemMaster->name}. Available: {$currentStock}, Required: {$item['quantity']}"
                );
            }
        }
    }

    /**
     * Create order items and deduct stock
     */
    protected function createOrderItems(Order $order, array $items)
    {
        foreach ($items as $itemData) {
            $itemMaster = ItemMaster::findOrFail($itemData['item_id']);
            
            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $itemData['item_id'],
                'inventory_item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemMaster->selling_price,
                'total_price' => $itemMaster->selling_price * $itemData['quantity']
            ]);

            // Deduct stock
            $this->deductStock($order, $itemMaster, $itemData['quantity']);
        }

        $order->update(['stock_deducted' => true]);
    }

    /**
     * Deduct stock for an item
     */
    protected function deductStock(Order $order, ItemMaster $item, int $quantity)
    {
        ItemTransaction::create([
            'organization_id' => $order->branch->organization_id,
            'branch_id' => $order->branch_id,
            'inventory_item_id' => $item->id,
            'transaction_type' => 'sales_order',
            'quantity' => -$quantity, // Negative for stock out
            'cost_price' => $item->buying_price,
            'unit_price' => $item->selling_price,
            'source_id' => (string)$order->id,            'source_type' => 'Order',
            'created_by_user_id' => Auth::id(),
            'notes' => "Stock deducted for Order #{$order->order_number}",
            'is_active' => true
        ]);

        Log::info('Stock deducted', [
            'order_id' => $order->id,
            'item_id' => $item->id,
            'quantity' => $quantity,
            'remaining_stock' => ItemTransaction::stockOnHand($item->id, $order->branch_id)
        ]);
    }    /**
     * Get available servers for a branch (updated from stewards)
     */
    public function getAvailableServers($branchId)
    {
        return Employee::active()
            ->servers()
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available stewards for a branch (legacy method - kept for compatibility)
     */
    public function getAvailableStewards($branchId)
    {
        return $this->getAvailableServers($branchId);
    }

    /**
     * Get items with stock information for ordering
     */
    public function getItemsWithStock($branchId, $organizationId)
    {
        return ItemMaster::where('organization_id', $organizationId)
            ->where('is_menu_item', true)
            ->get()
            ->map(function ($item) use ($branchId) {
                $stock = ItemTransaction::stockOnHand($item->id, $branchId);
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'selling_price' => $item->selling_price,
                    'unit_of_measurement' => $item->unit_of_measurement,
                    'current_stock' => $stock,
                    'is_available' => $stock > 0,
                    'category' => $item->category->name ?? 'Uncategorized'
                ];
            })
            ->filter(fn($item) => $item['is_available'])
            ->values();
    }

    /**
     * Check stock alerts for low stock items
     */
    public function getStockAlerts($branchId, $organizationId)
    {
        $items = ItemMaster::where('organization_id', $organizationId)->get();
        $alerts = [];

        foreach ($items as $item) {
            $stock = ItemTransaction::stockOnHand($item->id, $branchId);
            
            if ($stock <= 0) {
                $alerts[] = [
                    'type' => 'out_of_stock',
                    'item' => $item->name,
                    'current_stock' => $stock,
                    'message' => "{$item->name} is out of stock"
                ];
            } elseif ($stock <= $item->reorder_level) {
                $alerts[] = [
                    'type' => 'low_stock',
                    'item' => $item->name,
                    'current_stock' => $stock,
                    'reorder_level' => $item->reorder_level,
                    'message' => "{$item->name} is running low (Stock: {$stock}, Reorder: {$item->reorder_level})"
                ];
            }
        }

        return $alerts;
    }

    /**
     * Update order with validation
     */
    public function updateOrder(Order $order, array $data)
    {
        return DB::transaction(function () use ($order, $data) {
            // If items are being updated, validate stock and restore previous stock
            if (isset($data['items'])) {
                // Restore previous stock
                $this->restoreOrderStock($order);
                
                // Delete old items
                $order->items()->delete();
                
                // Validate new stock requirements
                $this->validateStockAvailability($data['items'], $order->branch_id);
                
                // Create new items and deduct stock
                $this->createOrderItems($order, $data['items']);
            }

            // Update order details
            $order->update(array_intersect_key($data, array_flip([
                'customer_name', 'customer_phone', 'steward_id', 'notes'
            ])));

            $order->calculateTotal();

            return $order;
        });
    }

    /**
     * Restore stock when order is cancelled or updated
     */
    public function restoreOrderStock(Order $order)
    {
        if (!$order->stock_deducted) {
            return;
        }

        foreach ($order->items as $orderItem) {
            ItemTransaction::create([
                'organization_id' => $order->branch->organization_id,
                'branch_id' => $order->branch_id,
                'inventory_item_id' => $orderItem->inventory_item_id,
                'transaction_type' => 'return',
                'quantity' => $orderItem->quantity, // Positive for stock return
                'cost_price' => $orderItem->inventoryItem->buying_price,
                'unit_price' => $orderItem->unit_price,
                'source_id' => (string)$order->id,                'source_type' => 'OrderCancellation',
                'created_by_user_id' => Auth::id(),
                'notes' => "Stock restored from cancelled Order #{$order->order_number}",
                'is_active' => true
            ]);
        }

        $order->update(['stock_deducted' => false]);

        Log::info('Stock restored for order', ['order_id' => $order->id]);
    }

    /**
     * Cancel order and restore stock
     */
    public function cancelOrder(Order $order, $reason = null)
    {
        return DB::transaction(function () use ($order, $reason) {
            // Restore stock if it was deducted
            $this->restoreOrderStock($order);
            
            // Cancel the order
            $order->cancel($reason);

            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'reason' => $reason
            ]);

            return $order;
        });
    }
}
