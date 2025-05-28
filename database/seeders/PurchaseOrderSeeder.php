<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\ItemMaster;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = Carbon::now()->subMonths(3);
        
        // For each organization
        for ($orgId = 1; $orgId <= 5; $orgId++) {
            $suppliers = Supplier::where('organization_id', $orgId)->get();
            $items = ItemMaster::where('organization_id', $orgId)->get();
            $branches = \App\Models\Branch::where('organization_id', $orgId)->get();

            // Skip if no items available
            if ($items->isEmpty()) {
                $this->command->warn("  ⚠️  No items found for organization $orgId - skipping PO creation");
                continue;
            }

            // Create POs for last 3 months
            for ($i = 0; $i < 90; $i++) {
                $orderDate = $startDate->copy()->addDays($i);
                
                // Create 1-3 POs per day
                $dailyPOs = rand(1, 3);
                
                for ($j = 0; $j < $dailyPOs; $j++) {
                    $supplier = $suppliers->random();
                    $branch = $branches->random();
                    
                    $po = PurchaseOrder::create([
                        'branch_id' => $branch->id,
                        'organization_id' => $orgId,
                        'supplier_id' => $supplier->id,
                        'user_id' => rand(1, 5),
                        'po_number' => 'PO-' . $orderDate->format('Ymd') . '-' . Str::padLeft(rand(1, 999), 3, '0'),
                        'order_date' => $orderDate,
                        'expected_delivery_date' => $orderDate->copy()->addDays(rand(1, 5)),
                        'status' => $this->getRandomStatus($orderDate),
                        'notes' => 'Seeded purchase order',
                        'is_active' => true
                    ]);

                    // Determine number of items (but don't exceed available items)
                    $itemCount = min(rand(3, 8), $items->count());
                    $poItems = $items->random($itemCount);
                    $total = 0;

                    foreach ($poItems as $item) {
                        $qty = rand(5, 50);
                        $price = $item->buying_price;
                        $lineTotal = $qty * $price;
                        $total += $lineTotal;

                        PurchaseOrderItem::create([
                            'po_id' => $po->po_id,
                            'item_code' => $item->item_code,
                            'buying_price' => $price,
                            'quantity' => $qty,
                            'line_total' => $lineTotal,
                            'po_status' => $po->status
                        ]);
                    }

                    $po->update([
                        'total_amount' => $total,
                        'paid_amount' => $this->calculatePaidAmount($total, $po->status)
                    ]);
                }
            }
        }

        $this->command->info('  ✅ Purchase Orders seeded successfully!');
    }

    private function getRandomStatus($orderDate)
    {
        $today = Carbon::now();
        $daysDiff = $today->diffInDays($orderDate);

        if ($daysDiff > 60) {
            return 'Completed';
        } elseif ($daysDiff > 30) {
            return $this->getWeightedStatus(['Completed' => 70, 'Received' => 20, 'Approved' => 10]);
        } else {
            return $this->getWeightedStatus(['Pending' => 30, 'Approved' => 40, 'Received' => 20, 'Completed' => 10]);
        }
    }

    private function getWeightedStatus($weights)
    {
        $rand = rand(1, 100);
        $sum = 0;
        
        foreach ($weights as $status => $weight) {
            $sum += $weight;
            if ($rand <= $sum) {
                return $status;
            }
        }
        
        return array_key_first($weights);
    }

    private function calculatePaidAmount($total, $status)
    {
        switch ($status) {
            case 'Completed':
                return $total;
            case 'Received':
                return $total * (rand(50, 90) / 100);
            case 'Approved':
                return $total * (rand(0, 30) / 100);
            default:
                return 0;
        }
    }
}