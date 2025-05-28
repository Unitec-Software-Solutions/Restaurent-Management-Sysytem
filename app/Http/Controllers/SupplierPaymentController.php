<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupplierPaymentMaster;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierPaymentController extends Controller
{
    // Get current user's organization ID
    protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access');
        }
        return $user->organization_id;
    }

    // Verify payment belongs to user's organization
    protected function checkOrganization(SupplierPaymentMaster $payment)
    {
        if ($payment->supplier->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access to payment details');
        }
    }

    public function index()
    {
        $orgId = $this->getOrganizationId();

        $summary = [
            'total_payments' => SupplierPaymentMaster::whereHas('supplier', fn($q) => $q->where('organization_id', $orgId))
                ->sum('total_amount') ?? 0,
            'pending_payments' => SupplierPaymentMaster::where('payment_status', 'pending')
                ->whereHas('supplier', fn($q) => $q->where('organization_id', $orgId))
                ->sum('total_amount') ?? 0,
            'overdue_payments' => SupplierPaymentMaster::where('payment_status', 'overdue')
                ->whereHas('supplier', fn($q) => $q->where('organization_id', $orgId))
                ->sum('total_amount') ?? 0,
            'suppliers_count' => Supplier::where('organization_id', $orgId)->count(),
            'pending_count' => SupplierPaymentMaster::where('payment_status', 'pending')
                ->whereHas('supplier', fn($q) => $q->where('organization_id', $orgId))
                ->count(),
            'overdue_count' => SupplierPaymentMaster::where('payment_status', 'overdue')
                ->whereHas('supplier', fn($q) => $q->where('organization_id', $orgId))
                ->count(),
            'increase_percentage' => 12,
        ];

        $suppliers = Supplier::where('is_active', true)
            ->where('organization_id', $orgId)
            ->get();

        $payments = SupplierPaymentMaster::with(['supplier', 'purchaseOrder', 'paymentDetails'])
            ->whereHas('supplier', fn($q) => $q->where('organization_id', $orgId))
            ->when(request('status'), function ($query, $status) {
                $query->where('payment_status', $status);
            })
            ->when(request('supplier'), function ($query, $supplierId) {
                is_numeric($supplierId) && $query->where('supplier_id', $supplierId);
            })
            ->when(request('search'), function ($query, $search) {
                $query->where('payment_number', 'like', '%' . $search . '%')
                    ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', '%' . $search . '%'));
            })
            ->orderBy('payment_date', 'desc')
            ->paginate(10);

        return view(
            'admin.suppliers.payments.index',
            compact('summary', 'suppliers', 'payments')
        );
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)
            ->where('organization_id', $this->getOrganizationId())
            ->get();
        return view('admin.suppliers.payments.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $orgId = $this->getOrganizationId();

        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
                function ($attribute, $value, $fail) use ($orgId) {
                    $exists = Supplier::where('id', $value)
                        ->where('organization_id', $orgId)
                        ->exists();
                    !$exists && $fail('Invalid supplier selected');
                },
            ],
            'payment_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|in:draft,pending,paid,overdue,partial',
        ]);

        $validated['payment_number'] = 'PAY-' . strtoupper(uniqid());
        $payment = SupplierPaymentMaster::create($validated);

        return redirect()->route('admin.suppliers.payments.show', $payment->id)
            ->with('success', 'Payment created successfully.');
    }

    public function show($id)
    {
        $payment = SupplierPaymentMaster::with(['supplier', 'purchaseOrder', 'paymentDetails'])
            ->findOrFail($id);
        $this->checkOrganization($payment);

        return view('admin.suppliers.payments.show', compact('payment'));
    }

    public function edit($id)
    {
        $payment = SupplierPaymentMaster::findOrFail($id);
        $this->checkOrganization($payment);

        $suppliers = Supplier::where('is_active', true)
            ->where('organization_id', $this->getOrganizationId())
            ->get();

        return view('admin.suppliers.payments.edit', compact('payment', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $payment = SupplierPaymentMaster::findOrFail($id);
        $this->checkOrganization($payment);

        $orgId = $this->getOrganizationId();

        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
                function ($attribute, $value, $fail) use ($orgId) {
                    $exists = Supplier::where('id', $value)
                        ->where('organization_id', $orgId)
                        ->exists();
                    !$exists && $fail('Invalid supplier selected');
                },
            ],
            'payment_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|in:draft,pending,paid,overdue,partial',
        ]);

        $payment->update($validated);

        return redirect()->route('admin.payments.show', $payment->id)
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy($id)
    {
        $payment = SupplierPaymentMaster::findOrFail($id);
        $this->checkOrganization($payment);

        $payment->delete();
        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    public function print($id)
    {
        $payment = SupplierPaymentMaster::with(['supplier', 'paymentDetails'])
            ->findOrFail($id);
        $this->checkOrganization($payment);

        return view('admin.suppliers.payments.print', compact('payment'));
    }
}
