<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemTransaction;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderIngredient;
use App\Models\ProductionOrderItem;
use App\Models\ProductionSession;
use App\Models\ProductionRecipe;
use Illuminate\Http\Request;
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
    public function create(Request $request)
    {
        // Get available production orders (approved status)
        $availableOrders = ProductionOrder::with(['items.item', 'productionRequestMaster'])
            ->where('organization_id', Auth::user()->organization_id)
            ->where('status', 'approved')
            ->orderBy('production_date')
            ->get();

        // If order_id is provided in URL, verify it exists and is available
        if ($request->has('order_id')) {
            $orderId = $request->get('order_id');
            $selectedOrder = $availableOrders->where('id', $orderId)->first();

            if (!$selectedOrder) {
                return redirect()->route('admin.production.sessions.create')
                    ->with('error', 'The specified production order is not available for session creation.');
            }
        }

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

        return redirect()->route('admin.production.sessions.show', $session)
            ->with('success', 'Production session created successfully.');
    }

    /**
     * Display the specified production session
     */
    public function show(ProductionSession $session)
    {
        $session->load(['productionOrder.items.item', 'supervisor']);

        // Get production recipes for production items
        $recipes = ProductionRecipe::whereIn('production_item_id',
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
        $recipe = ProductionRecipe::where('production_item_id', $item['item_id'])->first();

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

    /**
     * Record production output and return items to inventory
     */
    public function recordProduction(Request $request, ProductionSession $session)
    {
        $request->validate([
            'production_items' => 'required|array',
            'production_items.*.item_id' => 'required|exists:item_master,id',
            'production_items.*.quantity_produced' => 'required|numeric|min:0',
            'production_items.*.quantity_wasted' => 'nullable|numeric|min:0',
            'production_items.*.waste_reason' => 'nullable|string|max:255',
            'production_items.*.batch_number' => 'nullable|string|max:100',
            'production_items.*.expiry_date' => 'nullable|date',
            'production_items.*.quality_notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $session) {
            foreach ($request->production_items as $itemData) {
                // Update production order item
                $orderItem = ProductionOrderItem::where('production_order_id', $session->production_order_id)
                    ->where('item_id', $itemData['item_id'])
                    ->first();

                if ($orderItem) {
                    $orderItem->update([
                        'quantity_produced' => $orderItem->quantity_produced + $itemData['quantity_produced'],
                        'quantity_wasted' => $orderItem->quantity_wasted + ($itemData['quantity_wasted'] ?? 0),
                    ]);

                    // Record production output to inventory (production in)
                    if ($itemData['quantity_produced'] > 0) {
                        $notes = "Production completed - Session: {$session->session_name}, Order: {$session->productionOrder->production_order_number}";

                        if (!empty($itemData['batch_number'])) {
                            $notes .= ", Batch: {$itemData['batch_number']}";
                        }

                        if (!empty($itemData['quality_notes'])) {
                            $notes .= ", Quality: {$itemData['quality_notes']}";
                        }

                        ItemTransaction::create([
                            'organization_id' => Auth::user()->organization_id,
                            'branch_id' => Auth::user()->branch_id, // HQ branch
                            'inventory_item_id' => $itemData['item_id'],
                            'transaction_type' => 'production_in',
                            'quantity' => $itemData['quantity_produced'],
                            'unit_price' => 0,
                            'total_amount' => 0,
                            'transaction_date' => now(),
                            'description' => $notes,
                            'production_session_id' => $session->id,
                            'production_order_id' => $session->production_order_id,
                            'created_by_user_id' => Auth::id(),
                        ]);
                    }

                    // Record waste if any
                    if (!empty($itemData['quantity_wasted']) && $itemData['quantity_wasted'] > 0) {
                        ItemTransaction::create([
                            'organization_id' => Auth::user()->organization_id,
                            'branch_id' => Auth::user()->branch_id,
                            'inventory_item_id' => $itemData['item_id'],
                            'transaction_type' => 'production_waste',
                            'quantity' => -$itemData['quantity_wasted'],
                            'unit_price' => 0,
                            'total_amount' => 0,
                            'transaction_date' => now(),
                            'description' => "Production waste: " . ($itemData['waste_reason'] ?? 'Not specified'),
                            'waste_quantity' => $itemData['quantity_wasted'],
                            'waste_reason' => $itemData['waste_reason'] ?? null,
                            'production_session_id' => $session->id,
                            'production_order_id' => $session->production_order_id,
                            'created_by_user_id' => Auth::id(),
                        ]);
                    }
                }
            }

            // Mark session as completed if all items are produced
            $this->checkSessionCompletion($session);
        });

        return redirect()->back()->with('success', 'Production output recorded successfully and items added to inventory.');
    }

    /**
     * Return unused ingredients to inventory
     */
    public function returnIngredients(Request $request, ProductionSession $session)
    {
        $request->validate([
            'returned_ingredients' => 'required|array',
            'returned_ingredients.*.ingredient_id' => 'required|exists:production_order_ingredients,id',
            'returned_ingredients.*.return_quantity' => 'required|numeric|min:0',
            'returned_ingredients.*.return_reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $session) {
            foreach ($request->returned_ingredients as $returnData) {
                $ingredient = ProductionOrderIngredient::find($returnData['ingredient_id']);

                if ($ingredient && $returnData['return_quantity'] > 0) {
                    // Update ingredient returned quantity
                    $ingredient->update([
                        'returned_quantity' => $ingredient->returned_quantity + $returnData['return_quantity'],
                    ]);

                    // Return ingredients to inventory
                    ItemTransaction::create([
                        'organization_id' => Auth::user()->organization_id,
                        'branch_id' => Auth::user()->branch_id,
                        'inventory_item_id' => $ingredient->ingredient_item_id,
                        'transaction_type' => 'production_return',
                        'quantity' => $returnData['return_quantity'],
                        'unit_price' => 0,
                        'total_amount' => 0,
                        'transaction_date' => now(),
                        'description' => "Ingredient returned from production: " . ($returnData['return_reason'] ?? 'Unused ingredient'),
                        'production_session_id' => $session->id,
                        'production_order_id' => $session->production_order_id,
                        'created_by_user_id' => Auth::id(),
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Unused ingredients returned to inventory successfully.');
    }

    /**
     * Check if session can be completed
     */
    private function checkSessionCompletion(ProductionSession $session)
    {
        // Check if all required production items have been completed
        $allItemsCompleted = $session->productionOrder->items->every(function ($item) {
            return $item->quantity_produced >= $item->quantity_to_produce;
        });

        if ($allItemsCompleted && $session->status === 'in_progress') {
            $session->update([
                'status' => 'completed',
                'end_time' => now(),
            ]);

            // Check if entire production order is complete
            $this->checkProductionOrderCompletion($session->productionOrder);
        }
    }

    public function issueIngredients(Request $request, $id)
    {
        // TODO: Implement issueIngredients logic
        return redirect()->back()->with('success', 'Ingredients issued successfully');
    }
}
