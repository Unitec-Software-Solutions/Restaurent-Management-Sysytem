<?php

namespace App\Http\Controllers;

use App\Models\ProductionRequestMaster;
use App\Models\ProductionRequestItem;
use App\Models\ItemMaster;
use App\Models\Branch;
use App\Models\Recipe;
use App\Models\RecipeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\IngredientStockService;
use Illuminate\Support\Facades\DB;

class ProductionRequestsMasterController extends Controller
{
    /**
     * Display a listing of production requests
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        $query = ProductionRequestMaster::with(['items.item', 'branch'])
            ->where('organization_id', $admin->organization_id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('request_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('request_date', '<=', $request->date_to);
        }

        if ($request->filled('required_date_from')) {
            $query->whereDate('required_date', '>=', $request->required_date_from);
        }

        if ($request->filled('required_date_to')) {
            $query->whereDate('required_date', '<=', $request->required_date_to);
        }

        $requests = $query->latest()->paginate(20);

        // Get branches for filter
        $branches = Branch::where('organization_id', Auth::user()->organization_id)->get();

        // Get statistics for filters
        $stats = [
            'pending_approval' => ProductionRequestMaster::where('organization_id', Auth::user()->organization_id)
                ->where('status', ProductionRequestMaster::STATUS_SUBMITTED)
                ->count(),
            'approved_requests' => ProductionRequestMaster::where('organization_id', Auth::user()->organization_id)
                ->where('status', ProductionRequestMaster::STATUS_APPROVED)
                ->count(),
            'in_production' => ProductionRequestMaster::where('organization_id', Auth::user()->organization_id)
                ->where('status', ProductionRequestMaster::STATUS_IN_PRODUCTION)
                ->count(),
            'completed' => ProductionRequestMaster::where('organization_id', Auth::user()->organization_id)
                ->where('status', ProductionRequestMaster::STATUS_COMPLETED)
                ->count()
        ];

        return view('admin.production.requests.index', compact('requests', 'branches', 'stats'));
    }

    /**
     * Show the form for creating a new production request
     */
    public function create()
    {
        // Only production items can be selected
        $user = Auth::user();
        $productionItems = ItemMaster::whereHas('category', function($query) {
                $query->where('name', 'Production Items');
            })
            ->when($user->organization_id, function($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            })
            ->get();

        return view('admin.production.requests.create', compact('productionItems'));
    }

    /**
     * Store a newly created production request
     */
    public function store(Request $request)
    {
        $admin = auth('admin')->user() ?? Auth::user();
        $organizationId = $admin->organization_id ?? $request->organization_id;
        $branchId = $admin->branch_id ?? $request->branch_id;

        if (!$organizationId || !$branchId) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Organization or Branch not set for this user.']);
        }

        $request->validate([
            'required_date' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity_requested' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($request, $organizationId, $branchId, $admin) {
            $productionRequest = ProductionRequestMaster::create([
                'organization_id' => $organizationId,
                'branch_id' => $branchId,
                'request_date' => now()->toDateString(),
                'required_date' => $request->required_date,
                'status' => 'submitted', // set this to  'draft' to allow edits before submission
                'notes' => $request->notes,
                'created_by_user_id' => $admin->id,
            ]);

            foreach ($request->items as $item) {
                ProductionRequestItem::create([
                    'production_request_master_id' => $productionRequest->id,
                    'item_id' => $item['item_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.production.requests.index')
            ->with('success', 'Production request created successfully.');
    }

    /**
     * Display the specified production request
     */
    public function show(ProductionRequestMaster $productionRequest)
    {
        $productionRequest->load(['branch', 'items.item', 'createdBy', 'approvedBy']);

        return view('admin.production.requests.show', compact('productionRequest'));
    }

    /**
     * Submit production request for approval
     */
    public function submit(ProductionRequestMaster $productionRequest)
    {
        if ($productionRequest->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft requests can be submitted.');
        }

        $productionRequest->update(['status' => 'submitted']);

        return redirect()->back()->with('success', 'Production request submitted for approval.');
    }

    /**
     * Approve production request (HQ only)
     */
    public function approve(ProductionRequestMaster $productionRequest)
    {
        if ($productionRequest->status !== 'submitted') {
            return redirect()->back()->with('error', 'Only submitted requests can be approved.');
        }

        $productionRequest->update([
            'status' => 'approved',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Auto-approve all requested quantities
        $productionRequest->items()->update([
            'quantity_approved' => DB::raw('quantity_requested')
        ]);

        return redirect()->back()->with('success', 'Production request approved successfully.');
    }

    /**
     * Cancel production request
     */
    public function cancel(ProductionRequestMaster $productionRequest)
    {
        if (in_array($productionRequest->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'Cannot cancel completed or already cancelled requests.');
        }

        $productionRequest->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Production request cancelled.');
    }

    /**
     * Show the aggregate view for creating production orders
     */
    public function aggregate(Request $request)
    {
        // Get all approved production requests
        $query = ProductionRequestMaster::with(['items.item', 'branch'])
            ->where('organization_id', Auth::user()->organization_id)
            ->where('status', 'approved');

        // Apply filters if provided
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('required_date_from')) {
            $query->whereDate('required_date', '>=', $request->required_date_from);
        }

        if ($request->filled('required_date_to')) {
            $query->whereDate('required_date', '<=', $request->required_date_to);
        }

        $requests = $query->orderBy('required_date')->get();

        // Get aggregated data for preview
        $aggregatedItems = $this->getAggregatedData($requests);

        // Get all branches for filtering
        $branches = Branch::where('organization_id', Auth::user()->organization_id)
            ->where('is_active', true)
            ->get();

        // Get all available ingredients for manual addition
        $availableIngredients = ItemMaster::whereHas('category', function($query) {
            $query->whereIn('name', ['Raw Materials', 'Ingredients']);
        })
        ->where('organization_id', Auth::user()->organization_id)
        ->where('is_active', true)
        ->get();

        return view('admin.production.orders.aggregate', compact('requests', 'branches', 'aggregatedItems', 'availableIngredients'));
    }

    /**
     * Get aggregated data with ingredient calculations
     */
    private function getAggregatedData($requests)
    {
        $aggregated = [];
        $totalIngredients = [];

        // Get HQ branch for stock calculations
        $hqBranch = \App\Models\Branch::where('organization_id', Auth::user()->organization_id)
            ->where('is_head_office', true)
            ->first();

        foreach ($requests as $request) {
            foreach ($request->items as $item) {
                $itemId = $item->item_id;

                if (!isset($aggregated[$itemId])) {
                    $aggregated[$itemId] = [
                        'item' => $item->item,
                        'total_quantity' => 0,
                        'requests' => [],
                        'ingredients' => []
                    ];
                }

                $aggregated[$itemId]['total_quantity'] += $item->quantity_approved;
                $aggregated[$itemId]['requests'][] = [
                    'request_id' => $request->id,
                    'branch' => $request->branch->name,
                    'quantity' => $item->quantity_approved
                ];

                // Calculate ingredients from recipe
                $recipe = \App\Models\Recipe::where('production_item_id', $itemId)
                    ->where('is_active', true)
                    ->with('details.rawMaterialItem')
                    ->first();

                if ($recipe) {
                    $multiplier = $item->quantity_approved / $recipe->yield_quantity;

                    foreach ($recipe->details as $detail) {
                        $ingredientId = $detail->raw_material_item_id;
                        $requiredQuantity = $detail->quantity_required * $multiplier;

                        if (!isset($totalIngredients[$ingredientId])) {
                            // Get available stock from HQ branch using IngredientStockService
                            $availableStock = IngredientStockService::getHQStock($ingredientId);

                            $totalIngredients[$ingredientId] = [
                                'item' => $detail->rawMaterialItem,
                                'total_required' => 0,
                                'available_stock' => $availableStock,
                                'unit' => $detail->unit_of_measurement ?: $detail->rawMaterialItem->unit_of_measurement,
                                'from_items' => []
                            ];
                        }

                        $totalIngredients[$ingredientId]['total_required'] += $requiredQuantity;
                        $totalIngredients[$ingredientId]['from_items'][] = [
                            'production_item' => $item->item->name,
                            'quantity_needed' => $requiredQuantity,
                            'notes' => $detail->preparation_notes
                        ];

                        // Add to item's ingredient list
                        $aggregated[$itemId]['ingredients'][$ingredientId] = [
                            'item' => $detail->rawMaterialItem,
                            'quantity_per_unit' => $detail->quantity_required,
                            'total_quantity' => $requiredQuantity,
                            'unit' => $detail->unit_of_measurement ?: $detail->rawMaterialItem->unit_of_measurement,
                            'notes' => $detail->preparation_notes
                        ];
                    }
                }
            }
        }

        return [
            'items' => $aggregated,
            'ingredients' => $totalIngredients
        ];
    }

    /**
     * Calculate ingredient requirements for selected requests (AJAX endpoint)
     */
    public function calculateIngredients(Request $request)
    {
        try {
            $requestIds = explode(',', $request->request_ids);

            $requests = ProductionRequestMaster::with(['items.item', 'branch'])
                ->where('organization_id', Auth::user()->organization_id)
                ->where('status', 'approved')
                ->whereIn('id', $requestIds)
                ->get();

            if ($requests->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid requests found'
                ]);
            }

            $aggregatedData = $this->getAggregatedData($requests);

            return response()->json([
                'success' => true,
                'aggregatedItems' => $aggregatedData['items'],
                'ingredients' => $aggregatedData['ingredients']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating ingredients: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show all production requests for management (HQ only)
     */
    public function manage(Request $request)
    {
        $admin = auth('admin')->user();

        // For debugging - temporarily allow all authenticated admins
        // TODO: Restore proper access control once tested
        // if (!$admin->is_super_admin && $admin->branch_id !== null) {
        //     abort(403, 'Access denied. HQ access required.');
        // }

        $baseQuery = ProductionRequestMaster::with(['items.item', 'branch', 'createdBy', 'approvedBy']);

        // Filter by organization for non-super admins
        if (!$admin->is_super_admin) {
            $baseQuery->where('organization_id', $admin->organization_id);
        }

        // Get pending approval requests (submitted status)
        $pendingApprovalRequests = (clone $baseQuery)
            ->where('status', ProductionRequestMaster::STATUS_SUBMITTED)
            ->orderBy('required_date')
            ->get();

        // Get approved requests
        $approvedRequests = (clone $baseQuery)
            ->where('status', ProductionRequestMaster::STATUS_APPROVED)
            ->orderBy('approved_at', 'desc')
            ->get();

        // Get all requests for general listing
        $query = (clone $baseQuery);

        // Apply filters for all requests tab
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('request_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('request_date', '<=', $request->date_to);
        }

        if ($request->filled('required_date_from')) {
            $query->whereDate('required_date', '>=', $request->required_date_from);
        }

        if ($request->filled('required_date_to')) {
            $query->whereDate('required_date', '<=', $request->required_date_to);
        }

        $requests = $query->latest()->paginate(20);

        // Get branches for filter
        $branches = \App\Models\Branch::where('organization_id', $admin->organization_id)->get();

        return view('admin.production.requests.manage', compact(
            'pendingApprovalRequests',
            'approvedRequests',
            'requests',
            'branches'
        ));
    }

    /**
     * Show approval form for a production request
     */
    public function showApprovalForm(ProductionRequestMaster $productionRequest)
    {
        $admin = auth('admin')->user();

        // Allow super admin and organization admin (users without branch_id = HQ users)
        if (!$admin->is_super_admin && $admin->branch_id !== null) {
            abort(403, 'Access denied. HQ access required.');
        }

        if (!$productionRequest->canBeApproved()) {
            return redirect()->back()->with('error', 'This request cannot be approved.');
        }

        $productionRequest->load(['items.item', 'branch', 'createdBy']);

        return view('admin.production.requests.approval-form', compact('productionRequest'));
    }

    /**
     * Process approval of production request
     */
    public function processApproval(Request $request, ProductionRequestMaster $productionRequest)
    {
        $admin = auth('admin')->user();

        // Allow super admin and organization admin (users without branch_id = HQ users)
        if (!$admin->is_super_admin && $admin->branch_id !== null) {
            abort(403, 'Access denied. HQ access required.');
        }

        if (!$productionRequest->canBeApproved()) {
            return redirect()->back()->with('error', 'This request cannot be approved.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.quantity_approved' => 'required|numeric|min:0',
            'approval_notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($request, $productionRequest, $admin) {
            // Update each item with approved quantities
            foreach ($request->items as $itemId => $itemData) {
                $productionRequestItem = $productionRequest->items()->where('item_id', $itemId)->first();
                if ($productionRequestItem) {
                    $productionRequestItem->update([
                        'quantity_approved' => $itemData['quantity_approved'],
                        'notes' => $itemData['notes'] ?? null
                    ]);
                }
            }

            // Update master request
            $productionRequest->update([
                'status' => ProductionRequestMaster::STATUS_APPROVED,
                'approved_by_user_id' => $admin->id,
                'approved_at' => now(),
                'notes' => $request->approval_notes
            ]);
        });

        return redirect()->route('admin.production.requests.manage')
            ->with('success', 'Production request approved successfully.');
    }
}
