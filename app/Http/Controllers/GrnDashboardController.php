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
use Illuminate\Support\Facades\Log;
use App\Models\Organizations;

class GrnDashboardController extends Controller
{
    protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access');
        }
        return $user->organization_id;
    }

    public function index(Request $request)
    {
        $orgId = $this->getOrganizationId();

        // Set default date range: 30 days back to today
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $query = GrnMaster::with(['supplier', 'branch', 'verifiedByUser', 'purchaseOrder'])
            ->where('organization_id', $orgId);

        if ($request->filled('search') ) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                    ->orWhere('delivery_note_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('purchaseOrder', function ($q) use ($search) {
                        $q->where('po_number', 'like', "%{$search}%");
                    });
            });
        }

        // Always apply date range filter
        $query->whereBetween('received_date', [$startDate, $endDate]);

        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $sortBy = $request->input('sort_by', 'received_date');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $grns = $query->paginate(10);

        $statsQuery = GrnMaster::where('organization_id', $orgId);

        // Always apply date range filter for stats
        $statsQuery->whereBetween('received_date', [$startDate, $endDate]);

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

        if (!$grn->isPending()) {
            return redirect()->route('admin.grn.show', $grn)
                ->with('error', 'Only pending GRNs can be edited');
        }

        $items = ItemMaster::where('organization_id', $orgId)
            ->active()
            ->get();

        $grn->load(['items.item', 'purchaseOrder.items']);

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

        foreach ($grn->items as $item) {
            if ($item->item) {
                $item->item_name = $item->item->name;
            }
        }

        return view('admin.suppliers.grn.edit', compact(
            'grn',
            'suppliers',
            'branches',
            'purchaseOrders',
            'items'
        ));
    }

    public function update(Request $request, GrnMaster $grn)
    {
        $orgId = $this->getOrganizationId();
        if ($grn->organization_id !== $orgId) {
            abort(403);
        }

        if (!$grn->isPending()) {
            return back()->with('error', 'Only pending GRNs can be updated');
        }

        Log::info('GRN Update Request Received', ['grn_id' => $grn->grn_id, 'data' => $request->all()]);

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
            'items.*.free_received_quantity' => 'nullable|numeric|min:0',
            'items.*.discount_received' => 'nullable|numeric|min:0',
            'items.*.rejected_quantity' => 'required|numeric|min:0',
            'items.*.buying_price' => 'required|numeric|min:0',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date|after:items.*.manufacturing_date',
            'items.*.rejection_reason' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $rejectedQuantity = $request->input("items.$index.rejected_quantity");
                    if ($rejectedQuantity > 0 && empty($value)) {
                        $fail("The rejection reason is required when rejected quantity is greater than 0.");
                    }
                },
            ],
        ]);

        Log::info('GRN Data Validated', ['grn_id' => $grn->grn_id, 'validated' => $validated]);

        DB::beginTransaction();
        try {
            $grn->update([
                'branch_id' => $validated['branch_id'],
                'supplier_id' => $validated['supplier_id'],
                'received_date' => $validated['received_date'],
                'delivery_note_number' => $validated['delivery_note_number'],
                'invoice_number' => $validated['invoice_number'],
                'notes' => $validated['notes'],
            ]);

            Log::info('GRN Master Updated', ['grn_id' => $grn->grn_id]);

            $grn->items()->delete();

            $total = 0;
            foreach ($validated['items'] as $item) {
                $itemMaster = \App\Models\ItemMaster::findOrFail($item['item_id']);
                $lineTotal = $item['accepted_quantity'] * $item['buying_price'] - ($item['discount_received'] ?? 0);
                $total += $lineTotal;

                \App\Models\GrnItem::create([
                    'grn_id' => $grn->grn_id,
                    'po_detail_id' => $item['po_detail_id'] ?? null,
                    'item_id' => $item['item_id'],
                    'item_code' => $itemMaster->item_code,
                    'item_name' => $itemMaster->name,
                    'batch_no' => $item['batch_no'] ?? (date('Y') . '-' . str_pad(\App\Models\GrnItem::max('id') + 1, 4, '0', STR_PAD_LEFT)),
                    'ordered_quantity' => $item['ordered_quantity'],
                    'received_quantity' => $item['received_quantity'],
                    'accepted_quantity' => $item['accepted_quantity'],
                    'rejected_quantity' => $item['rejected_quantity'],
                    'buying_price' => $item['buying_price'],
                    'line_total' => $lineTotal,
                    'manufacturing_date' => $item['manufacturing_date'],
                    'expiry_date' => $item['expiry_date'],
                    'rejection_reason' => $item['rejection_reason'],
                    'free_received_quantity' => $item['free_received_quantity'] ?? 0,
                    'discount_received' => $item['discount_received'] ?? 0,
                ]);
            }

            Log::info('GRN Items Created', ['grn_id' => $grn->grn_id, 'item_count' => count($validated['items'])]);

            $grn->update(['total_amount' => $total]);

            DB::commit();
            Log::info('GRN Update Committed', ['grn_id' => $grn->grn_id]);
            return redirect()->route('admin.grn.show', $grn)
                ->with('success', 'GRN updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GRN Update Failed', ['grn_id' => $grn->grn_id, 'error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Error updating GRN: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $orgId = $this->getOrganizationId();

        $items = ItemMaster::where('organization_id', $orgId)->active()->get();
        $suppliers = Supplier::where('organization_id', $orgId)->active()->get();
        $branches = Branch::where('organization_id', $orgId)->active()->get();
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
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'received_date' => 'required|date',
            'delivery_note_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.item_code' => 'nullable|exists:item_master,item_code',
            'items.*.batch_no' => 'nullable|string|max:50',
            'items.*.ordered_quantity' => 'required|numeric|min:0',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.buying_price' => 'required|numeric|min:0',
            'items.*.discount_received' => 'nullable|numeric|min:0',
            'items.*.free_received_quantity' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $grn = GrnMaster::create([
                'grn_number' => GrnMaster::generateGRNNumber($orgId),
                'branch_id' => $validated['branch_id'],
                'organization_id' => $orgId,
                'supplier_id' => $validated['supplier_id'],
                'received_by_user_id' => optional(Auth::user())->id,
                'received_date' => $validated['received_date'],
                'delivery_note_number' => $validated['delivery_note_number'],
                'invoice_number' => $validated['invoice_number'],
                'notes' => $validated['notes'],
                'status' => GrnMaster::STATUS_PENDING,
                'is_active' => true,
                'created_by' => optional(Auth::user())->id
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $itemMaster = \App\Models\ItemMaster::findOrFail($item['item_id']);
                $lineTotal = $item['received_quantity'] * $item['buying_price'] - ($item['discount_received'] ?? 0);
                $total += $lineTotal;

                \App\Models\GrnItem::create([
                    'grn_id' => $grn->grn_id,
                    'item_id' => $item['item_id'],
                    'item_code' => $itemMaster->item_code,
                    'item_name' => $itemMaster->name,
                    'batch_no' => $item['batch_no'] ?? (date('Y') . '-' . str_pad((\App\Models\GrnItem::max('grn_item_id') ?? 0) + 1, 4, '0', STR_PAD_LEFT)),
                    'ordered_quantity' => $item['ordered_quantity'],
                    'received_quantity' => $item['received_quantity'],
                    'accepted_quantity' => $item['received_quantity'],
                    'rejected_quantity' => 0,
                    'buying_price' => $item['buying_price'],
                    'line_total' => $lineTotal,
                    'free_received_quantity' => $item['free_received_quantity'] ?? 0,
                    'discount_received' => $item['discount_received'] ?? 0,
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

    public function print(GrnMaster $grn)
    {
        $orgId = $this->getOrganizationId();
        if ($grn->organization_id !== $orgId) {
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

        $organization = Organizations::find($orgId);
        $printedDate = now()->format('M d, Y h:i A');

        return view('admin.suppliers.grn.print', compact(
            'grn',
            'organization',
            'printedDate'
        ));
    }

    public function verify(Request $request, GrnMaster $grn)
    {
        Log::info('Starting GRN verification', ['grn_id' => $grn->grn_id]);

        if ($grn->organization_id !== $this->getOrganizationId()) {
            Log::error('Unauthorized access to GRN verification', ['grn_id' => $grn->grn_id]);
            abort(403);
        }

        if (!$grn->isPending()) {
            Log::warning('Attempt to verify a non-pending GRN', ['grn_id' => $grn->grn_id, 'status' => $grn->status]);
            return back()->with('error', 'Only pending GRNs can be verified.');
        }

        $validated = $request->validate([
            'status' => 'required|in:' . GrnMaster::STATUS_VERIFIED . ',' . GrnMaster::STATUS_REJECTED,
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            Log::info('Updating GRN status', ['grn_id' => $grn->grn_id, 'new_status' => $validated['status']]);

            $grn->verified_by_user_id = Auth::id(); // Fetch logged-in user ID
            $grn->verified_at = now();
            $grn->status = $validated['status'];
            $grn->notes = $validated['notes'] ?? $grn->notes;
            $grn->save();

            if ($validated['status'] === GrnMaster::STATUS_VERIFIED) {
                // Check if this GRN was created from a GTN
                $isFromGTN = (strpos($grn->notes ?? '', 'Internal transfer from GTN #') === 0) ||
                             (strpos($grn->delivery_note_number ?? '', 'GTN-') === 0);

                $transactionType = $isFromGTN ? 'gtn_stock_in' : 'grn_stock_in';
                $sourceType = $isFromGTN ? 'App\\Models\\GoodsTransferNote' : 'supplier';

                Log::info('Determining GRN source', [
                    'grn_id' => $grn->grn_id,
                    'is_from_gtn' => $isFromGTN,
                    'transaction_type' => $transactionType,
                    'notes' => $grn->notes,
                    'delivery_note' => $grn->delivery_note_number
                ]);

                foreach ($grn->items as $grnItem) {
                    $qty = $grnItem->accepted_quantity + ($grnItem->free_received_quantity ?? 0);

                    // Always use a positive value for quantity for stock in
                    $qty = abs($qty);

                    if ($qty > 0) {
                        // For GTN-based GRNs, ensure accepted_quantity is set
                        if ($isFromGTN && $grnItem->accepted_quantity <= 0) {
                            // If we're verifying a GTN-based GRN and accepted_quantity is 0,
                            // update it to the received_quantity
                            $grnItem->accepted_quantity = $grnItem->received_quantity;
                            $grnItem->save();

                            // Recalculate qty with the updated accepted_quantity
                            $qty = $grnItem->accepted_quantity + ($grnItem->free_received_quantity ?? 0);
                            Log::info('Updated GTN-based GRN item accepted quantity', [
                                'grn_id' => $grn->grn_id,
                                'item_id' => $grnItem->item_id,
                                'accepted_quantity' => $grnItem->accepted_quantity,
                                'new_qty' => $qty
                            ]);
                        }

                        // Create the item transaction with appropriate values
                        $transaction = ItemTransaction::create([
                            'organization_id' => $grn->organization_id,
                            'branch_id' => $grn->branch_id,
                            'inventory_item_id' => $grnItem->item_id,
                            'transaction_type' => $transactionType,
                            'quantity' => $qty, // Always positive for incoming stock
                            'received_quantity' => $grnItem->received_quantity,
                            'damaged_quantity' => $grnItem->rejected_quantity ?? 0,
                            // Use item buying_price even if 0 for GTN transfers
                            'cost_price' => $isFromGTN ? 0 : $grnItem->line_total,
                            'unit_price' => $isFromGTN ? 0 : $grnItem->buying_price,
                            'source_id' => $isFromGTN ? (string) $grn->delivery_note_number : (string) $grnItem->batch_no,
                            'source_type' => $sourceType,
                            'created_by_user_id' => Auth::id(),
                            'notes' => $isFromGTN
                                ? 'Stock received from GTN #' . $grn->delivery_note_number
                                : 'Stock added from GRN #' . $grn->grn_number,
                            'is_active' => true,
                        ]);

                        Log::info('Created stock transaction', [
                            'transaction_id' => $transaction->id,
                            'grn_id' => $grn->grn_id,
                            'item_id' => $grnItem->item_id,
                            'qty' => $qty,
                            'transaction_type' => $transactionType,
                            'is_from_gtn' => $isFromGTN
                        ]);
                    } else {
                        Log::warning('Skipped creating transaction for zero quantity item', [
                            'grn_id' => $grn->grn_id,
                            'item_id' => $grnItem->item_id,
                            'accepted_quantity' => $grnItem->accepted_quantity,
                            'free_received_quantity' => $grnItem->free_received_quantity ?? 0
                        ]);
                    }
                }

                if ($grn->po_id) {
                    $this->updatePurchaseOrderStatus($grn->purchaseOrder);
                }
            }

            DB::commit();
            Log::info('GRN verification completed successfully', ['grn_id' => $grn->grn_id]);
            return redirect()->route('admin.grn.show', $grn)
                ->with('success', 'GRN ' . strtolower($validated['status']) . ' successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during GRN verification', [
                'grn_id' => $grn->grn_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error verifying GRN: ' . $e->getMessage());
        }
    }

    protected function createStockTransactions(GrnMaster $grn)
    {
        foreach ($grn->items as $grnItem) {
            $qty = $grnItem->accepted_quantity + $grnItem->free_received_quantity;
            if ($qty > 0) {
                ItemTransaction::create([
                    'organization_id' => $grn->organization_id,
                    'branch_id' => $grn->branch_id,
                    'inventory_item_id' => $grnItem->item_id,
                    'transaction_type' => 'purchase_order',
                    'quantity' => $qty,
                    'cost_price' => $grnItem->buying_price,
                    'created_by_user_id' => optional(Auth::user())->id,
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
                    ->whereHas('grn', function ($q) {
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

