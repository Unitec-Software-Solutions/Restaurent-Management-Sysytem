<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\GoodsTransferNote;
use App\Models\GoodsTransferItem;
use App\Models\Branch;
use App\Models\ItemMaster;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\ItemTransaction;
use App\Models\GrnMaster;
use App\Services\GTNService;
use App\Http\Requests\GTNStoreRequest;
use App\Http\Requests\GTNUpdateRequest;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class GoodsTransferNoteController extends Controller
{
    use Exportable;

    protected $gtnService;

    public function __construct(GTNService $gtnService)
    {
        $this->gtnService = $gtnService;
    }

    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the GTN dashboard.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        // Super admins can see all GTNs, others see their organization's
        $orgId = $isSuperAdmin ? null : $user->organization_id;

        // Set default date range: 30 days back to today
        $startDate = request('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));

        // Build query with proper super admin handling
        $query = GoodsTransferNote::with(['fromBranch', 'toBranch', 'createdBy', 'items']);

        // Apply organization filter for non-super admins
        if ($orgId !== null) {
            $query->where('organization_id', $orgId);
        }

        // Apply filters using the trait
        $query = $this->applyFiltersToQuery($query, $request);

        // Handle export
        if ($request->has('export')) {
            return $this->exportToExcel($request, $query, 'gtn_export.xlsx', [
                'GTN Number', 'From Branch', 'To Branch', 'Status', 'Origin Status', 'Receiver Status', 'Transfer Date', 'Created At'
            ]);
        }

        // Always apply date range filter
        $query->whereBetween('transfer_date', [$startDate, $endDate]);

        $gtns = $query->latest()->paginate(15);

        // Get organization and related data with proper super admin handling
        $organization = $isSuperAdmin ? null : Organization::find($orgId);

        $branches = $isSuperAdmin ?
            Branch::active()->get() :
            Branch::where('organization_id', $orgId)->active()->get();

        $items = $isSuperAdmin ?
            ItemMaster::active()->get() :
            ItemMaster::where('organization_id', $orgId)->active()->get();

        return view('admin.inventory.gtn.index', compact('gtns', 'organization', 'branches', 'items', 'startDate', 'endDate'));
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access GTN creation.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        // Get organizations for super admin dropdown
        $organizations = $isSuperAdmin ? Organization::active()->get() : collect();

        // For super admin, require organization_id parameter for GTN creation
        if ($isSuperAdmin && !request('organization_id')) {
            // For super admin, show organization selection or require organization_id
            return view('admin.inventory.gtn.select-organization', compact('organizations'));
        }

        $targetOrgId = $isSuperAdmin ? request('organization_id') : $user->organization_id;

        $lastGtn = GoodsTransferNote::where('organization_id', $targetOrgId)->orderByDesc('gtn_id')->first();
        $datePrefix = now()->format('Ymd');
        $nextSeq = 1;
        if ($lastGtn && str_starts_with($lastGtn->gtn_number, 'GTN-' . $datePrefix)) {
            $lastSeq = (int)substr($lastGtn->gtn_number, -3);
            $nextSeq = $lastSeq + 1;
        }
        $nextGtnNumber = 'GTN-' . $datePrefix . '-' . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);

        // Get data with proper super admin handling
        $branches = $isSuperAdmin ?
            Branch::where('organization_id', $targetOrgId)->active()->get() :
            Branch::where('organization_id', $targetOrgId)->active()->get();

        $items = $isSuperAdmin ?
            ItemMaster::where('organization_id', $targetOrgId)->active()->get() :
            ItemMaster::where('organization_id', $targetOrgId)->active()->get();

        $employees = $isSuperAdmin ?
            Employee::where('organization_id', $targetOrgId)->get() :
            Employee::where('organization_id', $targetOrgId)->get();

        $organization = $isSuperAdmin ?
            Organization::find($targetOrgId) :
            Organization::find($targetOrgId);

        return view('admin.inventory.gtn.create', [
            'branches' => $branches,
            'items' => $items,
            'employees' => $employees,
            'organization' => $organization,
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
                'input' => $request->validated()
            ]);

            return back()->withErrors('Failed to create GTN. Check logs for details.')->withInput();
        }
    }

    public function show($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access GTN details.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        // Build query with proper super admin handling
        $query = GoodsTransferNote::with(['fromBranch', 'toBranch', 'items']);

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin) {
            $query->where('organization_id', $user->organization_id);
        }

        $gtn = $query->findOrFail($id);

        return view('admin.inventory.gtn.show', compact('gtn'));
    }

    public function edit($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access GTN editing.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        // Build query with proper super admin handling
        $query = GoodsTransferNote::with('items');

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin) {
            $query->where('organization_id', $user->organization_id);
        }

        $gtn = $query->where('gtn_id', $id)->firstOrFail();

        // Get organization ID for data fetching
        $orgId = $gtn->organization_id;

        // Get data for the form
        $branches = Branch::where('organization_id', $orgId)->active()->get();
        $items = ItemMaster::where('organization_id', $orgId)->active()->get();
        $employees = Employee::where('organization_id', $orgId)->get();
        $organization = Organization::find($orgId);

        return view('admin.inventory.gtn.edit', [
            'gtn' => $gtn,
            'branches' => $branches,
            'items' => $items,
            'employees' => $employees,
            'organization' => $organization
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
                'input' => $request->validated()
            ]);

            return back()->withErrors('Failed to update GTN: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified GTN from storage
     */
    public function destroy($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to delete GTN.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        try {
            DB::transaction(function () use ($id, $user, $isSuperAdmin) {
                $query = GoodsTransferNote::query();

                // Apply organization filter for non-super admins
                if (!$isSuperAdmin) {
                    $query->where('organization_id', $user->organization_id);
                }

                $gtn = $query->findOrFail($id);

                // Only allow deletion of pending GTNs
                if ($gtn->status !== 'Pending') {
                    throw new Exception('Only pending GTNs can be deleted.');
                }

                // Delete related items first
                $gtn->items()->delete();

                // Delete the GTN
                $gtn->delete();

                Log::info('GTN deleted', ['gtn_id' => $gtn->gtn_id, 'user_id' => $user->id]);
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to change GTN status.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        $request->validate([
            'status' => 'required|in:Confirmed,Approved,Verified',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $query = GoodsTransferNote::query();

            // Apply organization filter for non-super admins
            if (!$isSuperAdmin) {
                $query->where('organization_id', $user->organization_id);
            }

            $gtn = $query->findOrFail($id);

            DB::transaction(function () use ($request, $gtn, $user) {
                $status = $request->input('status');
                $notes = $request->input('notes');

                // Use the new workflow methods
                switch ($status) {
                    case 'Confirmed':
                        if ($gtn->isDraft()) {
                            $this->gtnService->confirmGTN($gtn->gtn_id, $user->id);
                        } else {
                            throw new Exception('GTN can only be confirmed from draft status');
                        }
                        break;

                    case 'Approved':
                        // For backward compatibility, treat as confirmed if draft
                        if ($gtn->isDraft()) {
                            $this->gtnService->confirmGTN($gtn->gtn_id, $user->id);
                        }
                        break;

                    case 'Verified':
                        // For backward compatibility, treat as confirmed if draft
                        if ($gtn->isDraft()) {
                            $this->gtnService->confirmGTN($gtn->gtn_id, $user->id);
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
                    'user_id' => $user->id
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Please log in to confirm GTN.'], 401);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return response()->json(['error' => 'Account setup incomplete.'], 403);
        }

        try {
            $gtn = $this->gtnService->confirmGTN($id, $user->id);

            return response()->json([
                'success' => true,
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Please log in to receive GTN.'], 401);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return response()->json(['error' => 'Account setup incomplete.'], 403);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $gtn = $this->gtnService->receiveGTN($id, $user->id, $request->input('notes'));

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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Please log in to verify GTN.'], 401);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return response()->json(['error' => 'Account setup incomplete.'], 403);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $gtn = $this->gtnService->verifyGTN($id, $user->id, $request->input('notes'));

            return response()->json([
                'success' => true,
                'message' => '', // Prevent undefined message
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Please log in to process GTN acceptance.'], 401);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return response()->json(['error' => 'Account setup incomplete.'], 403);
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
                $user->id
            );

            return response()->json([
                'success' => true,
                'message' => '', // Prevent undefined message
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Please log in to reject GTN.'], 401);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return response()->json(['error' => 'Account setup incomplete.'], 403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        try {
            $gtn = $this->gtnService->rejectGTN(
                $id,
                $request->input('rejection_reason'),
                $user->id
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Please log in to view audit trail.'], 401);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return response()->json(['error' => 'Account setup incomplete.'], 403);
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Please log in to access items.'], 401);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return response()->json(['error' => 'Account setup incomplete.'], 403);
        }

        $branchId = $request->get('branch_id');

        if (!$branchId) {
            return response()->json(['error' => 'Branch ID is required'], 400);
        }

        // Verify branch belongs to user's organization (for non-super admins)
        $branchQuery = Branch::where('id', $branchId);
        if (!$isSuperAdmin) {
            $branchQuery->where('organization_id', $user->organization_id);
        }

        $branch = $branchQuery->first();

        if (!$branch) {
            return response()->json(['error' => 'Invalid branch'], 400);
        }

        try {
            $orgId = $isSuperAdmin ? $branch->organization_id : $user->organization_id;
            $items = $this->gtnService->getItemsWithStock($branchId, $orgId);
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Please log in to search items.'], 401);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return response()->json(['error' => 'Account setup incomplete.'], 403);
        }

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'search' => 'nullable|string|max:100'
        ]);

        $branchId = $request->get('branch_id');
        $search = $request->get('search', '');

        // Verify branch belongs to user's organization (for non-super admins)
        $branchQuery = Branch::where('id', $branchId);
        if (!$isSuperAdmin) {
            $branchQuery->where('organization_id', $user->organization_id);
        }

        $branch = $branchQuery->first();

        if (!$branch) {
            return response()->json(['error' => 'Invalid branch'], 400);
        }

        try {
            $orgId = $isSuperAdmin ? $branch->organization_id : $user->organization_id;
            $items = $this->gtnService->searchItemsWithStock($branchId, $orgId, $search);
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Please log in to check item stock.'], 401);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return response()->json(['error' => 'Account setup incomplete.'], 403);
        }

        $request->validate([
            'item_id' => 'required|exists:item_masters,id',
            'branch_id' => 'required|exists:branches,id'
        ]);

        try {
            // For super admin, determine organization from the branch
            $orgId = $isSuperAdmin ?
                Branch::find($request->get('branch_id'))->organization_id :
                $user->organization_id;

            $stock = $this->gtnService->getItemStock(
                $request->get('item_id'),
                $request->get('branch_id'),
                $orgId
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

    /**
     * Print GTN as invoice-style document
     */
    public function print($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to print GTN.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        $query = GoodsTransferNote::with([
            'fromBranch',
            'toBranch',
            'items',
            'createdBy',
            'receivedBy',
            'verifiedBy',
            'organization'
        ]);

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin) {
            $query->where('organization_id', $user->organization_id);
        }

        $gtn = $query->findOrFail($id);

        return view('admin.inventory.gtn.print', compact('gtn'));
    }

    /**
     * Get searchable columns for GTN
     */
    protected function getSearchableColumns(): array
    {
        return ['gtn_number'];
    }
}
