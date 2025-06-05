<?php

namespace Database\Seeders;

use App\Models\GrnMaster;
use App\Models\GrnItem;
use App\Models\ItemMaster;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GRNSeeder extends Seeder
{
    public function run(): void
    {
        // Get all POs that are Approved or Received
        $purchaseOrders = PurchaseOrder::whereIn('status', ['Approved', 'Received', 'Completed'])
            ->with(['items', 'supplier'])
            ->get();

        foreach ($purchaseOrders as $po) {
            // Create GRN
            $grn = GrnMaster::create([
                'grn_number' => 'GRN-' . $po->order_date->format('Ymd') . '-' . Str::random(4),
                'po_id' => $po->po_id,
                'branch_id' => $po->branch_id,
                'organization_id' => $po->organization_id,
                'supplier_id' => $po->supplier_id,
                'received_by_user_id' => rand(1, 5),
                'verified_by_user_id' => rand(1, 5),
                'received_date' => $po->order_date->addDays(rand(1, 3)),
                'delivery_note_number' => 'DN-' . Str::random(8),
                'invoice_number' => 'INV-' . Str::random(8),
                'status' => $this->getGrnStatus($po->status),
                'notes' => 'Seeded GRN record',
                'is_active' => true,
                'created_by' => 1,
                'total_amount' => 0
            ]);

            $total = 0;

            // Create GRN items based on PO items
            foreach ($po->items as $poItem) {
                // Find corresponding item from item_master
                $item = ItemMaster::where('item_code', $poItem->item_code)->first();

                if (!$item) {
                    $this->command->warn("⚠️ Skipping item with code {$poItem->item_code} - Item not found in master");
                    continue;
                }

                $receivedQty = $this->calculateReceivedQuantity($poItem->quantity);
                $acceptedQty = $this->calculateAcceptedQuantity($receivedQty);
                $rejectedQty = $receivedQty - $acceptedQty;
                $lineTotal = $acceptedQty * $poItem->buying_price;
                $total += $lineTotal;

                GrnItem::create([
                    'grn_id' => $grn->grn_id,
                    'po_detail_id' => $poItem->po_detail_id,
                    'item_id' => $item->id, // Add the item_id from item_master
                    'item_code' => $poItem->item_code,
                    'batch_no' => 'BTH-' . Str::random(6),
                    'ordered_quantity' => $poItem->quantity,
                    'received_quantity' => $receivedQty,
                    'accepted_quantity' => $acceptedQty,
                    'rejected_quantity' => $rejectedQty,
                    'buying_price' => $poItem->buying_price,
                    'line_total' => $lineTotal,
                    'manufacturing_date' => Carbon::now()->subDays(rand(1, 30)),
                    'expiry_date' => Carbon::now()->addMonths(rand(3, 12)),
                    'rejection_reason' => $rejectedQty > 0 ? $this->getRandomRejectionReason() : null
                ]);
            }

            $grn->update(['total_amount' => $total]);
        }

        $this->command->info('  ✅ GRNs seeded successfully!');
    }

    private function getGrnStatus($poStatus)
    {
        if ($poStatus === 'Completed') {
            return 'Verified';
        } elseif ($poStatus === 'Received') {
            return rand(0, 1) ? 'Verified' : 'Pending';
        }
        return 'Pending';
    }

    private function calculateReceivedQuantity($ordered)
    {
        // 80% chance of receiving full quantity
        return rand(1, 100) <= 80 ? $ordered : $ordered * (rand(85, 95) / 100);
    }

    private function calculateAcceptedQuantity($received)
    {
        // 90% chance of accepting full received quantity
        return rand(1, 100) <= 90 ? $received : $received * (rand(90, 98) / 100);
    }

    private function getRandomRejectionReason()
    {
        $reasons = [
            'Damaged in transit',
            'Quality below standard',
            'Wrong specification',
            'Expired product',
            'Packaging damaged'
        ];
        return $reasons[array_rand($reasons)];
    }
}
