<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $branches = Branch::all();
    $suppliers = Supplier::all();
    $users = User::all();

    if ($branches->isEmpty()) {
        $this->command->error('No branches found. Please run the branch seeder first.');
        return;
    }

    if ($suppliers->isEmpty()) {
        $this->command->error('No suppliers found. Please run the supplier seeder first.');
        return;
    }

    if ($users->isEmpty()) {
        $this->command->error('No users found. Please run the user seeder first.');
        return;
    }

    $startDate = Carbon::now()->subMonths(3);
    $endDate = Carbon::now();

        // Each branch will have 10-20 purchase orders
        foreach ($branches as $branch) {
            $numOrders = rand(10, 20);
            
            for ($i = 0; $i < $numOrders; $i++) {
                // Generate a random date between start and end
                $orderDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );
                
                // Expected delivery is 3-7 days after order date
                $expectedDelivery = (clone $orderDate)->addDays(rand(3, 7));
                
                // Select random status based on weights
                $status = $this->getRandomWeightedStatus($statuses);
                
                // Generate PO number (format: PO-YYYYMMDD-XXX)
                $poNumber = 'PO-' . $orderDate->format('YmdHis') . '-' . Str::random(3);

                PurchaseOrder::create([
                    'branch_id' => $branch->id,
                    'supplier_id' => $suppliers->random()->id,
                    'user_id' => $users->random()->id,
                    'po_number' => $poNumber,
                    'order_date' => $orderDate,
                    'expected_delivery_date' => $expectedDelivery,
                    'status' => $status,
                    'total_amount' => 0, // Will be updated when items are added
                    'notes' => $this->generateNotes($status),
                    'is_active' => true,
                    'updates' => '1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

    $statuses = [
        'received' => 40,
        'partially_received' => 20,
        'sent' => 20,
        'draft' => 15,
        'cancelled' => 5
    ];

    $existingPoNumbers = [];

    foreach ($branches as $branch) {
        $numOrders = rand(10, 20);

        for ($i = 0; $i < $numOrders; $i++) {
            $orderDate = Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            );

            $expectedDelivery = (clone $orderDate)->addDays(rand(3, 7));

            $status = $this->getRandomWeightedStatus($statuses);

            // Ensure unique PO number generation
            do {
                $poNumber = sprintf(
                    "PO-%s-%03d",
                    $orderDate->format('Ymd'),
                    rand(1, 999)
                );
            } while (in_array($poNumber, $existingPoNumbers) || PurchaseOrder::where('po_number', $poNumber)->exists());

            $existingPoNumbers[] = $poNumber;


            PurchaseOrder::create([
                'branch_id' => $branch->id,
                'supplier_id' => $suppliers->random()->id,
                'user_id' => $users->random()->id,
                'po_number' => $poNumber,
                'order_date' => $orderDate,
                'expected_delivery_date' => $expectedDelivery,
                'status' => $status,
                'total_amount' => 0,
                'notes' => $this->generateNotes($status),
                'is_active' => true
            ]);
        }
    }

    $this->command->info('Purchase orders seeded successfully!');
}

    /**
     * Generate appropriate notes based on the PO status
     */
    private function generateNotes(string $status): string
    {
        $notes = [
            'received' => [
                'Order received in good condition',
                'All items verified and stored',
                'Delivery completed as expected',
                'Order fulfilled successfully'
            ],
            'partially_received' => [
                'Partial delivery - remaining items expected next week',
                'Some items out of stock, partial delivery accepted',
                'Incomplete delivery due to supplier shortage',
                'Partial order received, balance to follow'
            ],
            'sent' => [
                'Order confirmed by supplier',
                'Awaiting delivery confirmation',
                'In transit',
                'Delivery scheduled'
            ],
            'draft' => [
                'Pending approval',
                'Under review',
                'Awaiting final confirmation',
                'To be reviewed by manager'
            ],
            'cancelled' => [
                'Cancelled due to pricing discrepancy',
                'Order cancelled - items no longer needed',
                'Supplier unable to fulfill order',
                'Cancelled and reordered with different supplier'
            ]
        ];

        return $notes[$status][array_rand($notes[$status])];
    }

    /**
     * Get a random status based on weights
     */
    private function getRandomWeightedStatus(array $statuses): string
    {
        $total = array_sum($statuses);
        $random = rand(1, $total);
        
        foreach ($statuses as $status => $weight) {
            $random -= $weight;
            if ($random <= 0) {
                return $status;
            }
        }

        return array_key_first($statuses); // Fallback
    }
}