<?php

namespace App\Http\Controllers\Admin;


use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\ItemMaster;
use App\Models\Branch;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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

    public function index(Request $request)
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

        // Build query with filters
        $query = $this->baseQuery();

        // Apply filters using the trait
        $query = $this->applyFiltersToQuery($query, $request);

        // Handle export
        if ($request->has('export')) {
            return $this->exportToExcel($request, $query, 'purchase_orders_export.xlsx', [
                'PO Number', 'Supplier', 'Branch', 'Status', 'Total Amount', 'Order Date', 'Created At'
            ]);
        }

        // Filtered POs
        $purchaseOrders = $query->orderBy('order_date', 'desc')->paginate(15);

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
        Log::info('Store method called with data:', $request->all());

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:order_date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_masters,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.buying_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            Log::info('Validation passed:', $validated);

            $po = PurchaseOrder::create([
                'branch_id' => $validated['branch_id'],
                'organization_id' => Auth::user()->organization_id,
                'supplier_id' => $validated['supplier_id'],
                'user_id' => Auth::id(),
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'status' => PurchaseOrder::STATUS_PENDING,
                'total_amount' => 0,
                'paid_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'is_active' => true
            ]);

            Log::info('Purchase order created:', $po->toArray());

            $total = 0;
            foreach ($validated['items'] as $item) {
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

            $po->update(['total_amount' => $total]);

            DB::commit();
            Log::info('Transaction committed successfully.');

            return redirect()->route('admin.purchase-orders.show', $po->po_id)
                ->with('success', 'Purchase order created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating purchase order: ' . $e->getMessage());
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
            'items.*.item_id' => 'required|exists:item_masters,id', // Changed to item_id
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

    public function print($id)
    {
        $purchaseOrder = $this->baseQuery()
            ->with(['supplier', 'branch.organization', 'user', 'items.item'])
            ->findOrFail($id);

        $organization = $purchaseOrder->branch->organization;

        return view('admin.suppliers.purchase-orders.print', [
            'po' => $purchaseOrder,
            'items' => $purchaseOrder->items,
            'organization' => $organization,
            'printedDate' => now()->format('M d, Y H:i')
        ]);
    }

    /**
     * Get searchable columns for purchase orders
     */
    protected function getSearchableColumns(): array
    {
        return ['po_number'];
    }
}
