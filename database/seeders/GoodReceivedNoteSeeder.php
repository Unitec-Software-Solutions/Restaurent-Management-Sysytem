<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GoodReceivedNote;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\PurchaseOrder;

class GoodReceivedNoteSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure required foreign keys exist
        $purchaseOrderId = 1;
        $receivedBy = 1;
        $checkedBy = 2;

        foreach (range(1, 5) as $i) {
            GoodReceivedNote::create([
                'grn_number' => 'GRN-' . strtoupper(Str::random(6)),
                'branch_id' => Branch::inRandomOrder()->value('id'),
                'purchase_order_id' => PurchaseOrder::inRandomOrder()->value('id'),
                'supplier_id' => Supplier::inRandomOrder()->value('id'),
                'received_by' => $receivedBy,
                'checked_by' => $checkedBy,
                'received_date' => Carbon::now()->toDateString(),
                'received_time' => Carbon::now()->toTimeString(),
                'delivery_note_number' => 'DN-' . rand(1000, 9999),
                'supplier_invoice_number' => 'INV-' . rand(1000, 9999),
                'status' => 'pending',
                'total_amount' => rand(1000, 5000),
                'notes' => 'Sample note for GRN ' . $i,
                'has_discrepancy' => false,
                'discrepancy_notes' => null,
                'is_active' => true,
            ]);
        }
    }
}
