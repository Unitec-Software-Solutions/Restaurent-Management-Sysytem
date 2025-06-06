<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupplierPaymentMaster;
use App\Models\SupplierPaymentDetail;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\GrnMaster;
use App\Models\PurchaseOrder;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SupplierPaymentController extends Controller
{
    protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access - organization not set');
        }
        return $user->organization_id;
    }

    protected function basePaymentQuery()
    {
        return SupplierPaymentMaster::whereHas('supplier', function ($q) {
            $q->where('organization_id', $this->getOrganizationId());
        });
    }

    protected function baseSupplierQuery()
    {
        return Supplier::where('organization_id', $this->getOrganizationId());
    }

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
            ->with(['supplier', 'paymentDetails', 'grns'])
            ->when(request('status'), function ($query, $status) {
                $query->where('payment_status', $status);
            })
            ->when(request('supplier'), function ($query, $supplierId) {
                is_numeric($supplierId) && $query->where('supplier_id', $supplierId);
            })
            ->when(request('search'), function ($query, $search) {
                $query->where('payment_number', 'like', '%' . $search . '%')
                    ->orWhereHas('supplier', fn($query) => $query->where('name', 'like', '%' . $search . '%'));
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
        'document_ids' => ['nullable', 'array'], // Make optional for drafts
        'document_ids.*' => ['string', 'regex:/^(grn|po)_[0-9]+$/'],
        'allocations' => ['nullable', 'array'], // Make optional for drafts
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
        $allocationErrors = [];

        // Process allocations for non-draft payments
        if ($validated['payment_status'] !== 'draft' && !empty($validated['allocations'])) {
            if (empty($validated['document_ids'])) {
                throw new \Exception('Document IDs are required for non-draft payments.');
            }

            foreach ($validated['allocations'] as $index => $allocation) {
                try {
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
                            PaymentAllocation::create([
                                'payment_id' => $payment->id,
                                'grn_id' => $grn->grn_id,
                                'po_id' => null,
                                'amount' => $amountToAllocate,
                                'allocated_at' => now(),
                                'allocated_by' => Auth::id(),
                            ]);

                            $allocatedTotal += $amountToAllocate;
                        } else {
                            $allocationErrors[] = "GRN {$grn->grn_number}: Invalid allocation amount or no due amount remaining.";
                        }
                    } elseif ($type === 'po') {
                        $po = PurchaseOrder::where('po_id', $id)
                            ->where('organization_id', $orgId)
                            ->where('supplier_id', $validated['supplier_id'])
                            ->where('status', 'approved')
                            ->first();

                        if (!$po) {
                            throw new \Exception("Invalid or unauthorized PO ID: {$id}");
                        }

                        $dueAmount = $po->total_amount - ($po->paid_amount ?? 0);
                        $amountToAllocate = min($allocation['amount'], $dueAmount, $validated['total_amount'] - $allocatedTotal);

                        if ($amountToAllocate > 0) {
                            // Update PO paid amount
                            $po->paid_amount = ($po->paid_amount ?? 0) + $amountToAllocate;
                            $po->calculatePaymentStatus();
                            $po->save();

                            // Create payment allocation record
                            PaymentAllocation::create([
                                'payment_id' => $payment->id,
                                'grn_id' => null,
                                'po_id' => $po->po_id,
                                'amount' => $amountToAllocate,
                                'allocated_at' => now(),
                                'allocated_by' => Auth::id(),
                            ]);

                            $allocatedTotal += $amountToAllocate;
                        } else {
                            $allocationErrors[] = "PO {$po->po_number}: Invalid allocation amount or no due amount remaining.";
                        }
                    }
                } catch (\Exception $e) {
                    $allocationErrors[] = "Allocation {$index}: {$e->getMessage()}";
                    continue; // Skip this allocation, continue with others
                }
            }

            // Validate total allocated amount
            if ($allocatedTotal > $validated['total_amount']) {
                throw new \Exception('Allocated amount exceeds payment total: ' . implode('; ', $allocationErrors));
            }

            // Update payment allocated amount and status
            $payment->allocated_amount = $allocatedTotal;
            if ($allocatedTotal >= $validated['total_amount']) {
                $payment->payment_status = 'paid';
            } elseif ($allocatedTotal > 0) {
                $payment->payment_status = 'partial';
            } elseif ($validated['payment_status'] !== 'draft' && $allocatedTotal == 0) {
                throw new \Exception('No valid allocations provided for non-draft payment: ' . implode('; ', $allocationErrors));
            }
            $payment->save();
        }

        DB::commit();
        $message = 'Payment created successfully.';
        if (!empty($allocationErrors)) {
            $message .= ' Some allocations failed: ' . implode('; ', $allocationErrors);
        }
        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', $message);
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
            ->with([
                'supplier',
                'branch',
                'paymentDetails',
                'grns' => function ($query) {
                    $query->with(['purchaseOrder']);
                },
                'allocations' => function ($query) {
                    $query->with(['grn', 'po']);
                },
                'processedBy'
            ])
            ->findOrFail($id);

        $this->checkOrganization($payment);

        $branches = Branch::where('organization_id', $this->getOrganizationId())
            ->where('is_active', true)
            ->get();

        $suppliers = $this->baseSupplierQuery()
            ->where('is_active', true)
            ->get();

        return view('admin.suppliers.payments.show', compact('payment', 'branches', 'suppliers'));
    }

    public function edit($id)
    {
        $payment = $this->basePaymentQuery()
            ->with(['supplier', 'branch', 'paymentDetails', 'allocations'])
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

            $payment->paymentDetails()->updateOrCreate(
                ['payment_id' => $payment->id],
                [
                    'method_type' => $validated['method_type'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'amount' => $validated['total_amount'],
                    'value_date' => $validated['value_date'] ?? $validated['payment_date'],
                ]
            );

            DB::commit();
            return redirect()->route('admin.payments.show', $payment->id)
                ->with('success', 'Payment updated successfully');
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
            foreach ($payment->allocations as $allocation) {
                if ($allocation->grn_id) {
                    $grn = GrnMaster::find($allocation->grn_id);
                    if ($grn) {
                        $grn->paid_amount = ($grn->paid_amount ?? 0) - $allocation->amount;
                        $grn->calculatePaymentStatus();
                        $grn->save();
                    }
                } elseif ($allocation->po_id) {
                    $po = PurchaseOrder::find($allocation->po_id);
                    if ($po) {
                        $po->paid_amount = ($po->paid_amount ?? 0) - $allocation->amount;
                        $po->calculatePaymentStatus();
                        $po->save();
                    }
                }
            }

            $payment->allocations()->delete();
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
            ->with(['supplier', 'branch', 'paymentDetails', 'allocations'])
            ->findOrFail($id);

        $this->checkOrganization($payment);

        return view('admin.suppliers.payments.print', compact('payment'));
    }

    public function getPendingGrns($supplierId)
    {
        $orgId = $this->getOrganizationId();

        $grns = GrnMaster::where('organization_id', $orgId)
            ->where('supplier_id', $supplierId)
            ->where('status', GrnMaster::STATUS_VERIFIED)
            ->whereRaw('total_amount > COALESCE(paid_amount, 0)')
            ->with(['purchaseOrder'])
            ->get()
            ->map(function ($grn) {
                return [
                    'grn_id' => $grn->grn_id,
                    'grn_number' => $grn->grn_number,
                    'po_number' => $grn->purchaseOrder ? $grn->purchaseOrder->po_number : null,
                    'received_date' => $grn->received_date->format('Y-m-d'),
                    'total_amount' => $grn->total_amount,
                    'paid_amount' => $grn->paid_amount ?? 0,
                    'due_amount' => $grn->total_amount - ($grn->paid_amount ?? 0),
                    'due_date' => $grn->due_date ? $grn->due_date->format('Y-m-d') : null,
                    'status' => $grn->payment_status ?? 'pending',
                ];
            });

        return response()->json($grns);
    }

    public function getPendingPos($supplierId)
    {
        $orgId = $this->getOrganizationId();

        $pos = PurchaseOrder::where('organization_id', $orgId)
            ->where('supplier_id', $supplierId)
            ->where('status', 'approved')
            ->whereRaw('total_amount > COALESCE(paid_amount, 0)')
            ->get()
            ->map(function ($po) {
                return [
                    'po_id' => $po->po_id,
                    'po_number' => $po->po_number,
                    'order_date' => $po->order_date->format('Y-m-d'),
                    'total_amount' => $po->total_amount,
                    'paid_amount' => $po->paid_amount ?? 0,
                    'due_amount' => $po->total_amount - ($po->paid_amount ?? 0),
                    'due_date' => $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : null,
                    'status' => $po->payment_status ?? 'pending',
                ];
            });

        return response()->json($pos);
    }
}