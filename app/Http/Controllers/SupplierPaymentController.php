<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupplierPaymentMaster;
use App\Models\SupplierPaymentDetail;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\GrnMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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

        // Validation rules
        $rules = [
            'supplier_id' => ['required', 'exists:suppliers,id,organization_id,' . $orgId],
            'branch_id' => ['required', 'exists:branches,id,organization_id,' . $orgId],
            'payment_date' => ['required', 'date'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:draft,pending,partial,paid'],
            'method_type' => ['required', 'in:cash,bank_transfer,check,credit_card'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'value_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'document_ids' => ['required_unless:payment_status,draft', 'array'],
            'document_ids.*' => ['string', 'regex:/^(grn|po)_[0-9]+$/'],
            'allocations' => ['required_unless:payment_status,draft', 'array'],
            'allocations.*.document_id' => ['string', 'regex:/^(grn|po)_[0-9]+$/'],
            'allocations.*.amount' => ['numeric', 'min:0'],
        ];

        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            Log::error('Validation failed for payment creation: ' . $e->getMessage(), $request->all());
            return back()->withInput()->withErrors($e->errors());
        }

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
                'allocated_amount' => 0,
                'payment_status' => $validated['payment_status'],
                'processed_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create payment details
            $payment->paymentDetails()->create([
                'method_type' => $validated['method_type'],
                'reference_number' => $validated['reference_number'] ?? null,
                'amount' => $validated['total_amount'],
                'value_date' => $validated['value_date'] ?? $validated['payment_date'],
            ]);

            $allocatedTotal = 0;

            // Process allocations only if not a draft
            if ($validated['payment_status'] !== 'draft' && !empty($validated['allocations'])) {
                foreach ($validated['allocations'] as $allocation) {
                    [$type, $id] = explode('_', $allocation['document_id']);

                    if ($type === 'grn') {
                        $grn = GrnMaster::where('grn_id', $id)
                            ->where('organization_id', $orgId)
                            ->where('supplier_id', $validated['supplier_id'])
                            ->where('status', GrnMaster::STATUS_VERIFIED)
                            ->first();

                        if (!$grn) {
                            throw new \Exception("Invalid or unauthorized GRN ID: {$id}");
                        }

                        $dueAmount = $grn->total_amount - ($grn->paid_amount ?? 0);
                        $amountToAllocate = min($allocation['amount'], $dueAmount, $validated['total_amount'] - $allocatedTotal);

                        if ($amountToAllocate > 0) {
                            // Update GRN paid amount
                            $grn->paid_amount = ($grn->paid_amount ?? 0) + $amountToAllocate;
                            $grn->calculatePaymentStatus();
                            $grn->save();

                            // Create payment allocation record
                            $payment->grns()->attach($grn->grn_id, [
                                'amount' => $amountToAllocate,
                                'allocated_at' => now(),
                                'allocated_by' => Auth::id(),
                            ]);

                            $allocatedTotal += $amountToAllocate;
                        }
                    }
                    // Add support for POs if needed in the future
                }

                // Validate total allocated amount
                if ($allocatedTotal > $validated['total_amount']) {
                    throw new \Exception('Allocated amount exceeds payment total');
                }

                // Update payment allocated amount and status
                $payment->allocated_amount = $allocatedTotal;
                if ($allocatedTotal >= $validated['total_amount']) {
                    $payment->payment_status = 'paid';
                } elseif ($allocatedTotal > 0) {
                    $payment->payment_status = 'partial';
                }
                $payment->save();
            }

            DB::commit();
            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('success', 'Payment created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating payment: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'Error creating payment: ' . $e->getMessage());
        }
    }

public function show($id)
{
    $payment = $this->basePaymentQuery()
        ->with(['supplier', 'purchaseOrder', 'paymentDetails', 'grns'])
        ->findOrFail($id);

    $this->checkOrganization($payment);

    // Fetch branches for the dropdown
    $branches = Branch::where('organization_id', $this->getOrganizationId())
        ->where('is_active', true)
        ->get();

    // Fetch suppliers for the dropdown (if needed in the view)
    $suppliers = $this->baseSupplierQuery()
        ->where('is_active', true)
        ->get();

    return view('admin.suppliers.payments.show', compact('payment', 'branches', 'suppliers'));
}

    public function edit($id)
    {
        $payment = $this->basePaymentQuery()
            ->with(['supplier', 'paymentDetails', 'grns'])
            ->findOrFail($id);

        $this->checkOrganization($payment);

        $suppliers = $this->baseSupplierQuery()
            ->where('is_active', true)
            ->get();

        $branches = Branch::where('organization_id', $this->getOrganizationId())
            ->where('is_active', true)
            ->get();

        return view('admin.suppliers.payments.edit', compact('payment', 'suppliers', 'branches'));
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
                'exists:suppliers,id,organization_id,' . $orgId,
            ],
            'branch_id' => [
                'required',
                'exists:branches,id,organization_id,' . $orgId,
            ],
            'payment_date' => ['required', 'date'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:draft,pending,partial,paid'],
            'method_type' => ['required', 'in:cash,bank_transfer,check,credit_card'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'value_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $payment->update([
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $validated['branch_id'],
                'payment_date' => $validated['payment_date'],
                'total_amount' => $validated['total_amount'],
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update payment details
            $payment->paymentDetails()->updateOrCreate(
                ['payment_master_id' => $payment->id],
                [
                    'method_type' => $validated['method_type'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'amount' => $validated['total_amount'],
                    'value_date' => $validated['value_date'] ?? $validated['payment_date'],
                ]
            );

            DB::commit();
            return redirect()->route('admin.payments.show', $payment->id)
                ->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment: ' . $e->getMessage(), [
                'payment_id' => $id,
                'request' => $request->all(),
            ]);
            return back()->withInput()->with('error', 'Error updating payment: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $payment = $this->basePaymentQuery()
            ->findOrFail($id);

        $this->checkOrganization($payment);

        DB::beginTransaction();
        try {
            // Reverse GRN allocations
            foreach ($payment->grns as $grn) {
                $grn->paid_amount = ($grn->paid_amount ?? 0) - $payment->grns()->where('grn_id', $grn->grn_id)->first()->pivot->amount;
                $grn->calculatePaymentStatus();
                $grn->save();
            }

            $payment->grns()->detach();
            $payment->paymentDetails()->delete();
            $payment->delete();

            DB::commit();
            return redirect()->route('admin.payments.index')
                ->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting payment: ' . $e->getMessage(), ['payment_id' => $id]);
            return back()->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $payment = $this->basePaymentQuery()
            ->with(['supplier', 'paymentDetails', 'grns'])
            ->findOrFail($id);

        $this->checkOrganization($payment);

        return view('admin.suppliers.payments.print', compact('payment'));
    }
}