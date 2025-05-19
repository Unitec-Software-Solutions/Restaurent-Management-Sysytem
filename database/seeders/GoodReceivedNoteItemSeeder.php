<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GoodReceivedNote;
use App\Models\GoodReceivedNoteItem;
use App\Models\PurchaseOrderItem;
use App\Models\InventoryItem;

class GoodReceivedNoteItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '256M'); // Temporary increase
    
        $initialCount = GoodReceivedNoteItem::count();

        $grns = GoodReceivedNote::all();
        $purchaseOrderItems = PurchaseOrderItem::with('purchaseOrder')->get();

        if ($grns->isEmpty() || $purchaseOrderItems->isEmpty()) {
            $this->command->info('No GRNs or purchase order items found. Please seed those first.');
            return;
        }

        foreach ($grns as $grn) {
            $poItems = $purchaseOrderItems->where('purchase_order_id', $grn->purchase_order_id);

            if ($poItems->isEmpty()) {
                continue;
            }

            $totalAmount = 0;
            $itemsCount = rand(1, min(5, $poItems->count()));
            $selectedItems = $poItems->random($itemsCount);

            foreach ($selectedItems as $poItem) {
                // Avoid duplicates
                if (GoodReceivedNoteItem::where([
                    ['good_received_note_id', $grn->id],
                    ['purchase_order_item_id', $poItem->id]
                ])->exists()) {
                    continue;
                }

                $inventoryItem = InventoryItem::find($poItem->inventory_item_id);
                if (!$inventoryItem || !$inventoryItem->code || !$inventoryItem->name) {
                    continue; // Skip if data is missing
                }

                $expectedQty = (int) $poItem->quantity;
                $receivedQty = (int) round($this->getReceivedQuantity($expectedQty));
                $acceptedQty = (int) round($this->getAcceptedQuantity($receivedQty));
                $rejectedQty = $receivedQty - $acceptedQty;
                $unitPrice = $poItem->unit_price ?? 0;
                $costPrice = $poItem->cost_price ?? 0;
                $totalPrice = $acceptedQty * $unitPrice;

                GoodReceivedNoteItem::create([
                    'good_received_note_id' => $grn->id,
                    'purchase_order_item_id' => $poItem->id,
                    'inventory_item_id' => $poItem->inventory_item_id,
                    'item_code' => $inventoryItem->code,
                    'item_name' => $inventoryItem->name,
                    'quantity' => $expectedQty,
                    'expected_quantity' => $expectedQty,
                    'received_quantity' => $receivedQty,
                    'accepted_quantity' => $acceptedQty,
                    'rejected_quantity' => $rejectedQty,
                    'free_quantity' => rand(0, 1) ? rand(1, 5) : 0,
                    'rejection_reason' => $rejectedQty > 0 ? 'Damaged goods' : null,
                    'cost_price' => $costPrice,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => rand(0, 1) ? rand(1, 20) : 0,
                    'total_price' => $totalPrice,
                    'total_amount' => $totalPrice,
                    'manufacturing_date' => rand(0, 1) ? now()->subMonths(rand(1, 12))->format('Y-m-d') : null,
                    'expiry_date' => rand(0, 1) ? now()->addMonths(rand(6, 36))->format('Y-m-d') : null,
                    'batch_number' => rand(0, 1) ? 'BATCH-' . rand(1000, 9999) : null,
                    'quality_checked' => rand(0, 1),
                    'quality_check_notes' => rand(0, 1) ? 'Quality check notes for item' : null,
                    'is_active' => true,
                ]);

                $totalAmount += $totalPrice;
            }

            $grn->update([
                'total_amount' => $totalAmount,
                'payable_amount' => $totalAmount - $grn->discount_amount + $grn->tax_amount,
                'paid_amount' => rand(0, 1) ? $totalAmount - $grn->discount_amount + $grn->tax_amount : 0,
            ]);
        }

        $finalCount = GoodReceivedNoteItem::count();
        $added = $finalCount - $initialCount;
        $this->command->info("Seeded {$added} GoodReceivedNoteItem records. Total now: {$finalCount}");
    }

    private function getReceivedQuantity($expectedQty): float
    {
        $variation = rand(-5, 5) / 10; // -0.5 to +0.5
        $received = $expectedQty * (1 + $variation);
        return max(0, round($received, 3));
    }

    private function getAcceptedQuantity($receivedQty): float
    {
        $acceptPercentage = rand(80, 100) / 100;
        return round($receivedQty * $acceptPercentage, 3);
    }
}
