<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionSession;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ItemTransaction;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionSessionController extends Controller
{
    /**
     * Display a listing of production sessions
     */
    public function index(Request $request)
    {
        $query = ProductionSession::with(['productionOrder', 'supervisor'])
            ->where('organization_id', Auth::user()->organization_id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('start_time', '<=', $request->date_to);
        }

        $sessions = $query->latest()->paginate(20);

        return view('admin.production.sessions.index', compact('sessions'));
    }

    /**
     * Show the form for creating a new production session
     */
    public function create()
    {
        $availableOrders = ProductionOrder::where('organization_id', Auth::user()->organization_id)
            ->whereIn('status', ['approved', 'in_progress'])
            ->with('items.item')
            ->get();

        return view('admin.production.sessions.create', compact('availableOrders'));
    }

    /**
     * Store a newly created production session
     */
    public function store(Request $request)
    {
        $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'session_name' => 'required|string|max:255',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:500'
        ]);

        $session = ProductionSession::create([
            'organization_id' => Auth::user()->organization_id,
            'production_order_id' => $request->production_order_id,
            'session_name' => $request->session_name,
            'status' => 'scheduled',
            'notes' => $request->notes,
            'supervisor_user_id' => $request->supervisor_user_id,
        ]);

        return redirect()->route('production.sessions.show', $session)
            ->with('success', 'Production session created successfully.');
    }

    /**
     * Display the specified production session
     */
    public function show(ProductionSession $session)
    {
        $session->load(['productionOrder.items.item', 'supervisor']);

        // Get recipes for production items
        $recipes = Recipe::whereIn('production_item_id',
            $session->productionOrder->items->pluck('item_id')
        )->with('details.rawMaterialItem')->get();
        return view('admin.production.sessions.show', compact('session', 'recipes'));
    }

    /**
     * Start production session
     */
    public function start(ProductionSession $session)
    {
        if ($session->status !== 'scheduled') {
            return redirect()->back()->with('error', 'Only scheduled sessions can be started.');
        }

        $session->update([
            'status' => 'in_progress',
            'start_time' => now(),
        ]);

        // Update production order status if needed
        if ($session->productionOrder->status === 'approved') {
            $session->productionOrder->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Production session started successfully.');
    }

    /**
     * Complete production session
     */
    public function complete(Request $request, ProductionSession $session)
    {
        if ($session->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Only in-progress sessions can be completed.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity_produced' => 'required|numeric|min:0',
            'items.*.quantity_wasted' => 'nullable|numeric|min:0',
            'items.*.waste_reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $session) {
            // Update production quantities
            foreach ($request->items as $item) {
                $orderItem = ProductionOrderItem::where('production_order_id', $session->production_order_id)
                    ->where('item_id', $item['item_id'])
                    ->first();

                if ($orderItem) {
                    $orderItem->update([
                        'quantity_produced' => $orderItem->quantity_produced + $item['quantity_produced'],
                        'quantity_wasted' => $orderItem->quantity_wasted + ($item['quantity_wasted'] ?? 0),
                    ]);

                    // Record inventory transactions
                    $this->recordInventoryTransactions($session, $item);

                    // Consume raw materials based on recipes
                    $this->consumeRawMaterials($session, $item);
                }
            }

            // Complete session
            $session->update([
                'status' => 'completed',
                'end_time' => now(),
            ]);

            // Check if production order is complete
            $this->checkProductionOrderCompletion($session->productionOrder);
        });

        return redirect()->back()->with('success', 'Production session completed successfully.');
    }

    /**
     * Cancel production session
     */
    public function cancel(ProductionSession $session)
    {
        if (in_array($session->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'Cannot cancel completed or already cancelled sessions.');
        }

        $session->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Production session cancelled.');
    }

    /**
     * Record inventory transactions for produced items
     */
    private function recordInventoryTransactions(ProductionSession $session, array $item)
    {
        // Record production (credit/increase)
        if ($item['quantity_produced'] > 0) {
            ItemTransaction::create([
                'organization_id' => Auth::user()->organization_id,
                'branch_id' => Auth::user()->branch_id,
                'inventory_item_id' => $item['item_id'],
                'transaction_type' => 'production',
                'quantity' => $item['quantity_produced'],
                'unit_price' => 0, // Production cost calculation would go here
                'total_amount' => 0,
                'transaction_date' => now(),
                'description' => 'Production from session: ' . $session->session_name,
                'production_session_id' => $session->id,
                'production_order_id' => $session->production_order_id,
                'created_by_user_id' => Auth::id(),
            ]);
        }

        // Record waste (debit/decrease)
        if (($item['quantity_wasted'] ?? 0) > 0) {
            ItemTransaction::create([
                'organization_id' => Auth::user()->organization_id,
                'branch_id' => Auth::user()->branch_id,
                'inventory_item_id' => $item['item_id'],
                'transaction_type' => 'waste',
                'quantity' => -$item['quantity_wasted'],
                'unit_price' => 0,
                'total_amount' => 0,
                'transaction_date' => now(),
                'description' => 'Production waste: ' . ($item['waste_reason'] ?? 'Not specified'),
                'waste_quantity' => $item['quantity_wasted'],
                'waste_reason' => $item['waste_reason'] ?? null,
                'production_session_id' => $session->id,
                'production_order_id' => $session->production_order_id,
                'created_by_user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Consume raw materials based on recipes
     */
    private function consumeRawMaterials(ProductionSession $session, array $item)
    {
        $recipe = Recipe::where('production_item_id', $item['item_id'])->first();

        if (!$recipe) {
            return; // No recipe found, skip raw material consumption
        }

        $productionMultiplier = $item['quantity_produced'] / $recipe->yield_quantity;

        foreach ($recipe->details as $detail) {
            $requiredQuantity = $detail->quantity_required * $productionMultiplier;

            ItemTransaction::create([
                'organization_id' => Auth::user()->organization_id,
                'branch_id' => Auth::user()->branch_id,
                'inventory_item_id' => $detail->raw_material_item_id,
                'transaction_type' => 'consumption',
                'quantity' => -$requiredQuantity,
                'unit_price' => 0,
                'total_amount' => 0,
                'transaction_date' => now(),
                'description' => 'Raw material consumed for production: ' . $recipe->recipe_name,
                'production_session_id' => $session->id,
                'production_order_id' => $session->production_order_id,
                'created_by_user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Check if production order is complete
     */
    private function checkProductionOrderCompletion(ProductionOrder $productionOrder)
    {
        $allItemsProduced = $productionOrder->items->every(function ($item) {
            return $item->quantity_produced >= $item->quantity_to_produce;
        });

        if ($allItemsProduced && $productionOrder->status === 'in_progress') {
            $productionOrder->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }
}
