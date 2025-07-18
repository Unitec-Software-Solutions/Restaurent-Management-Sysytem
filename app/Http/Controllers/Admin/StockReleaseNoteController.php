<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockReleaseNoteMaster;
use App\Models\StockReleaseNoteItem;
use App\Models\ItemTransaction;
use App\Models\ItemMaster;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class StockReleaseNoteController extends Controller
{
    /**
     * Display a listing of the stock release notes.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $query = StockReleaseNoteMaster::with(['items', 'branch', 'organization']);

        if (!$admin->is_super_admin) {
            $query->where('organization_id', $admin->organization_id);
            if ($admin->branch_id) {
                $query->where('branch_id', $admin->branch_id);
            }
        }

        // Optional filters
        if ($request->filled('search')) {
            $query->where('srn_number', 'ilike', '%' . $request->search . '%');
        }
        if ($request->filled('release_type')) {
            $query->where('release_type', $request->release_type);
        }

        $notes = $query->orderByDesc('id')->paginate(20);

        return view('admin.inventory.srn.index', compact('notes'));
    }

    /**
     * Show the form for creating a new stock release note.
     * Loads items with current stock for the selected branch.
     */
    public function create(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $isSuperAdmin = $admin->is_super_admin;
        $organizations = $isSuperAdmin ? \App\Models\Organization::active()->get() : collect();

        $orgId = $isSuperAdmin
            ? $request->get('organization_id')
            : $admin->organization_id;

        $branches = $isSuperAdmin
            ? ($orgId ? Branch::where('organization_id', $orgId)->get() : collect())
            : Branch::where('organization_id', $orgId)->get();

        $branchId = $request->old('branch_id') ?? $request->get('branch_id');
        $items = [];

        if ($branchId && $orgId) {
            $items = ItemMaster::where('organization_id', $orgId)
                ->where('is_active', true)
                ->get()
                ->map(function ($item) use ($branchId) {
                    $stock = ItemTransaction::stockOnHand($item->id, $branchId);
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'item_code' => $item->item_code,
                        'unit_of_measurement' => $item->unit_of_measurement,
                        'current_stock' => $stock,
                        'selling_price' => $item->selling_price,
                    ];
                });
        }

        // SRN number generation
        $latest = StockReleaseNoteMaster::orderByDesc('id')->first();
        $nextSrnNumber = $latest ? 'SRN-' . str_pad($latest->id + 1, 6, '0', STR_PAD_LEFT) : 'SRN-000001';

        return view('admin.inventory.srn.create', compact('branches', 'items', 'branchId', 'organizations', 'nextSrnNumber'));
    }

    /**
     * Store a new stock release note and create item transactions (+/-) for each item.
     * Validates that requested quantity does not exceed current stock.
     */
    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $isSuperAdmin = $admin->is_super_admin ?? false;
        $orgId = $isSuperAdmin ? $request->organization_id : $admin->organization_id;

        Log::info('SRN Store Request', [
            'user_id' => $admin->id ?? 'N/A',
            'is_super_admin' => $isSuperAdmin,
            'org_id' => $orgId ?? 'N/A',
            'request_data' => $request->all()
        ]);

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'release_type' => 'required|string|max:50',
            'release_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.release_quantity' => 'required|numeric|min:0.01',
        ]);

        foreach ($request->items as $itemData) {
            $itemId = $itemData['item_id'] ?? 'N/A';
            $releaseQty = $itemData['release_quantity'] ?? 'N/A';
            $currentStock = ItemTransaction::stockOnHand($itemId, $request->branch_id ?? 'N/A');

            Log::info('SRN Item Stock Check', [
                'item_id' => $itemId,
                'release_quantity' => $releaseQty,
                'current_stock' => $currentStock
            ]);

            if ($releaseQty > $currentStock) {
                $item = ItemMaster::find($itemId);
                Log::warning('SRN Insufficient Stock', [
                    'item_id' => $itemId,
                    'item_name' => $item ? $item->name : 'N/A',
                    'requested' => $releaseQty,
                    'available' => $currentStock
                ]);
                return view('errors.generic', [
                    'errorTitle' => 'Insufficient Stock',
                    'errorCode' => '400',
                    'errorHeading' => 'Insufficient Stock',
                    'errorMessage' => "Item '" . ($item ? $item->name : 'N/A') . "' has only {$currentStock} units in stock. Requested: {$releaseQty}.",
                    'headerClass' => 'bg-gradient-warning',
                    'errorIcon' => 'fas fa-box-open',
                    'mainIcon' => 'fas fa-box-open',
                    'iconBgClass' => 'bg-yellow-100',
                    'iconColor' => 'text-yellow-500',
                    'buttonClass' => 'bg-[#FF9800] hover:bg-[#e68a00]',
                ]);
            }
        }

        DB::beginTransaction();

        try {
            Log::info('SRN Creating Master Record', [
                'srn_number' => $request->srn_number ?? 'N/A',
                'branch_id' => $request->branch_id ?? 'N/A',
                'organization_id' => $orgId ?? 'N/A',
                'released_by_user_id' => ($admin && $admin->id) ? $admin->id : null,
                'created_by' => ($admin && $admin->id) ? $admin->id : null
            ]);
            $note = StockReleaseNoteMaster::create([
                'srn_number' => $request->srn_number ?? 'SRN-' . now()->format('YmdHis'),
                'branch_id' => $request->branch_id ?? null,
                'organization_id' => $orgId ?? null,
                'released_by_user_id' => ($admin && $admin->id) ? $admin->id : null,
                'released_at' => now(),
                'release_date' => $request->release_date ?? null,
                'release_type' => $request->release_type ?? 'N/A',
                'notes' => $request->notes ?? 'N/A',
                'is_active' => true,
                'created_by' => ($admin && $admin->id) ? $admin->id : null,
                'status' => 'Pending', // Assuming 'Pending' is the initial status  // Completed
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $itemData) {
                $item = ItemMaster::find($itemData['item_id'] ?? null);
                $lineTotal = ($itemData['release_quantity'] ?? 0) * ($item ? $item->selling_price : 0);

                Log::info('SRN Creating Item', [
                    'srn_id' => $note->id ?? 'N/A',
                    'item_id' => $item ? $item->id : 'N/A',
                    'release_quantity' => $itemData['release_quantity'] ?? 'N/A'
                ]);

                // Create StockReleaseNoteItem
                StockReleaseNoteItem::create([
                    'srn_id' => $note->id ?? null,
                    'item_id' => $item ? $item->id : null,
                    'item_code' => $item ? $item->item_code : 'N/A',
                    'item_name' => $item ? $item->name : 'N/A',
                    'release_quantity' => $itemData['release_quantity'] ?? 0,
                    'unit_of_measurement' => $item ? $item->unit_of_measurement : 'N/A',
                    'release_price' => $item ? $item->selling_price : 0,
                    'line_total' => $lineTotal,
                    'batch_no' => $itemData['batch_no'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'notes' => $itemData['notes'] ?? 'N/A',
                ]);

                $transactionType = $this->getTransactionTypeForRelease($request->release_type ?? 'N/A');
                $quantity = $this->getSignedQuantity($transactionType, $itemData['release_quantity'] ?? 0);

                Log::info('SRN Creating ItemTransaction', [
                    'item_id' => $item ? $item->id : 'N/A',
                    'transaction_type' => $transactionType,
                    'quantity' => $quantity
                ]);

                ItemTransaction::create([
                    'organization_id' => $orgId ?? null,
                    'branch_id' => $request->branch_id ?? null,
                    'inventory_item_id' => $item ? $item->id : null,
                    'item_master_id' => $item ? $item->id : null,
                    'transaction_type' => $transactionType,
                    'quantity' => $quantity,
                    'unit_price' => $item ? $item->selling_price : 0,
                    'total_amount' => $lineTotal,
                    'reference_type' => 'stock_release_note',
                    'reference_id' => $note->id ?? null,
                    'batch_number' => $itemData['batch_no'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'notes' => $itemData['notes'] ?? 'N/A',
                    'created_by_user_id' => ($admin && $admin->id) ? $admin->id : null,
                    'is_active' => true,
                ]);

                $totalAmount += $lineTotal;
            }

            $note->update(['total_amount' => $totalAmount]);

            DB::commit();

            Log::info('SRN Created Successfully', [
                'srn_id' => $note->id ?? 'N/A',
                'total_amount' => $totalAmount
            ]);

            return redirect()->route('admin.inventory.srn.index')
                ->with('success', 'Stock release note created and transactions recorded.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SRN Store Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return view('errors.generic', [
                'errorTitle' => 'Stock Release Failed',
                'errorCode' => '500',
                'errorHeading' => 'Stock Release Failed',
                'errorMessage' => $e->getMessage(),
                'headerClass' => 'bg-gradient-warning',
                'errorIcon' => 'fas fa-box-open',
                'mainIcon' => 'fas fa-box-open',
                'iconBgClass' => 'bg-yellow-100',
                'iconColor' => 'text-yellow-500',
                'buttonClass' => 'bg-[#FF9800] hover:bg-[#e68a00]',
            ]);
        }
    }

    /**
     * Display the specified stock release note.
     */
    public function show($id)
    {
        $admin = Auth::guard('admin')->user();
        $note = StockReleaseNoteMaster::with(['items', 'branch', 'organization'])->findOrFail($id);

        if (!$admin->is_super_admin && $note->organization_id !== $admin->organization_id) {
            abort(403, 'Unauthorized access');
        }

        return view('admin.inventory.srn.show', compact('note'));
    }

    /**
     * Show the form for editing the specified stock release note.
     */
    public function edit($id)
    {
        $admin = Auth::guard('admin')->user();
        $note = StockReleaseNoteMaster::with('items')->findOrFail($id);

        if (!$admin->is_super_admin && $note->organization_id !== $admin->organization_id) {
            abort(403, 'Unauthorized access');
        }

        $branches = $admin->is_super_admin
            ? Branch::all()
            : Branch::where('organization_id', $admin->organization_id)->get();

        $items = $admin->is_super_admin
            ? ItemMaster::all()
            : ItemMaster::where('organization_id', $admin->organization_id)->get();

        return view('admin.inventory.srn.edit', compact('note', 'branches', 'items'));
    }

    /**
     * Update the specified stock release note.
     */
    public function update(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        $note = StockReleaseNoteMaster::findOrFail($id);

        if (!$admin->is_super_admin && $note->organization_id !== $admin->organization_id) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'release_type' => 'required|string|max:50',
            'release_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.release_quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            $note->update([
                'branch_id' => $request->branch_id,
                'release_type' => $request->release_type,
                'release_date' => $request->release_date,
                'notes' => $request->notes,
            ]);

            // Remove old items and transactions
            $note->items()->delete();
            ItemTransaction::where('reference_type', 'stock_release_note')->where('reference_id', $note->id)->delete();

            $totalAmount = 0;

            foreach ($request->items as $itemData) {
                $item = ItemMaster::find($itemData['item_id']);
                $lineTotal = ($itemData['release_quantity'] ?? 0) * ($item->selling_price ?? 0);

                StockReleaseNoteItem::create([
                    'srn_id' => $note->id,
                    'item_id' => $item->id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->name,
                    'release_quantity' => $itemData['release_quantity'],
                    'unit_of_measurement' => $item->unit_of_measurement,
                    'release_price' => $item->selling_price,
                    'line_total' => $lineTotal,
                    'batch_no' => $itemData['batch_no'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $transactionType = $this->getTransactionTypeForRelease($request->release_type);
                $quantity = $this->getSignedQuantity($transactionType, $itemData['release_quantity']);

                ItemTransaction::create([
                    'organization_id' => $note->organization_id,
                    'branch_id' => $note->branch_id,
                    'inventory_item_id' => $item->id,
                    'item_master_id' => $item->id,
                    'transaction_type' => $transactionType,
                    'quantity' => $quantity,
                    'unit_price' => $item->selling_price,
                    'total_amount' => $lineTotal,
                    'reference_type' => 'stock_release_note',
                    'reference_id' => $note->id,
                    'batch_number' => $itemData['batch_no'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                    'created_by_user_id' => $admin->id,
                    'is_active' => true,
                ]);

                $totalAmount += $lineTotal;
            }

            $note->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('admin.inventory.srn.show', $note->id)
                ->with('success', 'Stock release note updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return view('errors.generic', [
                'errorTitle' => 'Update Failed',
                'errorCode' => '500',
                'errorHeading' => 'Stock Release Update Failed',
                'errorMessage' => $e->getMessage(),
                'headerClass' => 'bg-gradient-warning',
                'errorIcon' => 'fas fa-box-open',
                'mainIcon' => 'fas fa-box-open',
                'iconBgClass' => 'bg-yellow-100',
                'iconColor' => 'text-yellow-500',
                'buttonClass' => 'bg-[#FF9800] hover:bg-[#e68a00]',
            ]);
        }
    }

    /**
     * Helper: Get transaction type for release type.
     */
    protected function getTransactionTypeForRelease($releaseType)
    {
        $map = [
            'wastage' => 'waste',
            'sale' => 'sales_order',
            'transfer' => 'transfer_out',
            'usage' => 'usage',
            'kit' => 'kit_release',
            'staff_usage' => 'staff_usage',
            'internal_usage' => 'internal_usage',
            'other' => 'other_release',
        ];
        return $map[$releaseType] ?? 'usage';
    }

    /**
     * Helper: Get signed quantity for transaction type.
     */
    protected function getSignedQuantity($transactionType, $quantity)
    {
        $outTypes = ['waste', 'sales_order', 'transfer_out', 'usage', 'kit_release', 'staff_usage', 'internal_usage', 'other_release'];
        return in_array($transactionType, $outTypes) ? -abs($quantity) : abs($quantity);
    }

    /**
     * AJAX endpoint for items with stock for branch/org
     */
    public function itemsWithStock(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $isSuperAdmin = $admin->is_super_admin;
        $orgId = $isSuperAdmin
            ? $request->get('organization_id')
            : $admin->organization_id;

        $branchId = $request->get('branch_id');
        if (!$branchId) {
            return response()->json([]);
        }

        $items = ItemMaster::where('organization_id', $orgId)
            ->where('is_active', true)
            ->get()
            ->map(function ($item) use ($branchId) {
                $stock = ItemTransaction::stockOnHand($item->id, $branchId);
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit_of_measurement' => $item->unit_of_measurement,
                    'current_stock' => $stock,
                    'selling_price' => $item->selling_price,
                ];
            })
            ->filter(fn($item) => $item['current_stock'] > 0)
            ->values();

        return response()->json($items);
    }
}
