<?php

namespace App\Http\Controllers;

use App\Models\GrnMaster;
use App\Models\GrnItem;
use App\Models\PurchaseOrder;
use App\Models\ItemMaster;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ItemTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GrnDashboardController extends Controller
{
    protected function getOrganizationId()
    {
        return Auth::user()->organization_id ?? abort(403, 'Unauthorized access');
    }

    public function index(Request $request)
    {
        $orgId = $this->getOrganizationId();
        
        // Set default dates if not provided
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Base query with relationships
        $query = GrnMaster::with(['supplier', 'branch', 'verifiedByUser', 'purchaseOrder'])
            ->where('organization_id', $orgId);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                  ->orWhere('delivery_note_number', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('purchaseOrder', function($q) use ($search) {
                      $q->where('po_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('received_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'received_date');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Get paginated results
        $grns = $query->paginate(10);

        // Get summary statistics (using a fresh query to avoid pagination interference)
        $statsQuery = GrnMaster::where('organization_id', $orgId);
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $statsQuery->whereBetween('received_date', [$request->start_date, $request->end_date]);
        }
        
        $stats = [
            'total_grns' => $statsQuery->count(),
            'pending_verification' => $statsQuery->clone()->where('status', GrnMaster::STATUS_PENDING)->count(),
            'verified_grns' => $statsQuery->clone()->where('status', GrnMaster::STATUS_VERIFIED)->count(),
            'rejected_grns' => $statsQuery->clone()->where('status', GrnMaster::STATUS_REJECTED)->count(),
            'total_amount' => $statsQuery->clone()->sum('total_amount'),
            'monthly_amount' => $statsQuery->clone()
                ->whereBetween('received_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_amount'),
        ];

        // Get filter options
        $branches = Branch::where('organization_id', $orgId)->active()->get();
        $suppliers = Supplier::where('organization_id', $orgId)->active()->get();

        return view('admin.suppliers.grn.index', compact(
            'grns',
            'stats',
            'branches',
            'suppliers',
            'startDate',
            'endDate'
        ));
    }

    public function edit(GrnMaster $grn)
{
    $orgId = $this->getOrganizationId();
    if ($grn->organization_id !== $orgId) {
        abort(403);
    }

    // Only allow editing of pending GRNs
    if (!$grn->isPending()) {
        return redirect()->route('admin.grn.show', $grn)
            ->with('error', 'Only pending GRNs can be edited');
    }

    $grn->load([
        'items.item',
        'purchaseOrder.items'
    ]);

    $suppliers = Supplier::where('organization_id', $orgId)
        ->active()
        ->get();

    $branches = Branch::where('organization_id', $orgId)
        ->active()
        ->get();

    $purchaseOrders = PurchaseOrder::where('organization_id', $orgId)
        ->where('supplier_id', $grn->supplier_id)
        ->where('status', 'Approved')
        ->with(['items'])
        ->get();

    return view('admin.suppliers.grn.edit', compact(
        'grn',
        'suppliers',
        'branches',
        'purchaseOrders'
    ));
}

public function update(Request $request, GrnMaster $grn)
{
    $orgId = $this->getOrganizationId();
    if ($grn->organization_id !== $orgId) {
        abort(403);
    }

    // Only allow updating of pending GRNs
    if (!$grn->isPending()) {
        return back()->with('error', 'Only pending GRNs can be updated');
    }

    $validated = $request->validate([
        'branch_id' => 'required|exists:branches,id',
        'supplier_id' => 'required|exists:suppliers,id',
        'received_date' => 'required|date',
        'delivery_note_number' => 'nullable|string|max:100',
        'invoice_number' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.item_id' => 'required|exists:item_master,id',
        'items.*.item_code' => 'required|exists:item_master,item_code',
        'items.*.po_detail_id' => 'nullable|exists:po_details,po_detail_id',
        'items.*.batch_no' => 'nullable|string|max:50',
        'items.*.ordered_quantity' => 'required|numeric|min:0',
        'items.*.received_quantity' => 'required|numeric|min:0',
        'items.*.accepted_quantity' => 'required|numeric|min:0|lte:items.*.received_quantity',
        'items.*.rejected_quantity' => 'required|numeric|min:0',
        'items.*.buying_price' => 'required|numeric|min:0',
        'items.*.manufacturing_date' => 'nullable|date',
        'items.*.expiry_date' => 'nullable|date|after:items.*.manufacturing_date',
        'items.*.rejection_reason' => 'nullable|required_if:items.*.rejected_quantity,>,0|string|max:255'
    ]);

    DB::beginTransaction();
    try {
        // Update GRN Master
        $grn->update([
            'branch_id' => $validated['branch_id'],
            'supplier_id' => $validated['supplier_id'],
            'received_date' => $validated['received_date'],
            'delivery_note_number' => $validated['delivery_note_number'],
            'invoice_number' => $validated['invoice_number'],
            'notes' => $validated['notes'],
        ]);

        // Remove existing items
        $grn->items()->delete();

        // Create new items
        $total = 0;
        foreach ($validated['items'] as $item) {
            $lineTotal = $item['accepted_quantity'] * $item['buying_price'];
            $total += $lineTotal;

            GrnItem::create([
                'grn_id' => $grn->grn_id,
                'po_detail_id' => $item['po_detail_id'] ?? null,
                'item_id' => $item['item_id'],
                'item_code' => $item['item_code'],
                'batch_no' => $item['batch_no'],
                'ordered_quantity' => $item['ordered_quantity'],
                'received_quantity' => $item['received_quantity'],
                'accepted_quantity' => $item['accepted_quantity'],
                'rejected_quantity' => $item['rejected_quantity'],
                'buying_price' => $item['buying_price'],
                'line_total' => $lineTotal,
                'manufacturing_date' => $item['manufacturing_date'],
                'expiry_date' => $item['expiry_date'],
                'rejection_reason' => $item['rejection_reason'],
            ]);
        }

        $grn->update(['total_amount' => $total]);

        DB::commit();
        return redirect()->route('admin.grn.show', $grn)
            ->with('success', 'GRN updated successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()
            ->with('error', 'Error updating GRN: ' . $e->getMessage());
    }
}


    public function create()
    {
        $orgId = $this->getOrganizationId();

        $items = ItemMaster::where('organization_id', $orgId)
            ->active()
            ->get();

        $suppliers = Supplier::where('organization_id', $orgId)
            ->active()
            ->get();

        $branches = Branch::where('organization_id', $orgId)
            ->active()
            ->get();

        // Get approved POs
        $purchaseOrders = PurchaseOrder::where('organization_id', $orgId)
            ->where('status', 'Approved')
            ->with(['supplier', 'items'])
            ->get();

        return view('admin.suppliers.grn.create', compact(
            'items',
            'suppliers',
            'branches',
            'purchaseOrders'
        ));
    }

    public function store(Request $request)
    {
        $orgId = $this->getOrganizationId();
    
        $validated = $request->validate([
            'po_id' => 'nullable|exists:po_master,po_id',
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'received_date' => 'required|date',
            'delivery_note_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.item_code' => 'required|exists:item_master,item_code',
            'items.*.po_detail_id' => 'nullable|exists:po_details,po_detail_id',
            'items.*.batch_no' => 'nullable|string|max:50',
            'items.*.ordered_quantity' => 'required|numeric|min:0',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.accepted_quantity' => 'required|numeric|min:0|lte:items.*.received_quantity',
            'items.*.rejected_quantity' => 'required|numeric|min:0',
            'items.*.buying_price' => 'required|numeric|min:0',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date|after:items.*.manufacturing_date',
            'items.*.rejection_reason' => 'nullable|required_if:items.*.rejected_quantity,>,0|string|max:255'
        ]);
    
        DB::beginTransaction();
        try {
            // Create GRN Master
            $grn = GrnMaster::create([
                'grn_number' => 'GRN-' . date('Ymd') . '-' . Str::random(4),
                'po_id' => $validated['po_id'],
                'branch_id' => $validated['branch_id'],
                'organization_id' => $orgId,
                'supplier_id' => $validated['supplier_id'],
                'received_by_user_id' => auth()->id(),
                'received_date' => $validated['received_date'],
                'delivery_note_number' => $validated['delivery_note_number'],
                'invoice_number' => $validated['invoice_number'],
                'notes' => $validated['notes'],
                'status' => GrnMaster::STATUS_PENDING,
                'is_active' => true,
                'created_by' => auth()->id()
            ]);
    
            // Create GRN Items and calculate total
            $total = 0;
            foreach ($validated['items'] as $item) {
                $lineTotal = $item['accepted_quantity'] * $item['buying_price'];
                $total += $lineTotal;
    
                GrnItem::create([
                    'grn_id' => $grn->grn_id,
                    'po_detail_id' => $item['po_detail_id'] ?? null,
                    'item_id' => $item['item_id'],
                    'item_code' => $item['item_code'],
                    'batch_no' => $item['batch_no'],
                    'ordered_quantity' => $item['ordered_quantity'],
                    'received_quantity' => $item['received_quantity'],
                    'accepted_quantity' => $item['accepted_quantity'],
                    'rejected_quantity' => $item['rejected_quantity'],
                    'buying_price' => $item['buying_price'],
                    'line_total' => $lineTotal,
                    'manufacturing_date' => $item['manufacturing_date'],
                    'expiry_date' => $item['expiry_date'],
                    'rejection_reason' => $item['rejection_reason'],
                ]);
            }
    
            $grn->update(['total_amount' => $total]);
    
            DB::commit();
            return redirect()->route('admin.grn.show', $grn)
                ->with('success', 'GRN created successfully.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error creating GRN: ' . $e->getMessage());
        }
    }


    public function show(GrnMaster $grn)
    {
        if ($grn->organization_id !== $this->getOrganizationId()) {
            abort(403);
        }

        $grn->load([
            'items.item',
            'items.purchaseOrderDetail',
            'purchaseOrder',
            'supplier',
            'branch',
            'receivedByUser',
            'verifiedByUser',
            'createdByUser'
        ]);

        return view('admin.suppliers.grn.show', compact('grn'));
    }

    public function verify(Request $request, GrnMaster $grn)
    {
        if ($grn->organization_id !== $this->getOrganizationId()) {
            abort(403);
        }
    
        if (!$grn->isPending()) {
            return back()->with('error', 'Only pending GRNs can be verified.');
        }
    
        $validated = $request->validate([
            'status' => 'required|in:' . GrnMaster::STATUS_VERIFIED . ',' . GrnMaster::STATUS_REJECTED,
            'notes' => 'nullable|string'
        ]);
    
        DB::beginTransaction();
        try {
            $grn->verified_by_user_id = auth()->id();
            $grn->verified_at = now();
            $grn->status = $validated['status'];
            $grn->notes = $validated['notes'] ?? $grn->notes;
            $grn->save();
    
            if ($validated['status'] === GrnMaster::STATUS_VERIFIED) {
                // Create stock transactions for accepted items
                $this->createStockTransactions($grn);
    
                if ($grn->po_id) {
                    $this->updatePurchaseOrderStatus($grn->purchaseOrder);
                }
            }
    
            DB::commit();
            return redirect()->route('admin.grn.show', $grn)
                ->with('success', 'GRN ' . strtolower($validated['status']) . ' successfully.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error verifying GRN: ' . $e->getMessage());
        }
    }

    protected function createStockTransactions(GrnMaster $grn)
{
    foreach ($grn->items as $grnItem) {
        if ($grnItem->accepted_quantity > 0) {
            ItemTransaction::create([
                'organization_id' => $grn->organization_id,
                'branch_id' => $grn->branch_id,
                'inventory_item_id' => $grnItem->item_id,
                'transaction_type' => 'purchase_order',
                'quantity' => $grnItem->accepted_quantity,
                'cost_price' => $grnItem->buying_price,
                'created_by_user_id' => auth()->id(),
                'is_active' => true,
                'source_id' => $grn->grn_id,
                'source_type' => GrnMaster::class,
                'notes' => 'Stock added from GRN #' . $grn->grn_number,
            ]);
        }
    }
}

    protected function updatePurchaseOrderStatus(PurchaseOrder $po)
    {
        $allItemsReceived = $po->items()
            ->get()
            ->every(function ($item) {
                $receivedQty = $item->grnItems()
                    ->whereHas('grn', function($q) {
                        $q->where('status', GrnMaster::STATUS_VERIFIED);
                    })
                    ->sum('accepted_quantity');
                return $receivedQty >= $item->quantity;
            });

        if ($allItemsReceived) {
            $po->status = 'Received';
            $po->save();
        }
    }
}