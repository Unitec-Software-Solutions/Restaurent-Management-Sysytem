<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GoodReceivedNote;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GoodReceivedNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable query log and model events for performance
        DB::disableQueryLog();
        GoodReceivedNote::flushEventListeners();

        $branches = Branch::pluck('id')->toArray();
        $suppliers = Supplier::pluck('id')->toArray(); // Just get supplier IDs
        $users = User::pluck('id')->toArray();
        $purchaseOrders = PurchaseOrder::pluck('id')->toArray();

        if (empty($purchaseOrders)) {
            $this->command->info('No purchase orders found. Please seed purchase orders first.');
            return;
        }

        $existingGrnNumbers = GoodReceivedNote::pluck('grn_number')->toArray();
        $existingGrnSet = array_flip($existingGrnNumbers);

        $batchSize = 5;
        $batchData = [];
        $createdCount = 0;
        $maxAttempts = 100;
        $targetCount = 20;
        $currentDate = date('Ymd');

        for ($i = 1; $createdCount < $targetCount && $i <= $maxAttempts; $i++) {
            $grnNumber = 'GRN-' . $currentDate . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

            if (isset($existingGrnSet[$grnNumber])) {
                continue;
            }

            $supplierId = $suppliers[array_rand($suppliers)];
            $now = now();

            $batchData[] = [
                'grn_number' => $grnNumber,
                'grn_date' => $now->subDays(rand(1, 30))->format('Y-m-d'),
                'branch_id' => $branches[array_rand($branches)],
                'purchase_order_id' => $purchaseOrders[array_rand($purchaseOrders)],
                'supplier_id' => $supplierId,
                // Removed supplier_code as it doesn't exist in your table
                'received_by' => $users[array_rand($users)],
                'checked_by' => rand(0, 1) ? $users[array_rand($users)] : null,
                'received_date' => $now->subDays(rand(1, 30))->format('Y-m-d'),
                'received_time' => $now->subHours(rand(1, 24))->format('H:i:s'),
                'delivery_note_number' => rand(0, 1) ? 'DN-' . rand(1000, 9999) : null,
                'supplier_invoice_number' => rand(0, 1) ? 'INV-' . rand(1000, 9999) : null,
                'supplier_invoice_no' => rand(0, 1) ? 'SI-' . rand(1000, 9999) : null,
                'description' => rand(0, 1) ? 'Sample GRN description ' . $i : null,
                'status' => $this->getRandomStatus(),
                'total_amount' => 0,
                'discount_amount' => rand(0, 100),
                'tax_amount' => rand(0, 200),
                'payable_amount' => 0,
                'paid_amount' => 0,
                'notes' => rand(0, 1) ? 'Additional notes for GRN ' . $i : null,
                'ip_address' => rand(0, 1) ? '192.168.' . rand(1, 255) . '.' . rand(1, 255) : null,
                'rejection_reason' => rand(0, 1) ? 'Sample rejection reason' : null,
                'has_discrepancy' => rand(0, 1),
                'discrepancy_notes' => rand(0, 1) ? 'Discrepancy notes for GRN ' . $i : null,
                'is_active' => true,
                'created_by' => $users[array_rand($users)],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $existingGrnSet[$grnNumber] = true;
            $createdCount++;

            // Insert in batches
            if (count($batchData) >= $batchSize) {
                GoodReceivedNote::insert($batchData);
                $batchData = [];
            }
        }

        // Insert any remaining records
        if (!empty($batchData)) {
            GoodReceivedNote::insert($batchData);
        }

        $this->command->info("{$createdCount} Good Received Notes seeded successfully!");
    }

    private function getRandomStatus(): string
    {
        $statuses = ['pending', 'received', 'checked', 'completed', 'discrepancy', 'partially_checked', 'rejected'];
        return $statuses[array_rand($statuses)];
    }
}