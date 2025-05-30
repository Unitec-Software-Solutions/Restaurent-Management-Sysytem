<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupplierPaymentMaster;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\GrnMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierPaymentController extends Controller
{
    // Get current user's organization ID with strict validation
    protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access - organization not set');
        }
        return $user->organization_id;
    }

    // Base query for payments belonging to current organization
    protected function basePaymentQuery()
    {
        return SupplierPaymentMaster::whereHas('supplier', function ($q) {
            $q->where('organization_id', $this->getOrganizationId());
        });
    }

    // Base query for suppliers belonging to current organization
    protected function baseSupplierQuery()
    {
        return Supplier::where('organization_id', $this->getOrganizationId());
    }

    // Verify payment belongs to user's organization with more detailed checks
    protected function checkOrganization(SupplierPaymentMaster $payment)
    {
        if (!$payment->exists || !$payment->supplier || $payment->supplier->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access to payment details');
        }
    }

    public function index()
    {
        $orgId = $this->getOrganizationId();

        $summary = [
            'total_payments' => $this->basePaymentQuery()->sum('total_amount') ?? 0,
            'pending_payments' => $this->basePaymentQuery()
                ->where('payment_status', 'pending')
                ->sum('total_amount') ?? 0,
            'overdue_payments' => $this->basePaymentQuery()
                ->where('payment_status', 'overdue')
                ->sum('total_amount') ?? 0,
            'suppliers_count' => $this->baseSupplierQuery()->count(),
            'pending_count' => $this->basePaymentQuery()
                ->where('payment_status', 'pending')
                ->count(),
            'overdue_count' => $this->basePaymentQuery()
                ->where('payment_status', 'overdue')
                ->count(),
            'increase_percentage' => 12,
        ];

        $suppliers = $this->baseSupplierQuery()
            ->where('is_active', true)
            ->get();

        $payments = $this->basePaymentQuery()
            ->with(['supplier', 'purchaseOrder', 'paymentDetails'])
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
        $suppliers = $this->baseSupplierQuery()
            ->where('is_active', true)
            ->get();

        $branches = Branch::where('organization_id', $this->getOrganizationId())
            ->where('is_active', true)
            ->get();

        return view('admin.suppliers.payments.create', compact('suppliers', 'branches'));
    }

public function store(Request $request)
{
    $orgId = $this->getOrganizationId();
    
    $validated = $request->validate([
        'supplier_id' => ['required', 'exists:suppliers,id'],
        'branch_id' => 'required|exists:branches,id',
        'payment_date' => 'required|date',
        'total_amount' => 'required|numeric|min:0',
        'document_ids' => 'required|array',
        'document_ids.*' => 'string',
        'payment_status' => 'required|in:draft,pending,partial,paid',
        'method_type' => 'required|in:cash,bank_transfer,check,credit_card',
        'reference_number' => 'nullable|string'
    ]);

    DB::beginTransaction();
    try {
        // Create payment master
        $payment = SupplierPaymentMaster::create([
            'organization_id' => $orgId,
            'supplier_id' => $validated['supplier_id'],
            'branch_id' => $validated['branch_id'],
            'payment_number' => 'PAY-' . strtoupper(uniqid()),
            'payment_date' => $validated['payment_date'],
            'total_amount' => $validated['total_amount'],
            'allocated_amount' => 0, // Will be updated as we process documents
            'payment_status' => $validated['payment_status'],
            'processed_by' => auth()->id()
        ]);

        // Create payment details
        $payment->paymentDetails()->create([
            'method_type' => $validated['method_type'],
            'reference_number' => $validated['reference_number'],
            'amount' => $validated['total_amount']
        ]);

        $allocatedTotal = 0;

        // Process selected documents
        foreach ($validated['document_ids'] as $docString) {
            [$type, $id] = explode('_', $docString);

            if ($type === 'grn') {
                $grn = GrnMaster::find($id);
                
                if ($grn && $grn->organization_id === $orgId) {
                    $dueAmount = $grn->total_amount - ($grn->paid_amount ?? 0);
                    $amountToAllocate = min($dueAmount, $validated['total_amount'] - $allocatedTotal);

                    if ($amountToAllocate > 0) {
                        // Update GRN paid amount
                        $grn->paid_amount = ($grn->paid_amount ?? 0) + $amountToAllocate;
                        
                        // Update GRN payment status
                        if ($grn->paid_amount >= $grn->total_amount) {
                            $grn->payment_status = GrnMaster::PAYMENT_STATUS_PAID;
                        } elseif ($grn->paid_amount > 0) {
                            $grn->payment_status = GrnMaster::PAYMENT_STATUS_PARTIAL;
                        }
                        $grn->save();

                        // Create payment allocation record
                        $payment->grns()->attach($grn->grn_id, [
                            'amount' => $amountToAllocate,
                            'allocated_at' => now(),
                            'allocated_by' => auth()->id()
                        ]);

                        $allocatedTotal += $amountToAllocate;
                    }
                }
            }
        }

        // Update payment allocated amount
        $payment->update(['allocated_amount' => $allocatedTotal]);

        DB::commit();
        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment created successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()
            ->withInput()
            ->with('error', 'Error creating payment: ' . $e->getMessage());
    }
}



    //--------------------------------------------//
    public function show($id)
    {
        $payment = $this->basePaymentQuery()
            ->with(['supplier', 'purchaseOrder', 'paymentDetails'])
            ->findOrFail($id);

        $this->checkOrganization($payment);
        $branches = Branch::where('organization_id', $this->getOrganizationId())
            ->where('is_active', true)
            ->get();

        $suppliers = $this->baseSupplierQuery()
            ->where('is_active', true)
            ->get();

        //return view('admin.suppliers.payments.show', compact('payment', 'branches', 'suppliers'));
        return response()->json([
            'payment' => $payment,
            // 'branches' => $branches,
            // 'suppliers' => $suppliers,
        ]);
    }

    public function edit($id)
    {
        $payment = $this->basePaymentQuery()
            ->findOrFail($id);

        $this->checkOrganization($payment);

        $suppliers = $this->baseSupplierQuery()
            ->where('is_active', true)
            ->get();

        return view('admin.suppliers.payments.edit', compact('payment', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $payment = $this->basePaymentQuery()
            ->findOrFail($id);

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
                    if (!$exists) {
                        $fail('The selected supplier is invalid or not part of your organization');
                    }
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
        $payment = $this->basePaymentQuery()
            ->findOrFail($id);

        $this->checkOrganization($payment);

        $payment->delete();
        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    public function print($id)
    {
        $payment = $this->basePaymentQuery()
            ->with(['supplier', 'paymentDetails'])
            ->findOrFail($id);

        $this->checkOrganization($payment);

        return view('admin.suppliers.payments.print', compact('payment'));
    }
}
