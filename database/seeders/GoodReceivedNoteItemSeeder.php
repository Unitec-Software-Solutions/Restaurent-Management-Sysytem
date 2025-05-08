<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GoodReceivedNoteItem;
use App\Models\GoodReceivedNote;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GoodReceivedNoteItemSeeder extends Seeder
{
    public function run(): void
    {
        $grns = GoodReceivedNote::all();

        foreach ($grns as $grn) {
            foreach (range(1, 3) as $j) {
                $expected = rand(5, 10);
                $received = $expected;
                $accepted = $received;
                $rejected = 0;

                GoodReceivedNoteItem::create([
                    'good_received_note_id' => $grn->id,
                    'purchase_order_item_id' => 1,
                    'inventory_item_id' => 1,
                    'expected_quantity' => $expected,
                    'received_quantity' => $received,
                    'accepted_quantity' => $accepted,
                    'rejected_quantity' => $rejected,
                    'rejection_reason' => null,
                    'unit_price' => 50,
                    'total_price' => 50 * $accepted,
                    'manufacturing_date' => Carbon::now()->subMonths(2)->toDateString(),
                    'expiry_date' => Carbon::now()->addMonths(10)->toDateString(),
                    'batch_number' => 'BATCH-' . strtoupper(Str::random(4)),
                    'quality_checked' => true,
                    'quality_check_notes' => 'Checked and approved.',
                    'is_active' => true,
                ]);
            }
        }
    }
}
