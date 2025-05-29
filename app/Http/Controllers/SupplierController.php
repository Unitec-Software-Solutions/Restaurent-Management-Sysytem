<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\GrnMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access');
        }
        return $user->organization_id;
    }

    protected function checkOrganization(Supplier $supplier)
    {
        if ($supplier->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access to supplier details');
        }
    }

    public function index()
    {
        $orgId = $this->getOrganizationId();

        $suppliers = Supplier::forOrganization($orgId)
            ->when(request('search'), function ($query) {
                $query->where(function ($q) {
                    $search = '%' . request('search') . '%';
                    $q->where('name', 'like', $search)
                        ->orWhere('supplier_id', 'like', $search)
                        ->orWhere('contact_person', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when(request('status'), function ($query) {
                $status = request('status') === 'active';
                $query->where('is_active', $status);
            })
            ->latest()
            ->paginate(10);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'has_vat_registration' => 'boolean',
            'vat_registration_no' => 'nullable|required_if:has_vat_registration,true|string|max:50',
        ]);

        $validated['supplier_id'] = 'SUP-' . Str::upper(Str::random(6));
        $validated['is_active'] = true;
        $validated['organization_id'] = $this->getOrganizationId(); // Add organization ID

        Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $this->checkOrganization($supplier);
        $orgId = $this->getOrganizationId();

        $supplier->load([
            'purchaseOrders' => function ($query) use ($orgId) {
                $query->where('organization_id', $orgId)
                    ->latest()
                    ->take(5);
            },
            'transactions' => function ($query) use ($orgId) {
                $query->where('organization_id', $orgId)
                    ->latest()
                    ->take(5);
            }
        ]);

        $totalPurchases = $supplier->purchaseOrders()
            ->where('organization_id', $orgId)
            ->sum('total_amount');

        $totalPaid = $supplier->purchaseOrders()
            ->where('organization_id', $orgId)
            ->sum('paid_amount');

        $pendingPayment = $totalPurchases - $totalPaid;

        $stats = [
            'total_orders' => $supplier->purchaseOrders()->where('organization_id', $orgId)->count(),
            'total_purchases' => $totalPurchases,
            'total_paid' => $totalPaid,
            'pending_payment' => $pendingPayment
        ];

        return view('admin.suppliers.show', compact('supplier', 'stats'));
    }

    public function edit(Supplier $supplier)
    {
        $this->checkOrganization($supplier);
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->checkOrganization($supplier);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'has_vat_registration' => 'boolean',
            'vat_registration_no' => 'nullable|required_if:has_vat_registration,true|string|max:50',
            'is_active' => 'boolean'
        ]);

        $supplier->update($validated);

        return redirect()->route('admin.suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->checkOrganization($supplier);
        // Check if supplier has any associated orders
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Cannot delete supplier with associated purchase orders.');
        }

        $supplier->delete();
        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    public function purchaseOrders(Supplier $supplier)
    {
        $this->checkOrganization($supplier);
        $purchaseOrders = $supplier->purchaseOrders()
            ->with(['branch', 'user'])
            ->latest()
            ->paginate(10);

        // return view('admin.suppliers.purchase-orders', compact('supplier', 'purchaseOrders'));
    }
    // Get pending POs for a supplier
    public function pendingPos(Supplier $supplier)
    {
        $this->checkOrganization($supplier);

        $pos = PurchaseOrder::where('supplier_id', $supplier->id)
            ->where('status', '!=', 'Cancelled')
            ->get()
            ->map(function ($po) {
                return [
                    'po_id' => $po->po_id,
                    'po_number' => $po->po_number,
                    'order_date' => $po->order_date->format('Y-m-d'),
                    'total_amount' => $po->total_amount,
                    'paid_amount' => $po->paid_amount,
                    'due_amount' => $po->getBalanceAmount(),
                    'due_date' => $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : null,
                    'status' => $po->status
                ];
            });

        return response()->json($pos);
    }

    public function goodsReceived(Supplier $supplier)
    {
        $this->checkOrganization($supplier);
        $grns = GrnMaster::where('supplier_id', $supplier->id)
            ->with(['receivedByUser', 'verifiedByUser', 'purchaseOrder'])
            ->latest()
            ->paginate(10);

        // return view('admin.suppliers.grns', compact('supplier', 'grns'));
    }

    // Get pending GRNs for a supplier
    public function pendingGrns(Supplier $supplier)
    {
        $this->checkOrganization($supplier);

        $grns = GrnMaster::where('supplier_id', $supplier->id)
            ->where(function ($query) {
                $query->where('status', GrnMaster::STATUS_VERIFIED)
                    ->orWhere('status', GrnMaster::STATUS_PENDING);
            })
            ->with(['purchaseOrder'])
            ->get()
            ->map(function ($grn) {
                return [
                    'grn_id' => $grn->grn_id,
                    'grn_number' => $grn->grn_number,
                    'po_number' => $grn->purchaseOrder->po_number ?? null,
                    'received_date' => $grn->received_date->format('Y-m-d'),
                    'total_amount' => $grn->total_amount,
                    'paid_amount' => $grn->paid_amount ?? 0,
                    'due_amount' => $grn->total_amount - ($grn->paid_amount ?? 0),
                    'due_date' => $grn->received_date->addDays(30)->format('Y-m-d'), // Example 30-day terms
                    'status' => $grn->status
                ];
            });

        return response()->json($grns);
    }
}
