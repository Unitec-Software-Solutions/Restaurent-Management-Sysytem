<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\GrnMaster;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    use Exportable;

    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the suppliers page.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $admin->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        try {
            $query = Supplier::query();

            // Apply organization filter only for non-super admins
            if (!$isSuperAdmin && $admin->organization_id) {
                $query->where('organization_id', $admin->organization_id);
            }

            // Apply filters
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('supplier_id', 'ILIKE', "%{$search}%")
                      ->orWhere('contact_person', 'ILIKE', "%{$search}%")
                      ->orWhere('phone', 'ILIKE', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->input('status') === '1');
            }

            // Handle export
            if ($request->has('export')) {
                return $this->exportToExcel($request, $query, 'suppliers_export.xlsx', [
                    'Supplier ID', 'Name', 'Contact Person', 'Phone', 'Email', 'Address', 'Status', 'Created At'
                ]);
            }

            $suppliers = $query->latest()->paginate(15);

            // Statistics - improved for super admin
            $totalSuppliers = $isSuperAdmin ?
                Supplier::count() :
                Supplier::where('organization_id', $admin->organization_id)->count();

            $activeSuppliers = $isSuperAdmin ?
                Supplier::where('is_active', true)->count() :
                Supplier::where('organization_id', $admin->organization_id)->where('is_active', true)->count();

            $inactiveSuppliers = $isSuperAdmin ?
                Supplier::where('is_active', false)->count() :
                Supplier::where('organization_id', $admin->organization_id)->where('is_active', false)->count();

            $newSuppliersToday = $isSuperAdmin ?
                Supplier::whereDate('created_at', today())->count() :
                Supplier::where('organization_id', $admin->organization_id)->whereDate('created_at', today())->count();

            return view('admin.suppliers.index', compact(
                'suppliers',
                'totalSuppliers',
                'activeSuppliers',
                'inactiveSuppliers',
                'newSuppliersToday'
            ));
        } catch (\Exception $e) {
            Log::error('Supplier index error: ' . $e->getMessage());
            return view('admin.suppliers.index', ['suppliers' => collect()]);
        }
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to create suppliers.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        // Get organizations for super admin dropdown
        $organizations = $isSuperAdmin ? \App\Models\Organization::active()->get() : collect();

        return view('admin.suppliers.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to create suppliers.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        $validationRules = [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'has_vat_registration' => 'boolean',
            'vat_registration_no' => 'nullable|required_if:has_vat_registration,true|string|max:50',
        ];

        // Super admin must select organization
        if ($isSuperAdmin) {
            $validationRules['organization_id'] = 'required|exists:organizations,id';
        }

        $validated = $request->validate($validationRules);

        $validated['supplier_id'] = 'SUP-' . Str::upper(Str::random(6));
        $validated['is_active'] = true;

        // Set organization_id based on user type
        if ($isSuperAdmin) {
            // Super admin selected organization from dropdown
            $validated['organization_id'] = $request->organization_id;
        } else {
            // Regular admin uses their organization
            $validated['organization_id'] = $user->organization_id;
        }

        Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

public function show(Supplier $supplier)
{
    $user = Auth::guard('admin')->user();

    // Super admin check - bypass organization requirements
    $isSuperAdmin = $user->is_super_admin;

    if (!$user) {
        return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
    }

    // Super admin can view any supplier, non-super admin only their organization's suppliers
    if (!$isSuperAdmin && (!$user->organization_id || $supplier->organization_id !== $user->organization_id)) {
        return redirect()->route('admin.suppliers.index')->with('error', 'Unauthorized access.');
    }

    $orgId = $isSuperAdmin ? $supplier->organization_id : $user->organization_id;

    // Load relationships with organization scope
    $supplier->load([
        'organization',
        'purchaseOrders' => function ($query) use ($orgId) {
            $query->where('organization_id', $orgId)
                  ->with(['branch'])
                  ->latest()
                  ->take(5);
        }
    ]);

    // Get recent GRN transactions for this supplier
    $recentGrnTransactions = GrnMaster::where('organization_id', $orgId)
        ->where('supplier_id', $supplier->id)
        ->with(['purchaseOrder', 'receivedByUser', 'verifiedByUser'])
        ->latest()
        ->take(5)
        ->get();

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

    return view('admin.suppliers.show', compact('supplier', 'stats', 'recentGrnTransactions'));
}


    public function edit(Supplier $supplier)
    {
        $user = Auth::guard('admin')->user();

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        // Super admin can edit any supplier, non-super admin only their organization's suppliers
        if (!$isSuperAdmin && (!$user->organization_id || $supplier->organization_id !== $user->organization_id)) {
            return redirect()->route('admin.suppliers.index')->with('error', 'Unauthorized access.');
        }

        // Get organizations for super admin dropdown
        $organizations = $isSuperAdmin ? \App\Models\Organization::active()->get() : collect();

        return view('admin.suppliers.edit', compact('supplier', 'organizations'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $user = Auth::guard('admin')->user();

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        // Super admin can update any supplier, non-super admin only their organization's suppliers
        if (!$isSuperAdmin && (!$user->organization_id || $supplier->organization_id !== $user->organization_id)) {
            return redirect()->route('admin.suppliers.index')->with('error', 'Unauthorized access.');
        }

        $validationRules = [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'has_vat_registration' => 'boolean',
            'vat_registration_no' => 'nullable|required_if:has_vat_registration,true|string|max:50',
            'is_active' => 'boolean'
        ];

        // Super admin can change organization
        if ($isSuperAdmin) {
            $validationRules['organization_id'] = 'required|exists:organizations,id';
        }

        $validated = $request->validate($validationRules);

        $supplier->update($validated);

        return redirect()->route('admin.suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $user = Auth::guard('admin')->user();

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        // Super admin can delete any supplier, non-super admin only their organization's suppliers
        if (!$isSuperAdmin && (!$user->organization_id || $supplier->organization_id !== $user->organization_id)) {
            return redirect()->route('admin.suppliers.index')->with('error', 'Unauthorized access.');
        }

        $orgId = $isSuperAdmin ? $supplier->organization_id : $user->organization_id;

        // Check if supplier has any associated orders within organization
        if ($supplier->purchaseOrders()->where('organization_id', $orgId)->exists()) {
            return back()->with('error', 'Cannot delete supplier with associated purchase orders.');
        }

        $supplier->delete();
        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

public function purchaseOrders(Supplier $supplier)
{
    $user = Auth::guard('admin')->user();

    // Super admin check - bypass organization requirements
    $isSuperAdmin = $user->is_super_admin;

    if (!$user) {
        return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
    }

    // Super admin can view any supplier, non-super admin only their organization's suppliers
    if (!$isSuperAdmin && (!$user->organization_id || $supplier->organization_id !== $user->organization_id)) {
        return redirect()->route('admin.suppliers.index')->with('error', 'Unauthorized access.');
    }

    $orgId = $isSuperAdmin ? $supplier->organization_id : $user->organization_id;

    $purchaseOrders = PurchaseOrder::where('organization_id', $orgId)
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
        $user = Auth::guard('admin')->user();

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        if (!$user) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        // Super admin can access any supplier, non-super admin only their organization's suppliers
        if (!$isSuperAdmin && (!$user->organization_id || $supplier->organization_id !== $user->organization_id)) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $orgId = $isSuperAdmin ? $supplier->organization_id : $user->organization_id;

        $pos = PurchaseOrder::where('organization_id', $orgId)
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
    $user = Auth::guard('admin')->user();

    // Super admin check - bypass organization requirements
    $isSuperAdmin = $user->is_super_admin;

    if (!$user) {
        return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
    }

    // Super admin can access any supplier, non-super admin only their organization's suppliers
    if (!$isSuperAdmin && (!$user->organization_id || $supplier->organization_id !== $user->organization_id)) {
        return redirect()->route('admin.suppliers.index')->with('error', 'Unauthorized access.');
    }

    $orgId = $isSuperAdmin ? $supplier->organization_id : $user->organization_id;

    $grns = GrnMaster::where('organization_id', $orgId)
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
        $user = Auth::guard('admin')->user();

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        if (!$user) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        // Super admin can access any supplier, non-super admin only their organization's suppliers
        if (!$isSuperAdmin && (!$user->organization_id || $supplier->organization_id !== $user->organization_id)) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $orgId = $isSuperAdmin ? $supplier->organization_id : $user->organization_id;

        $grns = GrnMaster::where('organization_id', $orgId)
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

    /**
     * Get searchable columns for suppliers
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'supplier_id', 'contact_person', 'phone', 'email'];
    }
}
