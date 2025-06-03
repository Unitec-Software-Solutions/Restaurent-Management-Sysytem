<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\ItemMaster;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    // Get current user's organization ID with validation
    protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access - organization not set');
        }
        return $user->organization_id;
    }

    // Base query for POs belonging to current organization
    protected function baseQuery()
    {
        return PurchaseOrder::where('organization_id', $this->getOrganizationId())
            ->with(['supplier', 'branch', 'user', 'items']);
    }

    public function index()
    {
        $orgId = $this->getOrganizationId();

        // Statistics for dashboard cards
        $stats = [
            'total_pos' => $this->baseQuery()->count(),
            'pending_pos' => $this->baseQuery()->where('status', 'Pending')->count(),
            'approved_pos' => $this->baseQuery()->where('status', 'Approved')->count(),
            'received_pos' => $this->baseQuery()->where('status', 'Received')->count(),
            'total_amount' => $this->baseQuery()->sum('total_amount'),
            'pending_amount' => $this->baseQuery()->where('status', 'Pending')->sum('total_amount'),
        ];

        // Get filterable data
        $suppliers = Supplier::where('organization_id', $orgId)
            ->where('is_active', true)
            ->get();

        $branches = Branch::where('organization_id', $orgId)
            ->where('is_active', true)
            ->get();

        // Filtered POs
        $purchaseOrders = $this->baseQuery()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('po_number', 'like', '%' . $search . '%')
                        ->orWhereHas('supplier', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(request('supplier'), function ($query, $supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when(request('branch'), function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when(request('from_date'), function ($query, $fromDate) {
                $query->whereDate('order_date', '>=', $fromDate);
            })
            ->when(request('to_date'), function ($query, $toDate) {
                $query->whereDate('order_date', '<=', $toDate);
            })
            ->orderBy('order_date', 'desc')
            ->paginate(15);

        return view('admin.suppliers.purchase-orders.index', compact(
            'stats',
            'purchaseOrders',
            'suppliers',
            'branches'
        ));
    }

    public function create()
    {
        $orgId = $this->getOrganizationId();

        $suppliers = Supplier::where('organization_id', $orgId)
            ->where('is_active', true)
            ->get();

        $branches = Branch::where('organization_id', $orgId)
            ->where('is_active', true)
            ->get();

        return view('admin.suppliers.purchase-orders.create', compact(
            'suppliers',
            'branches'
        ));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:order_date',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.buying_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        // Calculate total amount
        $totalAmount = collect($request->items)->sum(function ($item) {
            return $item['quantity'] * $item['buying_price'];
        });

        // Create PO
        $po = PurchaseOrder::create([
            'branch_id' => $validated['branch_id'],
            'organization_id' => $this->getOrganizationId(),
            'supplier_id' => $validated['supplier_id'],
            'user_id' => Auth::id(),
            'po_number' => 'PO-' . strtoupper(uniqid()),
            'order_date' => $validated['order_date'],
            'expected_delivery_date' => $validated['expected_delivery_date'],
            'status' => 'Pending',
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'notes' => $validated['notes'] ?? null,
            'is_active' => true
        ]);

        // Add PO items
        foreach ($validated['items'] as $item) {
            $po->items()->create([
                'item_code' => $item['item_code'],
                'batch_no' => $item['batch_no'] ?? null,
                'buying_price' => $item['buying_price'],
                'quantity' => $item['quantity'],
                'line_total' => $item['quantity'] * $item['buying_price'],
                'po_status' => 'Pending'
            ]);
        }

        return redirect()->route('admin.purchase-orders.show', $po->po_id)
            ->with('success', 'Purchase order created successfully!');
    }

    public function show($id)
    {
        $purchaseOrder = $this->baseQuery()
            ->with(['items.item', 'grns.grnItems'])
            ->findOrFail($id);

        // Calculate received quantities for each item
        $itemsWithReceived = $purchaseOrder->items->map(function ($item) {
            $receivedQuantity = $item->grnItems->sum('quantity_received');
            $item->received_quantity = $receivedQuantity;
            $item->pending_quantity = $item->quantity - $receivedQuantity;
            return $item;
        });

        return view('admin.suppliers.purchase-orders.show', [
            'po' => $purchaseOrder,
            'items' => $itemsWithReceived
        ]);
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

        return view('admin.suppliers.purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'items'));
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