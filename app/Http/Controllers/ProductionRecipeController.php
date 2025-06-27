<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionRecipe;
use App\Models\ProductionRecipeDetail;
use App\Models\ItemMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionRecipeController extends Controller
{
    /**
     * Display a listing of production recipes
     */
    public function index(Request $request)
    {
        $query = ProductionRecipe::with(['productionItem', 'details.rawMaterialItem'])
            ->where('organization_id', Auth::user()->organization_id);

        // Apply filters
        if ($request->filled('production_item_id')) {
            $query->where('production_item_id', $request->production_item_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $recipes = $query->latest()->paginate(20);

        // Get production items for filter
        $productionItems = ItemMaster::whereHas('category', function($query) {
            $query->where('name', 'Production Items');
        })
        ->where('organization_id', Auth::user()->organization_id)
        ->get();

        return view('admin.production.recipes.index', compact('recipes', 'productionItems'));
    }

    /**
     * Show the form for creating a new recipe
     */
    public function create()
    {
        $productionItems = ItemMaster::whereHas('category', function($query) {
            $query->where('name', 'Production Items');
        })
        ->where('organization_id', Auth::user()->organization_id)
        ->get();

        // Get both Ingredients and Raw Materials
        $rawMaterials = ItemMaster::whereHas('category', function($query) {
            $query->whereIn('name', ['Ingredients', 'Raw Materials']);
        })
        ->where('organization_id', Auth::user()->organization_id)
        ->orderBy('name')
        ->get();

        return view('admin.production.recipes.create', compact('productionItems', 'rawMaterials'));
    }

    /**
     * Store a newly created recipe
     */
    public function store(Request $request)
    {
        $request->validate([
            'production_item_id' => 'required|exists:item_master,id',
            'recipe_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'yield_quantity' => 'required|numeric|min:1',
            'preparation_time' => 'nullable|integer|min:0',
            'cooking_time' => 'nullable|integer|min:0',
            'difficulty_level' => 'nullable|string|max:50',
            'raw_materials' => 'required|array|min:1',
            'raw_materials.*.item_id' => 'required|exists:item_master,id',
            'raw_materials.*.quantity_required' => 'required|numeric|min:0.001',
            'raw_materials.*.unit_of_measurement' => 'nullable|string|max:50',
            'raw_materials.*.preparation_notes' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $totalTime = ($request->preparation_time ?? 0) + ($request->cooking_time ?? 0);

            $recipe = ProductionRecipe::create([
                'organization_id' => Auth::user()->organization_id,
                'production_item_id' => $request->production_item_id,
                'recipe_name' => $request->recipe_name,
                'description' => $request->description,
                'instructions' => $request->instructions,
                'yield_quantity' => $request->yield_quantity,
                'preparation_time' => $request->preparation_time ?? 0,
                'cooking_time' => $request->cooking_time ?? 0,
                'total_time' => $totalTime,
                'difficulty_level' => $request->difficulty_level,
                'notes' => $request->notes,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->raw_materials as $rawMaterial) {
                ProductionRecipeDetail::create([
                    'recipe_id' => $recipe->id,
                    'raw_material_item_id' => $rawMaterial['item_id'],
                    'quantity_required' => $rawMaterial['quantity_required'],
                    'unit_of_measurement' => $rawMaterial['unit_of_measurement'],
                    'preparation_notes' => $rawMaterial['preparation_notes'],
                ]);
            }
        });

        return redirect()->route('admin.production.recipes.index')
            ->with('success', 'Recipe created successfully.');
    }

    /**
     * Display the specified recipe
     */
    public function show(ProductionRecipe $recipe)
    {
        $recipe->load(['productionItem', 'details.rawMaterialItem']);

        return view('admin.production.recipes.show', compact('recipe'));
    }

    /**
     * Show the form for editing the specified recipe
     */
    public function edit(ProductionRecipe $recipe)
    {
        $recipe->load('details');

        $productionItems = ItemMaster::whereHas('category', function($query) {
            $query->where('name', 'Production Items');
        })
        ->where('organization_id', Auth::user()->organization_id)
        ->get();

        // Get both Ingredients and Raw Materials
        $rawMaterials = ItemMaster::whereHas('category', function($query) {
            $query->whereIn('name', ['Ingredients', 'Raw Materials']);
        })
        ->where('organization_id', Auth::user()->organization_id)
        ->orderBy('name')
        ->get();

        return view('admin.production.recipes.edit', compact('recipe', 'productionItems', 'rawMaterials'));
    }

    /**
     * Update the specified recipe
     */
    public function update(Request $request, ProductionRecipe $recipe)
    {
        $request->validate([
            'production_item_id' => 'required|exists:item_master,id',
            'recipe_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'yield_quantity' => 'required|numeric|min:1',
            'preparation_time' => 'nullable|integer|min:0',
            'cooking_time' => 'nullable|integer|min:0',
            'difficulty_level' => 'nullable|string|max:50',
            'raw_materials' => 'required|array|min:1',
            'raw_materials.*.item_id' => 'required|exists:item_master,id',
            'raw_materials.*.quantity_required' => 'required|numeric|min:0.001',
            'raw_materials.*.unit_of_measurement' => 'nullable|string|max:50',
            'raw_materials.*.preparation_notes' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $recipe) {
            $totalTime = ($request->preparation_time ?? 0) + ($request->cooking_time ?? 0);

            $recipe->update([
                'production_item_id' => $request->production_item_id,
                'recipe_name' => $request->recipe_name,
                'description' => $request->description,
                'instructions' => $request->instructions,
                'yield_quantity' => $request->yield_quantity,
                'preparation_time' => $request->preparation_time ?? 0,
                'cooking_time' => $request->cooking_time ?? 0,
                'total_time' => $totalTime,
                'difficulty_level' => $request->difficulty_level,
                'notes' => $request->notes,
                'is_active' => $request->is_active ?? true,
                'updated_by' => Auth::id(),
            ]);

            // Delete existing recipe details
            $recipe->details()->delete();

            // Create new recipe details
            foreach ($request->raw_materials as $rawMaterial) {
                ProductionRecipeDetail::create([
                    'recipe_id' => $recipe->id,
                    'raw_material_item_id' => $rawMaterial['item_id'],
                    'quantity_required' => $rawMaterial['quantity_required'],
                    'unit_of_measurement' => $rawMaterial['unit_of_measurement'],
                    'preparation_notes' => $rawMaterial['preparation_notes'],
                ]);
            }
        });

        return redirect()->route('admin.production.recipes.show', $recipe)
            ->with('success', 'Recipe updated successfully.');
    }

    /**
     * Toggle recipe active status
     */
    public function toggleStatus(ProductionRecipe $recipe)
    {
        $recipe->update([
            'is_active' => !$recipe->is_active,
            'updated_by' => Auth::id(),
        ]);

        $status = $recipe->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "Recipe {$status} successfully.");
    }

    /**
     * Remove the specified recipe
     */
    public function destroy(ProductionRecipe $recipe)
    {
        $recipe->delete();

        return redirect()->route('admin.production.recipes.index')
            ->with('success', 'Recipe deleted successfully.');
    }

    /**
     * Get recipe details for production calculation
     */
    public function getRecipeForProduction(Request $request, ProductionRecipe $recipe)
    {
        $quantity = $request->input('quantity', 1);
        $multiplier = $quantity / $recipe->yield_quantity;

        $details = $recipe->details->map(function ($detail) use ($multiplier) {
            return [
                'raw_material_id' => $detail->raw_material_item_id,
                'raw_material_name' => $detail->rawMaterialItem->name,
                'quantity_required' => $detail->quantity_required * $multiplier,
                'unit_of_measurement' => $detail->unit_of_measurement,
                'preparation_notes' => $detail->preparation_notes,
            ];
        });

        return response()->json([
            'recipe' => [
                'id' => $recipe->id,
                'name' => $recipe->recipe_name,
                'yield_quantity' => $recipe->yield_quantity,
                'preparation_time' => $recipe->preparation_time,
                'cooking_time' => $recipe->cooking_time,
                'total_time' => $recipe->total_time,
                'instructions' => $recipe->instructions,
            ],
            'raw_materials' => $details,
            'production_quantity' => $quantity,
            'multiplier' => $multiplier,
        ]);
    }
}
