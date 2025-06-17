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
        $query = GoodsTransferNote::with(['fromBranch', 'toBranch', 'createdBy', 'items'])
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
        // Add origin_status filter
        if ($originStatus = request('origin_status')) {
            $query->where('origin_status', $originStatus);
        }
        // Add receiver_status filter
        if ($receiverStatus = request('receiver_status')) {
            $query->where('receiver_status', $receiverStatus);
        }
        // Add search filter for GTN number or GTN name (case-insensitive)
        if ($search = request('search')) {
            $search = strtolower($search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(gtn_number) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(gtn_number) LIKE ?', ['%' . $search . '%']);
                // If you have a GTN name field, add it here:
                // $q->orWhereRaw('LOWER(gtn_name) LIKE ?', ['%' . $search . '%']);
            });
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
            ->where('gtn_id', $id)
            ->firstOrFail();

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
            return redirect()->route('admin.inventory.gtn.show', $id)->with('success', 'GTN Updated Successfully');
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
            $gtn = GoodsTransferNote::where('organization_id', $admin->organization_id)
                                    ->findOrFail($id);

            DB::transaction(function () use ($request, $gtn, $admin) {
                $status = $request->input('status');
                $notes = $request->input('notes');

                // Use the new workflow methods
                switch ($status) {
                    case 'Confirmed':
                        if ($gtn->isDraft()) {
                            $this->gtnService->confirmGTN($gtn->gtn_id, $admin->id);
                        } else {
                            throw new Exception('GTN can only be confirmed from draft status');
                        }
                        break;

                    case 'Approved':
                        // For backward compatibility, treat as confirmed if draft
                        if ($gtn->isDraft()) {
                            $this->gtnService->confirmGTN($gtn->gtn_id, $admin->id);
                        }
                        break;

                    case 'Verified':
                        // For backward compatibility, treat as confirmed if draft
                        if ($gtn->isDraft()) {
                            $this->gtnService->confirmGTN($gtn->gtn_id, $admin->id);
                        }
                        break;
                }

                // Update legacy status field for backward compatibility
                $gtn->update([
                    'status' => $status,
                    'notes' => $notes ? ($gtn->notes . "\n" . $notes) : $gtn->notes,
                ]);

                Log::info('GTN status changed', [
                    'gtn_id' => $gtn->gtn_id,
                    'old_status' => $gtn->status,
                    'new_status' => $status,
                    'user_id' => $admin->id
                ]);
            });

            return redirect()->route('admin.inventory.gtn.show', $id)
                ->with('success', 'GTN status updated successfully and stock transfer processed.');
        } catch (Exception $e) {
            Log::error('GTN status change failed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to update GTN status: ' . $e->getMessage());
        }
    }

    /**
     * Confirm GTN and deduct stock from sender (new workflow method)
     */
    public function confirm($id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $gtn = $this->gtnService->confirmGTN($id, $admin->id);

            return response()->json([
                'success' => true,
                'message' => 'GTN confirmed successfully. Stock deducted from sender branch.',
                'gtn' => $gtn
            ]);
        } catch (Exception $e) {
            Log::error('GTN confirmation failed', [
                'gtn_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mark GTN as received
     */
    public function receive(Request $request, $id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $gtn = $this->gtnService->receiveGTN($id, $admin->id, $request->input('notes'));

            return response()->json([
                'success' => true,
                'message' => 'GTN marked as received successfully.',
                'gtn' => $gtn
            ]);
        } catch (Exception $e) {
            Log::error('GTN receive failed', [
                'gtn_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verify GTN items
     */
    public function verify(Request $request, $id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $gtn = $this->gtnService->verifyGTN($id, $admin->id, $request->input('notes'));

            return response()->json([
                'success' => true,
                'message' => 'GTN verified successfully.',
                'gtn' => $gtn
            ]);
        } catch (Exception $e) {
            Log::error('GTN verify failed', [
                'gtn_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Accept/Reject GTN items
     */
    public function processAcceptance(Request $request, $id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'acceptance_data' => 'required|array',
            'acceptance_data.*.quantity_accepted' => 'required|numeric|min:0',
            'acceptance_data.*.rejection_reason' => 'nullable|string|max:500',
        ]);

        try {
            $gtn = $this->gtnService->processGTNAcceptance(
                $id,
                $request->input('acceptance_data'),
                $admin->id
            );

            return response()->json([
                'success' => true,
                'message' => 'GTN acceptance processed successfully. Stock updated accordingly.',
                'gtn' => $gtn
            ]);
        } catch (Exception $e) {
            Log::error('GTN acceptance failed', [
                'gtn_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reject entire GTN
     */
    public function reject(Request $request, $id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        try {
            $gtn = $this->gtnService->rejectGTN(
                $id,
                $request->input('rejection_reason'),
                $admin->id
            );

            return response()->json([
                'success' => true,
                'message' => 'GTN rejected successfully. Stock returned to sender branch.',
                'gtn' => $gtn
            ]);
        } catch (Exception $e) {
            Log::error('GTN rejection failed', [
                'gtn_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get GTN audit trail
     */
    public function auditTrail($id)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $auditData = $this->gtnService->getGTNAuditTrail($id);

            return response()->json([
                'success' => true,
                'data' => $auditData
            ]);
        } catch (Exception $e) {
            Log::error('GTN audit trail failed', [
                'gtn_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
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
