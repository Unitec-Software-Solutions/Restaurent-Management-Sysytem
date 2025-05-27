<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\ItemMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
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
        
        $purchaseOrders = PurchaseOrder::with(['supplier', 'branch'])
            ->where('organization_id', $orgId)
            ->when(request('search'), function($query) {
                $query->where('po_number', 'like', '%' . request('search') . '%')
                    ->orWhereHas('supplier', function($q) {
                        $q->where('name', 'like', '%' . request('search') . '%');
                    });
            })
            ->when(request('status'), function($query) {
                $query->where('status', request('status'));
            })
            ->latest()
            ->paginate(10);

        return view('admin.purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $orgId = $this->getOrganizationId();
        
        $suppliers = Supplier::where('organization_id', $orgId)
            ->where('is_active', true)
            ->get();
            
        $items = ItemMaster::where('organization_id', $orgId)
            ->where('is_active', true)
            ->get();

        return view('admin.purchase-orders.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $orgId = $this->getOrganizationId();

        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'manual_supplier_name' => 'required_without:supplier_id|string|nullable',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required|exists:item_master,item_code',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.buying_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::create([
                'organization_id' => $orgId,
                'branch_id' => auth()->user()->branch_id,
                'supplier_id' => $validated['supplier_id'],
                'manual_supplier_name' => $validated['manual_supplier_name'],
                'user_id' => auth()->id(),
                'po_number' => 'PO-' . date('Ymd') . '-' . random_int(1000, 9999),
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'notes' => $validated['notes'],
                'status' => 'Pending',
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['buying_price'];
                $total += $lineTotal;

                $po->items()->create([
                    'item_code' => $item['item_code'],
                    'quantity' => $item['quantity'],
                    'buying_price' => $item['buying_price'],
                    'line_total' => $lineTotal,
                ]);
            }

            $po->update(['total_amount' => $total]);
            
            DB::commit();
            
            return redirect()->route('admin.purchase-orders.show', $po)
                ->with('success', 'Purchase Order created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create Purchase Order: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->organization_id !== $this->getOrganizationId()) {
            abort(403);
        }

        $purchaseOrder->load(['supplier', 'items', 'branch', 'user']);
        
        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->organization_id !== $this->getOrganizationId()) {
            abort(403);
        }

        if (!$purchaseOrder->isPending()) {
            return back()->with('error', 'Only pending purchase orders can be edited.');
        }

        $orgId = $this->getOrganizationId();
        $suppliers = Supplier::where('organization_id', $orgId)->active()->get();
        $items = ItemMaster::where('organization_id', $orgId)->active()->get();

        return view('admin.purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'items'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->organization_id !== $this->getOrganizationId()) {
            abort(403);
        }

        if (!$purchaseOrder->isPending()) {
            return back()->with('error', 'Only pending purchase orders can be updated.');
        }

        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'manual_supplier_name' => 'required_without:supplier_id|string|nullable',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required|exists:item_master,item_code',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.buying_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'manual_supplier_name' => $validated['manual_supplier_name'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'notes' => $validated['notes'],
            ]);

            // Delete existing items
            $purchaseOrder->items()->delete();

            $total = 0;
            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['buying_price'];
                $total += $lineTotal;

                $purchaseOrder->items()->create([
                    'item_code' => $item['item_code'],
                    'quantity' => $item['quantity'],
                    'buying_price' => $item['buying_price'],
                    'line_total' => $lineTotal,
                ]);
            }

            $purchaseOrder->update(['total_amount' => $total]);
            
            DB::commit();
            
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase Order updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update Purchase Order: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->organization_id !== $this->getOrganizationId()) {
            abort(403);
        }

        if (!$purchaseOrder->isPending()) {
            return back()->with('error', 'Only pending purchase orders can be deleted.');
        }

        $purchaseOrder->delete();
        
        return redirect()->route('admin.purchase-orders.index')
            ->with('success', 'Purchase Order deleted successfully.');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->organization_id !== $this->getOrganizationId()) {
            abort(403);
        }

        if (!$purchaseOrder->isPending()) {
            return back()->with('error', 'Only pending purchase orders can be approved.');
        }

        $purchaseOrder->markAsApproved();
        
        return back()->with('success', 'Purchase Order approved successfully.');
    }
}