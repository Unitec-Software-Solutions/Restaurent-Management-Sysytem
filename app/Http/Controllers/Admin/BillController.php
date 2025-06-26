<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Order;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    protected $printService;

    public function __construct(PrintService $printService)
    {
        $this->printService = $printService;
    }

    protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access');
        }
        return $user->organization_id;
    }

    public function index(Request $request)
    {
        $orgId = $this->getOrganizationId();
        
        $query = Bill::with(['order', 'branch', 'generatedBy'])
            ->whereHas('branch', fn($q) => $q->where('organization_id', $orgId));

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('bill_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('generated_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('generated_at', '<=', $request->end_date);
        }

        $bills = $query->orderByDesc('generated_at')->paginate(20);

        return view('admin.bills.index', compact('bills'));
    }

    public function show(Bill $bill)
    {
        if ($bill->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access');
        }

        $bill->load(['order.items.inventoryItem', 'branch.organization', 'generatedBy']);
        
        return view('admin.bills.show', compact('bill'));
    }

    public function print(Bill $bill)
    {
        if ($bill->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access');
        }

        $billData = $this->printService->generateBillData($bill);
        
        return view('prints.bill', $billData);
    }

    public function markAsPaid(Request $request, Bill $bill)
    {
        if ($bill->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,online,bank_transfer'
        ]);

        $bill->markAsPaid($validated['payment_method']);

        return redirect()
            ->route('admin.bills.show', $bill)
            ->with('success', 'Bill marked as paid successfully!');
    }

    public function export(Request $request)
    {
        $orgId = $this->getOrganizationId();
        
        $query = Bill::with(['order.items.inventoryItem', 'branch'])
            ->whereHas('branch', fn($q) => $q->where('organization_id', $orgId));

        if ($request->filled('start_date')) {
            $query->whereDate('generated_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('generated_at', '<=', $request->end_date);
        }

        $bills = $query->orderByDesc('generated_at')->get();

        $csvData = $bills->map(function ($bill) {
            return [
                'Bill Number' => $bill->bill_number,
                'Order Number' => "ORD-{$bill->order_id}",
                'Date' => $bill->generated_at->format('Y-m-d H:i'),
                'Customer' => $bill->customer_name ?? 'Walk-in',
                'Phone' => $bill->customer_phone ?? '-',
                'Branch' => $bill->branch->name,
                'Subtotal' => number_format($bill->subtotal, 2),
                'Tax' => number_format($bill->tax_amount, 2),
                'Service Charge' => number_format($bill->service_charge, 2),
                'Discount' => number_format($bill->discount_amount, 2),
                'Total' => number_format($bill->total_amount, 2),
                'Payment Method' => $bill->payment_method ?? '-',
                'Payment Status' => $bill->payment_status ?? 'Pending'
            ];
        });

        $filename = 'bills_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            
            if ($csvData->isNotEmpty()) {
                fputcsv($file, array_keys($csvData->first()));
            }
            
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
