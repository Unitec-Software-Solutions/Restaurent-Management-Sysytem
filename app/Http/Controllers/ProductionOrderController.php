<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ProductionRequestMaster;
use App\Models\ProductionRequestItem;
use App\Models\ProductionSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionOrderController extends Controller
{
    /**
     * Display a listing of production orders
     */
    public function index(Request $request)
    {
        $query = ProductionOrder::with(['productionRequests', 'items.item', 'createdBy'])
            ->where('organization_id', Auth::user()->organization_id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('production_date_from')) {
            $query->whereDate('production_date', '>=', $request->production_date_from);
        }

        if ($request->filled('production_date_to')) {
            $query->whereDate('production_date', '<=', $request->production_date_to);
        }

        $orders = $query->latest()->paginate(20);

        // Fetch production items for filter dropdown
        $productionItems = \App\Models\ItemMaster::whereHas('category', function($query) {
            $query->where('name', 'Production Items');
        })->where('organization_id', Auth::user()->organization_id)->get();

        // Count pending requests for aggregation
        $pendingRequests = \App\Models\ProductionRequestMaster::where('organization_id', Auth::user()->organization_id)
            ->where('status', 'approved')
            ->count();

        return view('admin.production.orders.index', compact('orders', 'productionItems', 'pendingRequests'));
    }

    /**
     * Show the form for creating a new production order
     */
    public function create(Request $request)
    {
        // Get aggregated approved production requests
        $aggregatedItems = $this->getAggregatedItems($request);

        return view('admin.production.orders.create', compact('aggregatedItems'));
    }

    /**
     * Store a newly created production order
     */
    public function store(Request $request)
    {
        $request->validate([
            'production_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity_to_produce' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($request) {
            $productionOrder = ProductionOrder::create([
                'organization_id' => Auth::user()->organization_id,
                'production_order_number' => $this->generateOrderNumber(),
                'production_date' => $request->production_date,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by_user_id' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'item_id' => $item['item_id'],
                    'quantity_to_produce' => $item['quantity_to_produce'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.production.orders.index')
            ->with('success', 'Production order created successfully.');
    }

    /**
     * Store a newly created production order from aggregated requests
     */
    public function store_aggregated(Request $request)
    {
        $request->validate([
            'production_date' => 'required|date',
            'selected_requests' => 'required|array|min:1',
            'selected_requests.*' => 'exists:production_requests_master,id',
            'production_notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($request) {
            // Get the selected production requests
            $selectedRequests = ProductionRequestMaster::whereIn('id', $request->selected_requests)
                ->where('status', 'approved')
                ->with('items.item')
                ->get();

            if ($selectedRequests->isEmpty()) {
                throw new \Exception('No approved requests found.');
            }

            // Generate production order number
            $orderNumber = $this->generateOrderNumber();

            // Create production order
            $productionOrder = ProductionOrder::create([
                'organization_id' => Auth::user()->organization_id,
                'production_order_number' => $orderNumber,
                'production_date' => $request->production_date,
                'status' => ProductionOrder::STATUS_DRAFT,
                'notes' => $request->production_notes,
                'created_by_user_id' => Auth::id(),
            ]);

            // Aggregate items by item_id
            $aggregatedItems = [];
            $requestIds = [];

            foreach ($selectedRequests as $productionRequest) {
                $requestIds[] = $productionRequest->id;

                foreach ($productionRequest->items as $item) {
                    $itemId = $item->item_id;

                    if (!isset($aggregatedItems[$itemId])) {
                        $aggregatedItems[$itemId] = [
                            'item_id' => $itemId,
                            'quantity_to_produce' => 0,
                            'requests' => []
                        ];
                    }

                    $aggregatedItems[$itemId]['quantity_to_produce'] += $item->quantity_approved;
                    $aggregatedItems[$itemId]['requests'][] = $productionRequest->id;
                }
            }

            // Create production order items
            foreach ($aggregatedItems as $itemData) {
                ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_to_produce' => $itemData['quantity_to_produce'],
                    'quantity_produced' => 0,
                    'notes' => 'Aggregated from requests: ' . implode(', ', $itemData['requests'])
                ]);
            }

            // Update production requests status (without production_order_id for now)
            ProductionRequestMaster::whereIn('id', $request->selected_requests)
                ->update([
                    'status' => ProductionRequestMaster::STATUS_IN_PRODUCTION
                ]);
        });

        return redirect()->route('admin.production.orders.index')
            ->with('success', 'Production order created successfully from ' . count($request->selected_requests) . ' requests.');
    }

    /**
     * Display the specified production order
     */
    public function show(ProductionOrder $productionOrder)
    {
        $productionOrder->load(['items.item', 'createdBy', 'approvedBy']);

        return view('admin.production.orders.show', compact('productionOrder'));
    }

    /**
     * Approve production order (starts production)
     */
    public function approve(ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft orders can be approved.');
        }

        $productionOrder->update([
            'status' => 'approved',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Production order approved successfully.');
    }

    /**
     * Start production
     */
    public function startProduction(ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'approved') {
            return redirect()->back()->with('error', 'Only approved orders can be started.');
        }

        $productionOrder->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Production started successfully.');
    }

    /**
     * Complete production
     */
    public function complete(ProductionOrder $productionOrder)
    {
        if ($productionOrder->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Only in-progress orders can be completed.');
        }

        $productionOrder->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Update related production requests
        $this->updateProductionRequests($productionOrder);

        return redirect()->back()->with('success', 'Production completed successfully.');
    }

    /**
     * Cancel production order
     */
    public function cancel(ProductionOrder $productionOrder)
    {
        if (!$productionOrder->canBeCancelled()) {
            return redirect()->back()->with('error', 'This production order cannot be cancelled.');
        }

        $productionOrder->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Production order cancelled successfully.');
    }

    /**
     * Create production session
     */
    public function createSession(Request $request, ProductionOrder $productionOrder)
    {
        $request->validate([
            'session_name' => 'required|string|max:255',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:500'
        ]);

        ProductionSession::create([
            'organization_id' => Auth::user()->organization_id,
            'production_order_id' => $productionOrder->id,
            'session_name' => $request->session_name,
            'status' => 'scheduled',
            'notes' => $request->notes,
            'supervisor_user_id' => $request->supervisor_user_id,
        ]);

        return redirect()->back()->with('success', 'Production session created successfully.');
    }

    /**
     * Generate unique production order number
     */
    private function generateOrderNumber()
    {
        $prefix = 'PO-' . date('Y');
        $lastOrder = ProductionOrder::where('production_order_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->production_order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get aggregated items from approved production requests
     */
    private function getAggregatedItems(Request $request)
    {
        $query = ProductionRequestMaster::with(['items.item'])
            ->where('organization_id', Auth::user()->organization_id)
            ->where('status', 'approved');

        if ($request->filled('required_date_from')) {
            $query->whereDate('required_date', '>=', $request->required_date_from);
        }

        if ($request->filled('required_date_to')) {
            $query->whereDate('required_date', '<=', $request->required_date_to);
        }

        $requests = $query->get();

        // Aggregate quantities by item
        $aggregatedItems = [];
        foreach ($requests as $request) {
            foreach ($request->items as $item) {
                $itemId = $item->item_id;
                if (!isset($aggregatedItems[$itemId])) {
                    $aggregatedItems[$itemId] = [
                        'item' => $item->item,
                        'total_quantity' => 0,
                        'requests' => []
                    ];
                }
                $aggregatedItems[$itemId]['total_quantity'] += $item->quantity_approved;
                $aggregatedItems[$itemId]['requests'][] = $request;
            }
        }

        return $aggregatedItems;
    }

    /**
     * Update production requests after production completion
     */
    private function updateProductionRequests(ProductionOrder $productionOrder)
    {
        foreach ($productionOrder->items as $orderItem) {
            // Find related production request items
            $requestItems = ProductionRequestItem::where('item_id', $orderItem->item_id)
                ->whereHas('productionRequestMaster', function($query) {
                    $query->where('status', 'approved');
                })
                ->where('quantity_produced', '<', DB::raw('quantity_approved'))
                ->get();

            $remainingQuantity = $orderItem->quantity_produced;

            foreach ($requestItems as $requestItem) {
                if ($remainingQuantity <= 0) break;

                $neededQuantity = $requestItem->quantity_approved - $requestItem->quantity_produced;
                $allocatedQuantity = min($remainingQuantity, $neededQuantity);

                $requestItem->update([
                    'quantity_produced' => $requestItem->quantity_produced + $allocatedQuantity
                ]);

                $remainingQuantity -= $allocatedQuantity;

                // Check if this request is now complete
                if ($requestItem->productionRequestMaster->items->every(function($item) {
                    return $item->quantity_produced >= $item->quantity_approved;
                })) {
                    $requestItem->productionRequestMaster->update(['status' => 'completed']);
                }
            }
        }
    }
}
