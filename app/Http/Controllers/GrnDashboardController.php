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
        
        // Base query with relationships
        $query = GrnMaster::with(['supplier', 'branch', 'verifiedByUser'])
            ->where('organization_id', $orgId);

        // Apply filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('received_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Get paginated results
        $grns = $query->latest('received_date')->paginate(10);

        // Get summary statistics
        $stats = [
            'total_grns' => $query->count(),
            'pending_verification' => $query->where('status', GrnMaster::STATUS_PENDING)->count(),
            'verified_grns' => $query->where('status', GrnMaster::STATUS_VERIFIED)->count(),
            'rejected_grns' => $query->where('status', GrnMaster::STATUS_REJECTED)->count(),
            'total_amount' => $query->sum('total_amount'),
            'monthly_amount' => $query->whereBetween('received_date', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->sum('total_amount'),
        ];

        // Get filter options
        $branches = Branch::where('organization_id', $orgId)->active()->get();
        $suppliers = Supplier::where('organization_id', $orgId)->active()->get();

        return view('admin.suppliers.grn.index', compact(
            'grns',
            'stats',
            'branches',
            'suppliers'
        ));
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
            'delivery_note_number' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.item_code' => 'required|exists:item_master,item_code',
            'items.*.po_detail_id' => 'nullable|exists:po_details,po_detail_id',
            'items.*.batch_no' => 'nullable|string',
            'items.*.ordered_quantity' => 'required|numeric|min:0',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.accepted_quantity' => 'required|numeric|min:0|lte:items.*.received_quantity',
            'items.*.rejected_quantity' => 'required|numeric|min:0',
            'items.*.buying_price' => 'required|numeric|min:0',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date|after:items.*.manufacturing_date',
            'items.*.rejection_reason' => 'nullable|required_if:items.*.rejected_quantity,>,0|string'
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

            // Create GRN Items
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
            return back()->with('error', 'Error creating GRN: ' . $e->getMessage());
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
            if ($validated['status'] === GrnMaster::STATUS_VERIFIED) {
                $grn->markAsVerified();
                
                // Update PO status if needed
                if ($grn->po_id) {
                    $this->updatePurchaseOrderStatus($grn->purchaseOrder);
                }
            } else {
                $grn->markAsRejected($validated['notes']);
            }

            DB::commit();
            return redirect()->route('admin.grn.show', $grn)
                ->with('success', 'GRN ' . strtolower($validated['status']) . ' successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error verifying GRN: ' . $e->getMessage());
        }
    }

    protected function updatePurchaseOrderStatus(PurchaseOrder $po)
    {
        $allItemsReceived = $po->items()
            ->get()
            ->every(function ($item) {
                $receivedQty = $item->grnItems()
                    ->where('status', GrnMaster::STATUS_VERIFIED)
                    ->sum('accepted_quantity');
                return $receivedQty >= $item->quantity;
            });

        if ($allItemsReceived) {
            $po->markAsReceived();
        }
    }
}