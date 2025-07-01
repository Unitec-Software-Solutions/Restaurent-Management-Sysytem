<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ProductionRequestMaster;
use App\Models\ProductionRequestItem;
use App\Models\ProductionSession;
use App\Models\ProductionOrderIngredient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProductionRecipe;
use App\Models\ItemMaster;

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
     * Store a newly created production order from aggregated requests (handles higher approved quantities)
     */
    public function store_aggregated(Request $request)
    {
        Log::info('Production Order Creation Request:', $request->all());

        $request->validate([
            'production_date' => 'required|date',
            'selected_requests' => 'required|array|min:1',
            'selected_requests.*' => 'exists:production_requests_master,id',
            'production_notes' => 'nullable|string|max:1000',
            'recipe_ingredients' => 'nullable|array',
            'recipe_ingredients.*.ingredient_id' => 'required_with:recipe_ingredients.*|exists:item_master,id',
            'recipe_ingredients.*.quantity' => 'required_with:recipe_ingredients.*|numeric|min:0.001',
            'recipe_ingredients.*.notes' => 'nullable|string|max:500',
            'recipe_ingredients.*.is_edited' => 'nullable|boolean',
            'manual_ingredients' => 'nullable|array',
            'manual_ingredients.*.ingredient_id' => 'required_with:manual_ingredients.*|exists:item_master,id',
            'manual_ingredients.*.quantity' => 'required_with:manual_ingredients.*|numeric|min:0.001',
            'manual_ingredients.*.notes' => 'nullable|string|max:500'
        ]);

        try {
            $productionOrder = DB::transaction(function () use ($request) {
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

                // Aggregate items by item_id (approved quantities can exceed requested)
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

                        // Use approved quantity (which can be higher than requested)
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

                // Calculate and create ingredient requirements
                $this->createIngredientRequirements($productionOrder, $aggregatedItems, $request->recipe_ingredients ?? [], $request->manual_ingredients ?? []);

                // Update production requests status and link to production order
                ProductionRequestMaster::whereIn('id', $request->selected_requests)
                    ->update([
                        'status' => ProductionRequestMaster::STATUS_IN_PRODUCTION,
                        'production_order_id' => $productionOrder->id
                    ]);

                return $productionOrder;
            });

            return redirect()->route('admin.production.orders.show', $productionOrder)
                ->with('success', 'Production order created successfully from ' . count($request->selected_requests) . ' requests with ingredient requirements.');
        } catch (\Exception $e) {
            Log::error('Production Order Creation Failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating production order: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Create ingredient requirements for production order
     */
    private function createIngredientRequirements($productionOrder, $aggregatedItems, $recipeIngredients = [], $manualIngredients = [])
    {
        $ingredientRequirements = [];

        // First, process recipe ingredients (which may have been edited)
        if (!empty($recipeIngredients)) {
            foreach ($recipeIngredients as $recipeIngredient) {
                $ingredientId = $recipeIngredient['ingredient_id'];
                $quantity = floatval($recipeIngredient['quantity']);
                $notes = $recipeIngredient['notes'] ?? '';
                $isEdited = isset($recipeIngredient['is_edited']) && $recipeIngredient['is_edited'];

                $ingredient = \App\Models\ItemMaster::find($ingredientId);

                $ingredientRequirements[$ingredientId] = [
                    'ingredient_item_id' => $ingredientId,
                    'planned_quantity' => $quantity,
                    'unit_of_measurement' => $ingredient->unit_of_measurement,
                    'notes' => $isEdited ? 'Manually adjusted from recipe' . ($notes ? ': ' . $notes : '') : 'From recipe' . ($notes ? ': ' . $notes : ''),
                    'is_manually_added' => $isEdited
                ];
            }
        } else {
            // Fallback: Calculate ingredients from production recipes if no recipe ingredients provided
            foreach ($aggregatedItems as $itemData) {
                $recipe = \App\Models\ProductionRecipe::where('production_item_id', $itemData['item_id'])
                    ->where('is_active', true)
                    ->with('details.rawMaterialItem')
                    ->first();

                if ($recipe) {
                    $multiplier = $itemData['quantity_to_produce'] / $recipe->yield_quantity;

                    foreach ($recipe->details as $detail) {
                        $ingredientId = $detail->raw_material_item_id;
                        $requiredQuantity = $detail->quantity_required * $multiplier;

                        if (!isset($ingredientRequirements[$ingredientId])) {
                            $ingredientRequirements[$ingredientId] = [
                                'ingredient_item_id' => $ingredientId,
                                'planned_quantity' => 0,
                                'unit_of_measurement' => $detail->unit_of_measurement ?: $detail->rawMaterialItem->unit_of_measurement,
                                'notes' => 'From recipe: ' . $recipe->recipe_name,
                                'is_manually_added' => false
                            ];
                        }

                        $ingredientRequirements[$ingredientId]['planned_quantity'] += $requiredQuantity;
                    }
                }
            }
        }

        // Add manual ingredients
        foreach ($manualIngredients as $manualIngredient) {
            $ingredientId = $manualIngredient['ingredient_id'];
            $quantity = floatval($manualIngredient['quantity']);
            $notes = $manualIngredient['notes'] ?? 'Manually added';

            if (!isset($ingredientRequirements[$ingredientId])) {
                $ingredient = \App\Models\ItemMaster::find($ingredientId);

                $ingredientRequirements[$ingredientId] = [
                    'ingredient_item_id' => $ingredientId,
                    'planned_quantity' => 0,
                    'unit_of_measurement' => $ingredient->unit_of_measurement,
                    'notes' => $notes,
                    'is_manually_added' => true
                ];
            }

            $ingredientRequirements[$ingredientId]['planned_quantity'] += $quantity;
            $ingredientRequirements[$ingredientId]['is_manually_added'] = true;

            // Update notes to include manual addition
            if (!empty($notes)) {
                $existingNotes = $ingredientRequirements[$ingredientId]['notes'];
                $ingredientRequirements[$ingredientId]['notes'] = $existingNotes . ($existingNotes ? '; ' : '') . $notes;
            }
        }

        // Create ProductionOrderIngredient records
        foreach ($ingredientRequirements as $requirement) {
            \App\Models\ProductionOrderIngredient::create([
                'production_order_id' => $productionOrder->id,
                'ingredient_item_id' => $requirement['ingredient_item_id'],
                'planned_quantity' => $requirement['planned_quantity'],
                'issued_quantity' => 0,
                'consumed_quantity' => 0,
                'returned_quantity' => 0,
                'unit_of_measurement' => $requirement['unit_of_measurement'],
                'notes' => $requirement['notes'],
                'is_manually_added' => $requirement['is_manually_added']
            ]);
        }

        return $ingredientRequirements;
    }



    /**
     * Display the specified production order
     */
    public function show(ProductionOrder $productionOrder)
    {
        $productionOrder->load(['items.item', 'ingredients.ingredient', 'createdBy', 'approvedBy', 'sessions']);

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
     * Issue ingredients to production order
     */
    public function issueIngredients(Request $request, ProductionOrder $productionOrder)
    {
        $request->validate([
            'ingredients' => 'required|array',
            'ingredients.*.ingredient_item_id' => 'required|exists:item_master,id',
            'ingredients.*.issued_quantity' => 'required|numeric|min:0.001'
        ]);

        DB::transaction(function () use ($request, $productionOrder) {
            foreach ($request->ingredients as $ingredientData) {
                $ingredient = \App\Models\ProductionOrderIngredient::where('production_order_id', $productionOrder->id)
                    ->where('ingredient_item_id', $ingredientData['ingredient_item_id'])
                    ->first();

                if ($ingredient) {
                    $issuedQuantity = $ingredientData['issued_quantity'];

                    // Update ingredient issued quantity
                    $ingredient->update([
                        'issued_quantity' => $ingredient->issued_quantity + $issuedQuantity
                    ]);

                    // Create inventory transaction for ingredient outgoing
                    \App\Models\ItemTransaction::create([
                        'organization_id' => Auth::user()->organization_id,
                        'branch_id' => Auth::user()->organization_id, // HQ branch
                        'inventory_item_id' => $ingredientData['ingredient_item_id'],
                        'transaction_type' => 'production_issue',
                        'quantity' => -$issuedQuantity, // Negative for outgoing
                        'cost_price' => 0, // Will be calculated based on current stock
                        'source_id' => $productionOrder->id,
                        'source_type' => 'ProductionOrder',
                        'notes' => 'Issued to production order: ' . $productionOrder->production_order_number,
                        'created_by_user_id' => Auth::id(),
                        'is_active' => true
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Ingredients issued successfully.');
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

    /**
     * Calculate ingredient requirements for aggregated items
     */
    public function calculateIngredients(Request $request)
    {
        $aggregatedItems = $request->input('aggregated_items', []);
        $ingredients = [];

        foreach ($aggregatedItems as $itemId => $itemData) {
            $quantity = $itemData['total_quantity'];

            // Find recipe for this production item
            $recipe = ProductionRecipe::where('production_item_id', $itemId)
                ->where('is_active', true)
                ->first();

            if ($recipe) {
                $recipeIngredients = $recipe->calculateIngredientsForQuantity($quantity);

                foreach ($recipeIngredients as $ingredient) {
                    $ingredientId = $ingredient['ingredient_item_id'];

                    if (isset($ingredients[$ingredientId])) {
                        $ingredients[$ingredientId]['planned_quantity'] += $ingredient['planned_quantity'];
                        $ingredients[$ingredientId]['source'] .= ', ' . $recipe->recipe_name;
                    } else {
                        $ingredients[$ingredientId] = [
                            'ingredient_item_id' => $ingredientId,
                            'ingredient_name' => $ingredient['ingredient']->name,
                            'planned_quantity' => $ingredient['planned_quantity'],
                            'unit_of_measurement' => $ingredient['unit_of_measurement'] ?: $ingredient['ingredient']->unit_of_measurement,
                            'is_manually_added' => false,
                            'source' => 'Recipe: ' . $recipe->recipe_name,
                            'notes' => $ingredient['preparation_notes'],
                            'current_stock' => $ingredient['ingredient']->getCurrentStock(),
                            'reorder_level' => $ingredient['ingredient']->reorder_level
                        ];
                    }
                }
            } else {
                // No recipe found - add warning
                $ingredients['no_recipe_' . $itemId] = [
                    'ingredient_item_id' => null,
                    'ingredient_name' => 'NO RECIPE FOUND',
                    'planned_quantity' => 0,
                    'unit_of_measurement' => '',
                    'is_manually_added' => false,
                    'source' => 'Item: ' . ($itemData['item']->name ?? 'Unknown'),
                    'notes' => 'Recipe not defined for this production item',
                    'current_stock' => 0,
                    'reorder_level' => 0,
                    'warning' => true
                ];
            }
        }

        return response()->json([
            'ingredients' => array_values($ingredients),
            'summary' => [
                'total_ingredients' => count($ingredients),
                'recipes_found' => ProductionRecipe::whereIn('production_item_id', array_keys($aggregatedItems))
                    ->where('is_active', true)
                    ->count(),
                'items_without_recipes' => count($aggregatedItems) - ProductionRecipe::whereIn('production_item_id', array_keys($aggregatedItems))
                    ->where('is_active', true)
                    ->count()
            ]
        ]);
    }

    /**
     * Get recipe details for a specific production item
     */
    public function getRecipeDetails(Request $request, $itemId)
    {
        $user = Auth::user();

        $recipe = ProductionRecipe::where('production_item_id', $itemId)
            ->where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->with(['details.rawMaterialItem', 'productionItem'])
            ->first();

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'No active recipe found for this production item'
            ], 404);
        }

        $quantity = $request->input('quantity', $recipe->yield_quantity);
        $multiplier = $quantity / $recipe->yield_quantity;

        $ingredients = $recipe->details->map(function($detail) use ($multiplier) {
            return [
                'ingredient_id' => $detail->raw_material_item_id,
                'ingredient_name' => $detail->rawMaterialItem->name,
                'quantity_required' => $detail->quantity_required * $multiplier,
                'base_quantity' => $detail->quantity_required,
                'unit_of_measurement' => $detail->unit_of_measurement ?: $detail->rawMaterialItem->unit_of_measurement,
                'preparation_notes' => $detail->preparation_notes,
                'current_stock' => $detail->rawMaterialItem->getCurrentStock()
            ];
        });

        return response()->json([
            'success' => true,
            'recipe' => [
                'id' => $recipe->id,
                'name' => $recipe->recipe_name,
                'production_item' => $recipe->productionItem->name,
                'yield_quantity' => $recipe->yield_quantity,
                'total_time' => $recipe->total_time,
                'difficulty_level' => $recipe->difficulty_level
            ],
            'production_quantity' => $quantity,
            'multiplier' => $multiplier,
            'ingredients' => $ingredients
        ]);
    }


    /**
     * Store aggregated production order from multiple requests
     */
    public function storeAggregated(Request $request)
    {
        $request->validate([
            'selected_requests' => 'required|array|min:1',
            'selected_requests.*' => 'exists:production_requests_master,id',
            'production_notes' => 'nullable|string',
            'manual_ingredients' => 'nullable|array',
            'manual_ingredients.*.ingredient_id' => 'required|exists:item_master,id',
            'manual_ingredients.*.quantity' => 'required|numeric|min:0.001',
            'manual_ingredients.*.notes' => 'nullable|string'
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Get selected production requests
                $selectedRequests = ProductionRequestMaster::with(['items.item', 'branch'])
                    ->where('organization_id', Auth::user()->organization_id)
                    ->where('status', ProductionRequestMaster::STATUS_APPROVED)
                    ->whereIn('id', $request->selected_requests)
                    ->get();

                if ($selectedRequests->isEmpty()) {
                    throw new \Exception('No valid approved requests found.');
                }

                // Create production order
                $productionOrder = ProductionOrder::create([
                    'organization_id' => Auth::user()->organization_id,
                    'production_order_number' => $this->generateOrderNumber(),
                    'production_date' => now()->addDay()->toDateString(),
                    'status' => ProductionOrder::STATUS_APPROVED,
                    'notes' => $request->production_notes,
                    'created_by_user_id' => Auth::id(),
                    'approved_by_user_id' => Auth::id(),
                    'approved_at' => now(),
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

                // Calculate and create ingredient requirements (including manual ones)
                $this->createIngredientRequirements($productionOrder, $aggregatedItems, $request->manual_ingredients ?? []);

                // Update production requests status and link to production order
                ProductionRequestMaster::whereIn('id', $request->selected_requests)
                    ->update([
                        'status' => ProductionRequestMaster::STATUS_IN_PRODUCTION,
                        'production_order_id' => $productionOrder->id
                    ]);

                return redirect()->route('admin.production.orders.show', $productionOrder)
                    ->with('success', 'Production order created successfully from ' . count($request->selected_requests) . ' requests with ingredient requirements.');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating production order: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get current stock for an ingredient
     */
    private function getCurrentStock($ingredientId, $organizationId)
    {
        // Get HQ branch for this organization
        $hqBranch = \App\Models\Branch::where('organization_id', $organizationId)
            ->where('is_head_office', true)
            ->first();

        if (!$hqBranch) {
            return 0;
        }

        // Calculate current stock from transactions
        $currentStock = \App\Models\ItemTransaction::where('inventory_item_id', $ingredientId)
            ->where('branch_id', $hqBranch->id)
            ->where('is_active', true)
            ->sum('quantity');

        return max(0, $currentStock);
    }

    public function calculateIngredientsFromRecipes(Request $request)
    {
        // TODO: Implement calculateIngredientsFromRecipes logic
        return response()->json(['message' => 'Ingredients calculated']);
    }

}
