<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supplier;
use App\Models\ItemMaster;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 20; $i++) {
            $organizationId = rand(1, 5);
            $branchId = 1;

            $supplier = Supplier::where('organization_id', $organizationId)->inRandomOrder()->first();
            $items = ItemMaster::where('organization_id', $organizationId)->inRandomOrder()->take(rand(1, 5))->get();
            $user = User::where('organization_id', $organizationId)->inRandomOrder()->first();

            if (!$supplier || $items->isEmpty() || !$user) {
                $this->command->warn("⚠️ Skipping: Missing supplier/items/user for organization $organizationId");
                continue;
            }

            DB::beginTransaction();

            try {
                $orderDate = Carbon::now()->subDays(rand(1, 90));
                $expectedDate = (clone $orderDate)->addDays(rand(3, 14));

                $po = PurchaseOrder::create([
                    'organization_id' => $organizationId,
                    'branch_id' => $branchId,
                    'supplier_id' => $supplier->id,
                    'user_id' => $user->id,
                    'order_date' => $orderDate,
                    'expected_delivery_date' => $expectedDate,
                    'status' => PurchaseOrder::STATUS_PENDING,
                    'total_amount' => 0,
                    'paid_amount' => 0,
                    'payment_method' => 'Cash',
                    'notes' => 'Auto-generated PO for seeding',
                    'is_active' => true
                ]);

                $total = 0;

                foreach ($items as $item) {
                    $qty = $faker->randomFloat(2, 1, 20);
                    $price = $faker->randomFloat(2, 10, 200);
                    $lineTotal = round($qty * $price, 2);

                    $po->items()->create([
                        'item_id' => $item->id,
                        'batch_no' => strtoupper(uniqid('BATCH')),
                        'buying_price' => $price,
                        'previous_buying_price' => $item->buying_price,
                        'quantity' => $qty,
                        'quantity_received' => 0,
                        'line_total' => $lineTotal,
                        'po_status' => PurchaseOrderItem::STATUS_PENDING,
                    ]);

                    $total += $lineTotal;
                }

                $po->update(['total_amount' => $total]);

                DB::commit();

                $this->command->info("✅ Created PO {$po->po_number} for Org $organizationId");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("❌ Failed to create PO for Org $organizationId: " . $e->getMessage());
            }
        }
    }
}
