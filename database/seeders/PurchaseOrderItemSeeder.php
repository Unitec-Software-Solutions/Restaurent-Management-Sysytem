<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
use App\Models\PurchaseOrderItem;

class PurchaseOrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purchaseOrders = PurchaseOrder::all();
        $inventoryItems = InventoryItem::all();
        
        if ($purchaseOrders->isEmpty()) {
            $this->command->error('No purchase orders found. Please run the purchase order seeder first.');
            return;
        }
        
        if ($inventoryItems->isEmpty()) {
            $this->command->error('No inventory items found. Please run the inventory item seeder first.');
            return;
        }

        foreach ($purchaseOrders as $order) {
            // Each purchase order will have 3-8 items
            $numItems = rand(3, 8);
            
            // Get random items for this order
            $orderItems = $inventoryItems->random($numItems);
            
            foreach ($orderItems as $item) {
                $quantity = rand(1, 100);
                $unitPrice = rand(100, 10000) / 100; // $1.00 to $100.00
                
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'inventory_item_id' => $item->id,
                    'quantity' => $quantity,
                    'received_quantity' => $order->status === 'received' ? $quantity : 
                                        ($order->status === 'partially_received' ? rand(1, $quantity) : 0),
                    'unit_price' => $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                    'is_active' => true
                ]);
            }
            
            // Update purchase order total
            $order->total_amount = $order->items()->sum('total_price');
            $order->save();
        }

        $this->command->info('Purchase order items seeded successfully!');
    }
}