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
    public function index()
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        $orgId = $user->organization_id;

        $suppliers = Supplier::with('purchaseOrders')
            ->where('organization_id', $orgId)
            ->when(request('search'), function ($query) {
                $search = request('search');
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('supplier_id', 'like', '%' . $search . '%')
                      ->orWhere('contact_person', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when(request('status'), function ($query) {
                $status = request('status');
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate(10);

        // Statistics
        $totalSuppliers = Supplier::where('organization_id', $orgId)->count();
        $activeSuppliers = Supplier::where('organization_id', $orgId)->where('is_active', true)->count();
        $inactiveSuppliers = Supplier::where('organization_id', $orgId)->where('is_active', false)->count();
        $newSuppliersToday = Supplier::where('organization_id', $orgId)->whereDate('created_at', today())->count();

        return view('admin.suppliers.index', compact(
            'suppliers',
            'totalSuppliers',
            'activeSuppliers',
            'inactiveSuppliers',
            'newSuppliersToday'
        ));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

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
        $validated['organization_id'] = $user->organization_id;

        Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

public function show(Supplier $supplier)
{
    $user = Auth::user();

    if (!$user || !$user->organization_id || $supplier->organization_id !== $user->organization_id) {
        return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
    }

    $orgId = $user->organization_id;

    // Load relationships with organization scope
    $supplier->load([
        'organization',
        'purchaseOrders' => function ($query) use ($orgId) {
            $query->where('organization_id', $orgId)
                  ->with(['branch'])
                  ->latest()
                  ->take(5);
        },
        'transactions' => function ($query) use ($orgId, $supplier) {
            $query->where('organization_id', $orgId)
                  ->whereRaw('CAST(source_id AS TEXT) = ?', [(string) $supplier->getKey()]) // Explicit cast
                  ->latest()
                  ->take(5);
        }
    ]);

    // Calculate stats with organization scope
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
        $user = Auth::user();

        if (!$user || !$user->organization_id || $supplier->organization_id !== $user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id || $supplier->organization_id !== $user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

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
        $user = Auth::user();

        if (!$user || !$user->organization_id || $supplier->organization_id !== $user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        // Check if supplier has any associated orders within organization
        if ($supplier->purchaseOrders()->where('organization_id', $user->organization_id)->exists()) {
            return back()->with('error', 'Cannot delete supplier with associated purchase orders.');
        }

        $supplier->delete();
        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

public function purchaseOrders(Supplier $supplier)
{
    $user = Auth::user();

    if (!$user || !$user->organization_id || $supplier->organization_id !== $user->organization_id) {
        return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
    }

    $purchaseOrders = PurchaseOrder::where('organization_id', $user->organization_id)
        ->where('supplier_id', $supplier->id)
        ->with(['branch', 'user', 'grns']) // remove-002 later
        ->latest()
        ->paginate(10);

    return view('admin.suppliers.purchase-orders-supplier', [
        'supplier' => $supplier,
        'purchaseOrders' => $purchaseOrders,
        'organization' => $supplier->organization 
    ]);
}

    public function pendingPos(Supplier $supplier)
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id || $supplier->organization_id !== $user->organization_id) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $pos = PurchaseOrder::where('organization_id', $user->organization_id)
            ->where('supplier_id', $supplier->id)
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
                    'due_date' => $po->expected_delivery_date?->format('Y-m-d'),
                    'status' => $po->status
                ];
            });

        return response()->json($pos);
    }

public function goodsReceived(Supplier $supplier)
{
    $user = Auth::user();

    if (!$user || !$user->organization_id || $supplier->organization_id !== $user->organization_id) {
        return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
    }

    $grns = GrnMaster::where('organization_id', $user->organization_id)
        ->where('supplier_id', $supplier->id)
        ->with([
            'receivedByUser', 
            'verifiedByUser', 
            'purchaseOrder',
            'items' // Add this if you need to show GRN items
        ])
        ->latest()
        ->paginate(10);

    return view('admin.suppliers.grns-supplier', [
        'supplier' => $supplier,
        'grns' => $grns,
        'organization' => $supplier->organization // Pass organization to view
    ]);
}

    public function pendingGrns(Supplier $supplier)
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id || $supplier->organization_id !== $user->organization_id) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $grns = GrnMaster::where('organization_id', $user->organization_id)
            ->where('supplier_id', $supplier->id)
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
                    'due_date' => $grn->received_date->addDays(30)->format('Y-m-d'),
                    'status' => $grn->status
                ];
            });

        return response()->json($grns);
    }
}