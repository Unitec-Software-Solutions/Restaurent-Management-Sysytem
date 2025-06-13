<?php

namespace App\Http\Controllers;

use App\Models\GoodsTransferNote;
use App\Models\GoodsTransferItem;
use App\Models\Branch;
use App\Models\ItemMaster;
use App\Models\Employee;
use App\Models\Organizations;
use App\Models\ItemTransaction;
use App\Models\GrnMaster;
use App\Services\GTNService;
use App\Http\Requests\GTNStoreRequest;
use App\Http\Requests\GTNUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class GoodsTransferNoteController extends Controller
{
    protected $gtnService;

    public function __construct(GTNService $gtnService)
    {
        $this->gtnService = $gtnService;
    }
    public function index()
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        $orgId = $admin->organization_id;

        // Set default date range: 30 days back to today
        $startDate = request('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));

        // Apply filters from request
        $query = GoodsTransferNote::with(['fromBranch', 'toBranch', 'createdBy'])
            ->where('organization_id', $orgId);

        if ($from = request('from_branch_id')) {
            $query->where('from_branch_id', $from);
        }
        if ($to = request('to_branch_id')) {
            $query->where('to_branch_id', $to);
        }
        if (($status = request('status')) && $status !== 'all') {
            $query->where('status', $status);
        }

        // Always apply date range filter
        $query->whereBetween('transfer_date', [$startDate, $endDate]);

        $gtns = $query->latest()->paginate(15);

        $organization = Organizations::find($orgId);
        $branches = Branch::where('organization_id', $orgId)->active()->get();
        $items = ItemMaster::where('organization_id', $orgId)->active()->get();

        return view('admin.inventory.gtn.index', compact('gtns', 'organization', 'branches', 'items', 'startDate', 'endDate'));
    }

    public function create()
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        $orgId = $admin->organization_id;

        // Generate next GTN number (simple example: GTN-YYYYMMDD-XXX)
        $lastGtn = GoodsTransferNote::where('organization_id', $orgId)->orderByDesc('gtn_id')->first();
        $datePrefix = now()->format('Ymd');
        $nextSeq = 1;
        if ($lastGtn && str_starts_with($lastGtn->gtn_number, 'GTN-' . $datePrefix)) {
            $lastSeq = (int)substr($lastGtn->gtn_number, -3);
            $nextSeq = $lastSeq + 1;
        }
        $nextGtnNumber = 'GTN-' . $datePrefix . '-' . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);

        return view('admin.inventory.gtn.create', [
            'branches' => Branch::where('organization_id', $orgId)->active()->get(),
            'items' => ItemMaster::where('organization_id', $orgId)->active()->get(),
            'employees' => Employee::where('organization_id', $orgId)->get(),
            'organization' => Organizations::find($orgId),
            'nextGtnNumber' => $nextGtnNumber,
        ]);
    }

    public function store(GTNStoreRequest $request)
    {
        try {
            $gtn = $this->gtnService->createGTN($request->validated());
            return redirect()
                ->route('admin.inventory.gtn.show', $gtn->gtn_id)
                ->with('success', 'GTN Created Successfully');
        } catch (Exception $e) {
            Log::error('GTN creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return back()->withErrors('Failed to create GTN. Check logs for details.')->withInput();
        }
    }

    public function show($id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        $gtn = GoodsTransferNote::with(['fromBranch', 'toBranch', 'items'])
            ->where('organization_id', $admin->organization_id)
            ->findOrFail($id);

        return view('admin.inventory.gtn.show', compact('gtn'));
    }

    public function edit($id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        $orgId = $admin->organization_id;

        $gtn = GoodsTransferNote::with('items')
            ->where('organization_id', $orgId)
            ->findOrFail($id);

        return view('admin.inventory.gtn.edit', [
            'gtn' => $gtn,
            'branches' => Branch::where('organization_id', $orgId)->active()->get(),
            'items' => ItemMaster::where('organization_id', $orgId)->active()->get(),
            'employees' => Employee::where('organization_id', $orgId)->get(),
            'organization' => Organizations::find($orgId)
        ]);
    }

    public function update(GTNUpdateRequest $request, $id)
    {
        try {
            $this->gtnService->updateGTN($id, $request->validated());
            return redirect()->route('admin.inventory.gtn.index')->with('success', 'GTN Updated Successfully');
        } catch (Exception $e) {
            Log::error('GTN update failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return back()->withErrors('Failed to update GTN: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified GTN from storage
     */
    public function destroy($id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        try {
            DB::transaction(function () use ($id, $admin) {
                $gtn = GoodsTransferNote::where('organization_id', $admin->organization_id)
                    ->findOrFail($id);

                // Only allow deletion of pending GTNs
                if ($gtn->status !== 'Pending') {
                    throw new Exception('Only pending GTNs can be deleted.');
                }

                // Delete related items first
                $gtn->items()->delete();

                // Delete the GTN
                $gtn->delete();

                Log::info('GTN deleted', ['gtn_id' => $gtn->gtn_id, 'user_id' => $admin->id]);
            });

            return redirect()->route('admin.inventory.gtn.index')
                ->with('success', 'GTN deleted successfully.');
        } catch (Exception $e) {
            Log::error('GTN deletion failed', [
                'gtn_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to delete GTN: ' . $e->getMessage());
        }
    }

    /**
     * Change GTN status and handle stock transactions
     */
    public function changeStatus(Request $request, $id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'status' => 'required|in:Confirmed,Approved,Verified',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::transaction(function () use ($request, $id, $admin) {
                $gtn = GoodsTransferNote::where('organization_id', $admin->organization_id)
                    ->findOrFail($id);

                if (!$this->gtnService->canChangeStatus($gtn, $request->status)) {
                    throw new Exception('GTN status cannot be changed from current state.');
                }

                // Update GTN status
                $gtn->update([
                    'status' => $request->status,
                    'approved_by' => $admin->id,
                    'notes' => $request->notes ?? $gtn->notes
                ]);

                // Process stock transactions
                $this->gtnService->processStockTransfer($gtn);

                Log::info('GTN status changed', [
                    'gtn_id' => $gtn->gtn_id,
                    'new_status' => $request->status,
                    'user_id' => $admin->id
                ]);
            });

            return redirect()->route('admin.inventory.gtn.show', $id)
                ->with('success', 'GTN status updated successfully and stock transfer processed.');
        } catch (Exception $e) {
            Log::error('GTN status change failed', [
                'gtn_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to update GTN status: ' . $e->getMessage());
        }
    }    /**
     * Get items with stock for a specific branch (AJAX endpoint)
     */
    public function getItemsWithStock(Request $request)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $branchId = $request->get('branch_id');

        if (!$branchId) {
            return response()->json(['error' => 'Branch ID is required'], 400);
        }

        // Verify branch belongs to user's organization
        $branch = Branch::where('id', $branchId)
            ->where('organization_id', $admin->organization_id)
            ->first();

        if (!$branch) {
            return response()->json(['error' => 'Invalid branch'], 400);
        }

        try {
            $items = $this->gtnService->getItemsWithStock($branchId, $admin->organization_id);
            return response()->json($items);
        } catch (Exception $e) {
            Log::error('Error fetching items with stock', [
                'branch_id' => $branchId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch items'], 500);
        }
    }

    /**
     * Search items with stock for autocomplete (AJAX endpoint)
     */
    public function searchItems(Request $request)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'search' => 'nullable|string|max:100'
        ]);

        $branchId = $request->get('branch_id');
        $search = $request->get('search', '');

        // Verify branch belongs to user's organization
        $branch = Branch::where('id', $branchId)
            ->where('organization_id', $admin->organization_id)
            ->first();

        if (!$branch) {
            return response()->json(['error' => 'Invalid branch'], 400);
        }

        try {
            $items = $this->gtnService->searchItemsWithStock($branchId, $admin->organization_id, $search);
            return response()->json($items);
        } catch (Exception $e) {
            Log::error('Error searching items with stock', [
                'branch_id' => $branchId,
                'search' => $search,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to search items'], 500);
        }
    }

    /**
     * Get current stock for specific item and branch (AJAX endpoint)
     */
    public function getItemStock(Request $request)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'item_id' => 'required|exists:item_master,id',
            'branch_id' => 'required|exists:branches,id'
        ]);

        try {
            $stock = $this->gtnService->getItemStock(
                $request->get('item_id'),
                $request->get('branch_id'),
                $admin->organization_id
            );

            return response()->json([
                'stock_on_hand' => $stock,
                'max_transfer' => $stock * 1.1
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching item stock', [
                'item_id' => $request->get('item_id'),
                'branch_id' => $request->get('branch_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch stock'], 500);
        }
    }
}
