<?php

namespace Database\Seeders;

use App\Models\GrnMaster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SupplierPaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get all verified GRNs
        $grns = GrnMaster::where('status', 'Verified')
            ->with(['purchaseOrder', 'supplier'])
            ->get();

        foreach ($grns as $grn) {
            // Skip if PO is fully paid
            if ($grn->purchaseOrder && $grn->purchaseOrder->total_amount <= $grn->purchaseOrder->paid_amount) {
                continue;
            }

            // Create payment master record
            $payment = \App\Models\SupplierPaymentMaster::create([
                'organization_id' => $grn->organization_id,
                'po_id' => $grn->po_id,
                'grn_id' => $grn->grn_id,
                'supplier_id' => $grn->supplier_id,
                'branch_id' => $grn->branch_id,
                'payment_number' => 'PAY-' . date('Ymd') . '-' . Str::random(4),
                'payment_date' => $grn->received_date->addDays(rand(1, 15)),
                'total_amount' => $this->calculatePaymentAmount($grn),
                'payment_status' => 'completed',
                'processed_by' => rand(1, 5),
                'notes' => 'Seeded payment record'
            ]);

            // Create payment details
            $this->createPaymentDetails($payment);
        }

        $this->command->info('  ✅ Supplier payments seeded successfully!');
    }

    private function calculatePaymentAmount($grn)
    {
        $remainingAmount = $grn->purchaseOrder 
            ? $grn->purchaseOrder->total_amount - $grn->purchaseOrder->paid_amount 
            : $grn->total_amount;

        return $remainingAmount > 0 ? $remainingAmount : $grn->total_amount;
    }

    private function createPaymentDetails($payment)
    {
        $amount = $payment->total_amount;
        $methods = $this->getPaymentMethods($amount);

        foreach ($methods as $method => $methodAmount) {
            \App\Models\SupplierPaymentDetail::create([
                'payment_master_id' => $payment->id,
                'method_type' => $method,
                'amount' => $methodAmount,
                'reference_number' => Str::random(10),
                'value_date' => $payment->payment_date,
                'cheque_number' => $method === 'cheque' ? 'CHQ-' . Str::random(6) : null,
                'bank_name' => $this->getBankName($method),
                'cheque_date' => $method === 'cheque' ? $payment->payment_date->addDays(rand(0, 30)) : null,
                'transaction_id' => $method === 'bank_transfer' ? 'TRX-' . Str::random(8) : null,
            ]);
        }
    }

    private function getPaymentMethods($total)
    {
        // Randomly decide payment split
        $methods = ['cash', 'cheque', 'bank_transfer'];
        $methodCount = rand(1, 2);
        $selectedMethods = array_rand(array_flip($methods), $methodCount);
        
        if ($methodCount === 1) {
            return [$selectedMethods => $total];
        }

        $split = rand(40, 60) / 100;
        return [
            $selectedMethods[0] => $total * $split,
            $selectedMethods[1] => $total * (1 - $split)
        ];
    }

    private function getBankName($method)
    {
        if (!in_array($method, ['cheque', 'bank_transfer'])) {
            return null;
        }

        $banks = [
            'Bank of Ceylon',
            'People\'s Bank',
            'Commercial Bank',
            'Sampath Bank',
            'HNB'
        ];

        return $banks[array_rand($banks)];
    }
}