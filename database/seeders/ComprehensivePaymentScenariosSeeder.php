<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Branch;
use Carbon\Carbon;

class ComprehensivePaymentScenariosSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Seeding comprehensive payment scenarios...');

        // Get organizations and branches
        $organizations = Organization::all();
        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Please run OrganizationSeeder first.');
            return;
        }

        $branches = Branch::all();
        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Please run BranchSeeder first.');
            return;
        }

        // Get existing orders
        $orders = Order::all();
        if ($orders->isEmpty()) {
            $this->command->warn('No orders found. Please run ComprehensiveOrdersSeeder first.');
            return;
        }

        $paymentMethods = ['cash', 'credit_card', 'debit_card', 'mobile_payment', 'bank_transfer'];
        $paymentStatuses = ['pending', 'completed', 'failed', 'refunded', 'cancelled'];
        $paymentGateways = ['stripe', 'paypal', 'square', 'razorpay', 'manual'];

        $paymentScenarios = [];

        // Scenario 1: Successful Payments (70% of all payments)
        $successfulCount = (int) ($orders->count() * 0.7);
        for ($i = 0; $i < $successfulCount; $i++) {
            $order = $orders->random();
            $branch = $branches->random();
            $organization = $organizations->random();

            $paymentScenarios[] = [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'payment_method' => fake()->randomElement($paymentMethods),
                'payment_gateway' => fake()->randomElement(['stripe', 'paypal', 'square', 'manual']),
                'amount' => $order->total_price,
                'currency' => 'USD',
                'status' => 'completed',
                'transaction_id' => 'TXN_' . strtoupper(fake()->lexify('?????')) . fake()->numerify('#####'),
                'gateway_response' => json_encode([
                    'status' => 'success',
                    'transaction_id' => 'gtw_' . fake()->lexify('??????????'),
                    'gateway_fee' => round($order->total_price * 0.029, 2), // 2.9% fee
                    'authorization_code' => fake()->lexify('AUTH_??????'),
                    'processed_at' => now()->subMinutes(fake()->numberBetween(1, 1440)),
                ]),
                'payment_date' => $order->created_at->addMinutes(fake()->numberBetween(1, 30)),
                'notes' => fake()->randomElement([
                    'Payment processed successfully',
                    'Customer paid via mobile app',
                    'In-person payment at counter',
                    'Online payment completed',
                    null
                ]),
                'created_at' => $order->created_at->addMinutes(fake()->numberBetween(1, 45)),
                'updated_at' => now(),
            ];
        }

        // Scenario 2: Failed Payments (15% of all payments)
        $failedCount = (int) ($orders->count() * 0.15);
        for ($i = 0; $i < $failedCount; $i++) {
            $order = $orders->random();
            $branch = $branches->random();
            $organization = $organizations->random();

            $failureReasons = [
                'Insufficient funds',
                'Card declined',
                'Payment gateway timeout',
                'Invalid card details',
                'Bank authorization failed',
                'Network connectivity issue',
                'Payment limit exceeded'
            ];

            $paymentScenarios[] = [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'payment_method' => fake()->randomElement($paymentMethods),
                'payment_gateway' => fake()->randomElement($paymentGateways),
                'amount' => $order->total_price,
                'currency' => 'USD',
                'status' => 'failed',
                'transaction_id' => 'FAIL_' . strtoupper(fake()->lexify('?????')) . fake()->numerify('#####'),
                'gateway_response' => json_encode([
                    'status' => 'failed',
                    'error_code' => fake()->randomElement(['4001', '4002', '4003', '5001', '5002']),
                    'error_message' => fake()->randomElement($failureReasons),
                    'decline_code' => fake()->randomElement(['generic_decline', 'insufficient_funds', 'lost_card', 'expired_card']),
                    'failed_at' => now()->subMinutes(fake()->numberBetween(1, 1440)),
                ]),
                'payment_date' => null,
                'notes' => 'Payment failed: ' . fake()->randomElement($failureReasons),
                'created_at' => $order->created_at->addMinutes(fake()->numberBetween(1, 45)),
                'updated_at' => now(),
            ];
        }

        // Scenario 3: Refunded Payments (10% of successful payments)
        $refundedCount = (int) ($successfulCount * 0.1);
        for ($i = 0; $i < $refundedCount; $i++) {
            $order = $orders->random();
            $branch = $branches->random();
            $organization = $organizations->random();

            $refundReasons = [
                'Customer requested cancellation',
                'Order cancelled by restaurant',
                'Food quality issue',
                'Delivery delay compensation',
                'Item unavailable',
                'Customer complaint resolution'
            ];

            $refundAmount = fake()->randomFloat(2, $order->total_price * 0.5, $order->total_price);

            $paymentScenarios[] = [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'payment_method' => fake()->randomElement($paymentMethods),
                'payment_gateway' => fake()->randomElement(['stripe', 'paypal', 'square']),
                'amount' => -$refundAmount, // Negative amount for refunds
                'currency' => 'USD',
                'status' => 'refunded',
                'transaction_id' => 'REF_' . strtoupper(fake()->lexify('?????')) . fake()->numerify('#####'),
                'gateway_response' => json_encode([
                    'status' => 'refunded',
                    'refund_id' => 'ref_' . fake()->lexify('??????????'),
                    'original_transaction_id' => 'TXN_' . strtoupper(fake()->lexify('?????')) . fake()->numerify('#####'),
                    'refund_amount' => $refundAmount,
                    'refund_reason' => fake()->randomElement($refundReasons),
                    'refunded_at' => now()->subMinutes(fake()->numberBetween(60, 10080)), // 1 hour to 1 week ago
                ]),
                'payment_date' => $order->created_at->addDays(fake()->numberBetween(1, 7)),
                'notes' => 'Refund processed: ' . fake()->randomElement($refundReasons),
                'created_at' => $order->created_at->addDays(fake()->numberBetween(1, 14)),
                'updated_at' => now(),
            ];
        }

        // Scenario 4: Pending Payments (3% of all payments)
        $pendingCount = (int) ($orders->count() * 0.03);
        for ($i = 0; $i < $pendingCount; $i++) {
            $order = $orders->random();
            $branch = $branches->random();
            $organization = $organizations->random();

            $paymentScenarios[] = [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'payment_method' => fake()->randomElement(['bank_transfer', 'mobile_payment', 'credit_card']),
                'payment_gateway' => fake()->randomElement($paymentGateways),
                'amount' => $order->total_price,
                'currency' => 'USD',
                'status' => 'pending',
                'transaction_id' => 'PEND_' . strtoupper(fake()->lexify('?????')) . fake()->numerify('#####'),
                'gateway_response' => json_encode([
                    'status' => 'pending',
                    'pending_reason' => fake()->randomElement([
                        'Bank authorization in progress',
                        'Waiting for customer confirmation',
                        'Manual review required',
                        'Payment gateway processing'
                    ]),
                    'initiated_at' => now()->subMinutes(fake()->numberBetween(1, 180)),
                ]),
                'payment_date' => null,
                'notes' => fake()->randomElement([
                    'Payment authorization pending',
                    'Waiting for bank confirmation',
                    'Customer verification required',
                    'Processing payment...'
                ]),
                'created_at' => $order->created_at->addMinutes(fake()->numberBetween(1, 30)),
                'updated_at' => now(),
            ];
        }

        // Scenario 5: Cancelled Payments (2% of all payments)
        $cancelledCount = (int) ($orders->count() * 0.02);
        for ($i = 0; $i < $cancelledCount; $i++) {
            $order = $orders->random();
            $branch = $branches->random();
            $organization = $organizations->random();

            $paymentScenarios[] = [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'payment_method' => fake()->randomElement($paymentMethods),
                'payment_gateway' => fake()->randomElement($paymentGateways),
                'amount' => $order->total_price,
                'currency' => 'USD',
                'status' => 'cancelled',
                'transaction_id' => 'CANC_' . strtoupper(fake()->lexify('?????')) . fake()->numerify('#####'),
                'gateway_response' => json_encode([
                    'status' => 'cancelled',
                    'cancellation_reason' => fake()->randomElement([
                        'Customer cancelled order',
                        'Payment timeout',
                        'System error during processing',
                        'Merchant cancelled transaction'
                    ]),
                    'cancelled_at' => now()->subMinutes(fake()->numberBetween(1, 1440)),
                ]),
                'payment_date' => null,
                'notes' => fake()->randomElement([
                    'Payment cancelled by customer',
                    'Transaction timeout',
                    'Order cancelled before payment',
                    'System error - payment cancelled'
                ]),
                'created_at' => $order->created_at->addMinutes(fake()->numberBetween(1, 60)),
                'updated_at' => now(),
            ];
        }

        // Scenario 6: High-value transactions (Large orders)
        $highValueOrders = $orders->where('total_price', '>', 100);
        foreach ($highValueOrders->take(20) as $order) {
            $branch = $branches->random();
            $organization = $organizations->random();

            $paymentScenarios[] = [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'payment_method' => fake()->randomElement(['credit_card', 'bank_transfer']),
                'payment_gateway' => fake()->randomElement(['stripe', 'paypal']),
                'amount' => $order->total_price,
                'currency' => 'USD',
                'status' => fake()->randomElement(['completed', 'pending']),
                'transaction_id' => 'HV_' . strtoupper(fake()->lexify('?????')) . fake()->numerify('#####'),
                'gateway_response' => json_encode([
                    'status' => 'completed',
                    'high_value_flag' => true,
                    'risk_score' => fake()->randomFloat(2, 0.1, 0.3), // Low risk for high value
                    'additional_verification' => 'CVV verified, AVS match',
                    'processed_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
                ]),
                'payment_date' => $order->created_at->addMinutes(fake()->numberBetween(5, 60)),
                'notes' => 'High-value transaction - additional verification completed',
                'created_at' => $order->created_at->addMinutes(fake()->numberBetween(1, 30)),
                'updated_at' => now(),
            ];
        }

        // Scenario 7: Split payments (Multiple payment methods for single order)
        $splitPaymentOrders = $orders->where('total_price', '>', 50)->take(15);
        foreach ($splitPaymentOrders as $order) {
            $branch = $branches->random();
            $organization = $organizations->random();
            
            // First payment (larger portion)
            $firstAmount = $order->total_price * fake()->randomFloat(2, 0.6, 0.8);
            $paymentScenarios[] = [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'payment_method' => 'credit_card',
                'payment_gateway' => 'stripe',
                'amount' => $firstAmount,
                'currency' => 'USD',
                'status' => 'completed',
                'transaction_id' => 'SPLIT1_' . strtoupper(fake()->lexify('????')) . fake()->numerify('####'),
                'gateway_response' => json_encode([
                    'status' => 'completed',
                    'split_payment' => true,
                    'payment_sequence' => 1,
                    'processed_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
                ]),
                'payment_date' => $order->created_at->addMinutes(fake()->numberBetween(1, 30)),
                'notes' => 'Split payment 1/2 - Credit card portion',
                'created_at' => $order->created_at->addMinutes(fake()->numberBetween(1, 30)),
                'updated_at' => now(),
            ];

            // Second payment (remaining amount)
            $secondAmount = $order->total_price - $firstAmount;
            $paymentScenarios[] = [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'payment_method' => 'cash',
                'payment_gateway' => 'manual',
                'amount' => $secondAmount,
                'currency' => 'USD',
                'status' => 'completed',
                'transaction_id' => 'SPLIT2_' . strtoupper(fake()->lexify('????')) . fake()->numerify('####'),
                'gateway_response' => json_encode([
                    'status' => 'completed',
                    'split_payment' => true,
                    'payment_sequence' => 2,
                    'processed_at' => now()->subMinutes(fake()->numberBetween(1, 30)),
                ]),
                'payment_date' => $order->created_at->addMinutes(fake()->numberBetween(31, 60)),
                'notes' => 'Split payment 2/2 - Cash portion',
                'created_at' => $order->created_at->addMinutes(fake()->numberBetween(31, 60)),
                'updated_at' => now(),
            ];
        }

        // Insert all payment scenarios in chunks
        $chunks = array_chunk($paymentScenarios, 100);
        foreach ($chunks as $chunk) {
            Payment::insert($chunk);
        }

        $this->command->info('Payment scenarios seeded successfully:');
        $this->command->info('- Successful payments: ' . $successfulCount);
        $this->command->info('- Failed payments: ' . $failedCount);
        $this->command->info('- Refunded payments: ' . $refundedCount);
        $this->command->info('- Pending payments: ' . $pendingCount);
        $this->command->info('- Cancelled payments: ' . $cancelledCount);
        $this->command->info('- High-value transactions: 20');
        $this->command->info('- Split payments: ' . ($splitPaymentOrders->count() * 2));
        $this->command->info('Total payment records: ' . count($paymentScenarios));
    }
}
