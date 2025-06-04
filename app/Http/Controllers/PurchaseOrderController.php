<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\ItemMaster;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $items = ItemMaster::where('organization_id', $orgId)->get();
        
        $branches = Branch::where('organization_id', $orgId)
            ->where('is_active', true)
            ->get();
        

        $suppliers = Supplier::where('organization_id', $orgId)
            ->where('is_active', true)
            ->get();


        return view('admin.suppliers.purchase-orders.create', compact(
            'suppliers',
            'branches',
            'items'
        ));
    }

    public function store(Request $request)
    {
        // Add required validation rules for branch_id and supplier_id
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id', // Make branch_id required
            'supplier_id' => 'required|exists:suppliers,id', // Make supplier_id required  
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:order_date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|numeric|min:0.01', 
            'items.*.buying_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Create PO with organization_id from authenticated user
            $po = PurchaseOrder::create([
                'branch_id' => $validated['branch_id'],
                'organization_id' => Auth::user()->organization_id,
                'supplier_id' => $validated['supplier_id'],
                'user_id' => Auth::id(),
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'status' => PurchaseOrder::STATUS_PENDING,
                'total_amount' => 0, // Will be updated after items
                'paid_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'is_active' => true
            ]);

            $total = 0;

            // Create PO items
            foreach ($validated['items'] as $item) {
                $itemMaster = ItemMaster::findOrFail($item['item_id']);
                $lineTotal = $item['quantity'] * $item['buying_price'];
                $total += $lineTotal;

                $po->items()->create([
                    'item_id' => $item['item_id'],
                    'buying_price' => $item['buying_price'], 
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                    'po_status' => PurchaseOrderItem::STATUS_PENDING
                ]);
            }

            // Update PO total
            $po->update(['total_amount' => $total]);

            DB::commit();
            return redirect()->route('admin.purchase-orders.show', $po->po_id)
                ->with('success', 'Purchase order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error creating purchase order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $purchaseOrder = $this->baseQuery()
            ->with(['items.item', 'grns'])
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
            'items' => $itemsWithReceived,
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

        // Load existing items with item relationship
        $purchaseOrder->load('items.item');

        return view('admin.suppliers.purchase-orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'items'
        ));
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
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id', // Changed to item_id
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.buying_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'] ?? null,                
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Keep track of existing items to preserve received quantities
            $existingItems = $purchaseOrder->items->keyBy('po_detail_id');
            
            $total = 0;
            $newItems = [];

            foreach ($validated['items'] as $itemData) {
                $itemMaster = ItemMaster::find($itemData['item_id']);
                $lineTotal = $itemData['quantity'] * $itemData['buying_price'];
                $total += $lineTotal;

                // Create new item or update existing
                if (isset($itemData['po_detail_id'])) {
                    $item = PurchaseOrderItem::find($itemData['po_detail_id']);
                    $item->update([
                        'item_id' => $itemData['item_id'],
                        'buying_price' => $itemData['buying_price'],
                        'previous_buying_price' => $itemMaster->buying_price,
                        'quantity' => $itemData['quantity'],
                        'line_total' => $lineTotal,
                    ]);
                } else {
                    $newItems[] = new PurchaseOrderItem([
                        'item_id' => $itemData['item_id'],
                        'buying_price' => $itemData['buying_price'],
                        'previous_buying_price' => $itemMaster->buying_price,
                        'quantity' => $itemData['quantity'],
                        'line_total' => $lineTotal,
                        'po_status' => PurchaseOrderItem::STATUS_PENDING
                    ]);
                }

                // Update item master price
                $itemMaster->update(['buying_price' => $itemData['buying_price']]);
            }

            // Save new items
            if (!empty($newItems)) {
                $purchaseOrder->items()->saveMany($newItems);
            }

            // Remove deleted items
            $requestItemIds = collect($validated['items'])
                ->pluck('po_detail_id')
                ->filter()
                ->toArray();
                
            $itemsToDelete = $existingItems->keys()->diff($requestItemIds);
            if ($itemsToDelete->isNotEmpty()) {
                PurchaseOrderItem::whereIn('po_detail_id', $itemsToDelete)->delete();
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

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->items()->delete();
            $purchaseOrder->delete();
        });
        
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