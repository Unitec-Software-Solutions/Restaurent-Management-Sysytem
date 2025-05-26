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

    public function index()
    {
        $orgId = $this->getOrganizationId();
        
        $suppliers = Supplier::query()
            ->when(request('search'), function($query) {
                $query->where(function($q) {
                    $search = '%' . request('search') . '%';
                    $q->where('name', 'like', $search)
                      ->orWhere('supplier_id', 'like', $search)
                      ->orWhere('contact_person', 'like', $search)
                      ->orWhere('phone', 'like', $search)
                      ->orWhere('email', 'like', $search);
                });
            })
            ->when(request('status'), function($query) {
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

        Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'purchaseOrders' => function($query) {
                $query->latest()->take(5);
            },
            'transactions' => function($query) {
                $query->latest()->take(5);
            }
        ]);

        $totalPurchases = $supplier->purchaseOrders()->sum('total_amount');
        $totalPaid = $supplier->purchaseOrders()->sum('paid_amount');
        $pendingPayment = $totalPurchases - $totalPaid;
        
        $stats = [
            'total_orders' => $supplier->purchaseOrders()->count(),
            'total_purchases' => $totalPurchases,
            'total_paid' => $totalPaid,
            'pending_payment' => $pendingPayment
        ];

        return view('admin.suppliers.show', compact('supplier', 'stats'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
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
        $purchaseOrders = $supplier->purchaseOrders()
            ->with(['branch', 'user'])
            ->latest()
            ->paginate(10);

        return view('admin.suppliers.purchase-orders', compact('supplier', 'purchaseOrders'));
    }

    public function goodsReceived(Supplier $supplier)
    {
        $grns = GrnMaster::where('supplier_id', $supplier->id)
            ->with(['receivedByUser', 'verifiedByUser', 'purchaseOrder'])
            ->latest()
            ->paginate(10);

        return view('admin.suppliers.grns', compact('supplier', 'grns'));
    }
}