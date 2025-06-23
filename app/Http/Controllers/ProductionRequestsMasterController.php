<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionRequestMaster;
use App\Models\ProductionRequestItem;
use App\Models\ItemMaster;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionRequestsMasterController extends Controller
{
    /**
     * Display a listing of production requests
     */
    public function index(Request $request)
    {
        $query = ProductionRequestMaster::with(['branch', 'items.item', 'createdBy'])
            ->where('organization_id', Auth::user()->organization_id);

        // Filter by branch for branch users
        if (!Auth::user()->hasRole('admin') && Auth::user()->branch_id) {
            $query->where('branch_id', Auth::user()->branch_id);
        }

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

        $requests = $query->latest()->paginate(20);
        $branches = Branch::where('organization_id', Auth::user()->organization_id)->get();

        return view('admin.production.requests.index', compact('requests', 'branches'));
    }

    /**
     * Show the form for creating a new production request
     */
    public function create()
    {
        // Only production items can be selected
        $productionItems = ItemMaster::whereHas('category', function($query) {
            $query->where('name', 'Production Items');
        })
        ->where('organization_id', Auth::user()->organization_id)
        ->get();

        return view('admin.production.requests.create', compact('productionItems'));
    }

    /**
     * Store a newly created production request
     */
    public function store(Request $request)
    {
        $request->validate([
            'required_date' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity_requested' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($request) {
            $productionRequest = ProductionRequestMaster::create([
                'organization_id' => Auth::user()->organization_id,
                'branch_id' => Auth::user()->branch_id,
                'request_date' => now()->toDateString(),
                'required_date' => $request->required_date,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by_user_id' => Auth::id(),
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
     * Get aggregated production requirements for HQ
     */
    public function aggregate(Request $request)
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

        return view('admin.production.requests.aggregate', compact('aggregatedItems', 'requests'));
    }
}
