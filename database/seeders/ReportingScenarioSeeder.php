<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\InventoryItem;
use App\Models\ItemTransaction;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Carbon\Carbon;

class ReportingScenarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates comprehensive test data for reporting and analytics
     */
    public function run(): void
    {
        $this->createHistoricalSalesData();
        $this->createInventoryReportingData();
        $this->createCustomerAnalyticsData();
        $this->createSeasonalTrendData();
    }

    /**
     * Create historical sales data for various time periods
     */
    private function createHistoricalSalesData()
    {
        $organization = Organization::first();
        $branch = Branch::first();
        $customer = Customer::first();
        
        if (!$organization || !$branch || !$customer) {
            return; // Skip if required models don't exist
        }

        // Create orders for different time periods
        $timeperiods = [
            ['days_ago' => 1, 'orders' => 15, 'label' => 'Yesterday'],
            ['days_ago' => 7, 'orders' => 12, 'label' => 'Last Week'],
            ['days_ago' => 30, 'orders' => 8, 'label' => 'Last Month'],
            ['days_ago' => 90, 'orders' => 5, 'label' => 'Last Quarter'],
        ];

        $orderNumber = 1000;

        foreach ($timeperiods as $period) {
            for ($i = 0; $i < $period['orders']; $i++) {
                $orderDate = Carbon::now()->subDays($period['days_ago'])->addHours(rand(-12, 12));
                
                $order = Order::create([
                    'order_number' => 'RPT-' . $orderNumber++,
                    'customer_id' => $customer->id,
                    'branch_id' => $branch->id,
                    'organization_id' => $organization->id,
                    'order_type' => collect(['dine_in', 'takeaway', 'delivery'])->random(),
                    'status' => 'completed',
                    'subtotal' => rand(1500, 8000) / 100, // $15.00 to $80.00
                    'tax_amount' => function($subtotal) { return $subtotal * 0.075; },
                    'service_charge' => function($subtotal) { return $subtotal * 0.10; },
                    'discount_amount' => rand(0, 500) / 100, // $0.00 to $5.00
                    'total_amount' => function($subtotal, $tax, $service, $discount) {
                        return $subtotal + $tax + $service - $discount;
                    },
                    'created_at' => $orderDate,
                    'completed_at' => $orderDate->copy()->addMinutes(rand(15, 45)),
                ]);

                // Fix calculated fields
                $subtotal = $order->subtotal;
                $tax = round($subtotal * 0.075, 2);
                $service = round($subtotal * 0.10, 2);
                $total = $subtotal + $tax + $service - $order->discount_amount;

                $order->update([
                    'tax_amount' => $tax,
                    'service_charge' => $service,
                    'total_amount' => $total,
                ]);

                // Create corresponding payment
                Payment::create([
                    'reference_number' => 'PAY-RPT-' . $order->id,
                    'amount' => $total,
                    'payment_method' => collect(['cash', 'card', 'digital_wallet'])->random(),
                    'payment_type' => 'customer_payment',
                    'status' => 'completed',
                    'order_id' => $order->id,
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                    'created_at' => $orderDate->copy()->addMinutes(rand(30, 60)),
                ]);
            }
        }
    }

    /**
     * Create inventory reporting data with different transaction types
     */
    private function createInventoryReportingData()
    {
        $organization = Organization::first();
        $branch = Branch::first();

        if (!$organization || !$branch) {
            return;
        }

        $inventoryItems = InventoryItem::limit(10)->get();

        foreach ($inventoryItems as $item) {
            // Stock in transaction
            ItemTransaction::create([
                'inventory_item_id' => $item->id,
                'transaction_type' => 'stock_in',
                'quantity' => rand(50, 200),
                'unit_cost' => rand(200, 1000) / 100, // $2.00 to $10.00
                'total_cost' => function($qty, $cost) { return $qty * $cost; },
                'reference_number' => 'GRN-' . rand(1000, 9999),
                'notes' => 'Stock replenishment for reporting test',
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);

            // Stock out transaction (usage)
            $stockOutQty = rand(20, 80);
            ItemTransaction::create([
                'inventory_item_id' => $item->id,
                'transaction_type' => 'stock_out',
                'quantity' => $stockOutQty,
                'unit_cost' => $item->unit_cost ?? rand(200, 1000) / 100,
                'total_cost' => $stockOutQty * ($item->unit_cost ?? rand(200, 1000) / 100),
                'reference_number' => 'USAGE-' . rand(1000, 9999),
                'notes' => 'Kitchen usage for production',
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'created_at' => Carbon::now()->subDays(rand(1, 15)),
            ]);

            // Wastage transaction
            if (rand(1, 3) === 1) { // 33% chance of wastage
                ItemTransaction::create([
                    'inventory_item_id' => $item->id,
                    'transaction_type' => 'wastage',
                    'quantity' => rand(2, 10),
                    'unit_cost' => $item->unit_cost ?? rand(200, 1000) / 100,
                    'total_cost' => function($qty, $cost) { return $qty * $cost; },
                    'reference_number' => 'WST-' . rand(1000, 9999),
                    'notes' => 'Expired items - ' . collect(['damaged', 'expired', 'contaminated'])->random(),
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                    'created_at' => Carbon::now()->subDays(rand(1, 20)),
                ]);
            }
        }
    }

    /**
     * Create customer analytics data
     */
    private function createCustomerAnalyticsData()
    {
        $organization = Organization::first();
        $branch = Branch::first();
        
        if (!$organization || !$branch) {
            return;
        }

        $customers = Customer::limit(5)->get();

        foreach ($customers as $customer) {
            // Create different customer behavior patterns
            $orderCount = rand(3, 15);
            $customerType = collect(['frequent', 'occasional', 'one_time'])->random();
            
            for ($i = 0; $i < $orderCount; $i++) {
                $orderDate = match($customerType) {
                    'frequent' => Carbon::now()->subDays(rand(1, 30)),
                    'occasional' => Carbon::now()->subDays(rand(30, 90)),
                    'one_time' => Carbon::now()->subDays(rand(90, 180)),
                };

                $order = Order::create([
                    'order_number' => 'CUST-' . $customer->id . '-' . ($i + 1),
                    'customer_id' => $customer->id,
                    'branch_id' => $branch->id,
                    'organization_id' => $organization->id,
                    'order_type' => $customer->preferred_order_type ?? 'dine_in',
                    'status' => 'completed',
                    'subtotal' => match($customerType) {
                        'frequent' => rand(2000, 5000) / 100, // Regular spender
                        'occasional' => rand(3000, 8000) / 100, // Higher value when visits
                        'one_time' => rand(1500, 3500) / 100, // Lower value
                    },
                    'tax_amount' => function($subtotal) { return $subtotal * 0.075; },
                    'service_charge' => function($subtotal) { return $subtotal * 0.10; },
                    'discount_amount' => match($customerType) {
                        'frequent' => rand(200, 800) / 100, // Loyalty discounts
                        default => rand(0, 200) / 100,
                    },
                    'total_amount' => 0, // Will be calculated
                    'created_at' => $orderDate,
                    'completed_at' => $orderDate->copy()->addMinutes(rand(20, 60)),
                ]);

                // Calculate totals
                $subtotal = $order->subtotal;
                $tax = round($subtotal * 0.075, 2);
                $service = round($subtotal * 0.10, 2);
                $total = $subtotal + $tax + $service - $order->discount_amount;

                $order->update([
                    'tax_amount' => $tax,
                    'service_charge' => $service,
                    'total_amount' => $total,
                ]);
            }
        }
    }

    /**
     * Create seasonal trend data
     */
    private function createSeasonalTrendData()
    {
        $organization = Organization::first();
        $branch = Branch::first();
        $customer = Customer::first();
        
        if (!$organization || !$branch || !$customer) {
            return;
        }

        // Create seasonal patterns (simulate different months)
        $seasonalData = [
            'winter' => ['multiplier' => 0.8, 'months' => [12, 1, 2]],
            'spring' => ['multiplier' => 1.0, 'months' => [3, 4, 5]],
            'summer' => ['multiplier' => 1.3, 'months' => [6, 7, 8]],
            'fall' => ['multiplier' => 1.1, 'months' => [9, 10, 11]],
        ];

        foreach ($seasonalData as $season => $data) {
            foreach ($data['months'] as $month) {
                $ordersThisMonth = intval(10 * $data['multiplier']);
                
                for ($i = 0; $i < $ordersThisMonth; $i++) {
                    $orderDate = Carbon::now()->subMonths(12 - $month)->addDays(rand(1, 28));
                    
                    $baseAmount = 3000; // $30.00 base
                    $seasonalAmount = intval($baseAmount * $data['multiplier']);
                    
                    $order = Order::create([
                        'order_number' => 'SEAS-' . $season . '-' . $month . '-' . $i,
                        'customer_id' => $customer->id,
                        'branch_id' => $branch->id,
                        'organization_id' => $organization->id,
                        'order_type' => match($season) {
                            'summer' => collect(['takeaway', 'delivery'])->random(),
                            'winter' => 'dine_in',
                            default => collect(['dine_in', 'takeaway'])->random(),
                        },
                        'status' => 'completed',
                        'subtotal' => ($seasonalAmount + rand(-500, 500)) / 100,
                        'tax_amount' => 0, // Will calculate
                        'service_charge' => 0, // Will calculate
                        'discount_amount' => rand(0, 300) / 100,
                        'total_amount' => 0, // Will calculate
                        'created_at' => $orderDate,
                        'completed_at' => $orderDate->copy()->addMinutes(rand(15, 45)),
                    ]);

                    // Calculate totals
                    $subtotal = $order->subtotal;
                    $tax = round($subtotal * 0.075, 2);
                    $service = round($subtotal * 0.10, 2);
                    $total = $subtotal + $tax + $service - $order->discount_amount;

                    $order->update([
                        'tax_amount' => $tax,
                        'service_charge' => $service,
                        'total_amount' => $total,
                    ]);
                }
            }
        }
    }
}
