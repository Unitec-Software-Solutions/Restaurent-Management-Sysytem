<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\ItemMaster;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\KitchenStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MenuItemController extends Controller
{
    /**
     * Display a listing of menu items
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $query = MenuItem::with(['menuCategory', 'itemMaster', 'organization', 'branch'])
                        ->active();

        // Apply organization/branch filtering based on admin type
        if (!$admin->is_super_admin) {
            $query->where('organization_id', $admin->organization_id);
            
            if ($admin->branch_id) {
                $query->where('branch_id', $admin->branch_id);
            }
        }

        // Apply filters
        if ($request->filled('category')) {
            if ($request->category === 'uncategorized') {
                $query->whereNull('menu_category_id');
            } else {
                $query->where('menu_category_id', $request->category);
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('item_code', 'ILIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'display_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $menuItems = $query->paginate(20);

        // Get filter options
        $categories = MenuCategory::active()
                                 ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                     $q->where('organization_id', $admin->organization_id);
                                 })
                                 ->orderBy('name')
                                 ->get();

        return view('admin.menu-items.index', compact('menuItems', 'categories'));
    }

    /**
     * ENHANCED: Display menu items with refined filtering and type classification
     */
    public function enhancedIndex(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $query = MenuItem::with(['menuCategory', 'itemMaster', 'organization', 'branch'])
                        ->active();

        // Apply organization/branch filtering based on admin type
        if (!$admin->is_super_admin) {
            $query->where('organization_id', $admin->organization_id);
            
            if ($admin->branch_id) {
                $query->where('branch_id', $admin->branch_id);
            }
        }

        // Enhanced filters
        if ($request->filled('category')) {
            $query->where('menu_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('item_code', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('availability')) {
            switch ($request->availability) {
                case 'available':
                    $query->where('is_available', true);
                    break;
                case 'unavailable':
                    $query->where('is_available', false);
                    break;
                case 'requires_preparation':
                    $query->where('requires_preparation', true);
                    break;
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $menuItems = $query->orderBy('display_order')
                          ->orderBy('name')
                          ->paginate(12)
                          ->withQueryString();

        // Get categories for filter dropdown
        $categories = MenuCategory::active()
                                 ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                     $q->where('organization_id', $admin->organization_id);
                                 })
                                 ->orderBy('name')
                                 ->get();

        return view('admin.menu-items.enhanced-index', compact('menuItems', 'categories'));
    }

    /**
     * Show the form for creating a new menu item
     */
    public function create(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $categories = MenuCategory::active()
                                 ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                     $q->where('organization_id', $admin->organization_id);
                                 })
                                 ->orderBy('name')
                                 ->get();

        $kitchenStations = KitchenStation::active()
                                        ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                            $q->where('organization_id', $admin->organization_id);
                                        })
                                        ->orderBy('name')
                                        ->get();

        $organizations = $admin->is_super_admin ? Organization::active()->get() : collect([$admin->organization]);
        $branches = collect();

        // If creating from item master
        $itemMaster = null;
        if ($request->filled('from_item_master')) {
            $itemMaster = ItemMaster::find($request->from_item_master);
        }

        return view('admin.menu-items.create', compact('categories', 'kitchenStations', 'organizations', 'branches', 'itemMaster'));
    }

    /**
     * Store a newly created menu item
     */
    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unicode_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'menu_category_id' => 'nullable|exists:menu_categories,id',
            'item_master_id' => 'nullable|exists:item_master,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'promotion_price' => 'nullable|numeric|min:0',
            'promotion_start' => 'nullable|date',
            'promotion_end' => 'nullable|date|after:promotion_start',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'preparation_time' => 'nullable|integer|min:0',
            'station' => 'nullable|string|max:100',
            'kitchen_station_id' => 'nullable|exists:kitchen_stations,id',
            'calories' => 'nullable|integer|min:0',
            'allergens' => 'nullable|array',
            'ingredients' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'type' => ['required', Rule::in([MenuItem::TYPE_BUY_SELL, MenuItem::TYPE_KOT])],
            'spice_level' => ['nullable', Rule::in(array_keys(MenuItem::getSpiceLevels()))],
            'display_order' => 'nullable|integer|min:0',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_vegetarian' => 'boolean',
            'is_vegan' => 'boolean',
            'is_spicy' => 'boolean',
            'contains_alcohol' => 'boolean',
            'requires_preparation' => 'boolean',
        ]);

        // Set organization and branch
        $validated['organization_id'] = $admin->is_super_admin ? 
                                      $request->organization_id : 
                                      $admin->organization_id;
        
        if (!$admin->is_super_admin && $admin->branch_id) {
            $validated['branch_id'] = $admin->branch_id;
        } else {
            $validated['branch_id'] = $request->branch_id;
        }

        // Generate item code if not provided
        if (empty($validated['item_code'])) {
            $validated['item_code'] = 'MI-' . str_pad(MenuItem::count() + 1, 4, '0', STR_PAD_LEFT);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menu-items', 'public');
            $validated['image_path'] = $imagePath;
        }

        // If created from item master, copy relevant data
        if ($validated['item_master_id']) {
            $itemMaster = ItemMaster::find($validated['item_master_id']);
            if ($itemMaster) {
                $validated['name'] = $validated['name'] ?: $itemMaster->name;
                $validated['description'] = $validated['description'] ?: $itemMaster->description;
                $validated['cost_price'] = $validated['cost_price'] ?: $itemMaster->buying_price;
                $validated['price'] = $validated['price'] ?: $itemMaster->selling_price;
                $validated['type'] = MenuItem::TYPE_BUY_SELL;
            }
        }

        $menuItem = MenuItem::create($validated);

        return redirect()
            ->route('admin.menu-items.show', $menuItem)
            ->with('success', 'Menu item created successfully.');
    }

    /**
     * Display the specified menu item
     */
    public function show(MenuItem $menuItem)
    {
        $admin = Auth::guard('admin')->user();
        
        // Check access permissions
        if (!$admin->is_super_admin && $menuItem->organization_id !== $admin->organization_id) {
            abort(403);
        }

        $menuItem->load(['menuCategory', 'itemMaster', 'organization', 'branch', 'kitchenStation', 'recipes.ingredientItem']);

        return view('admin.menu-items.show', compact('menuItem'));
    }

    /**
     * Show the form for editing the specified menu item
     */
    public function edit(MenuItem $menuItem)
    {
        $admin = Auth::guard('admin')->user();
        
        // Check access permissions
        if (!$admin->is_super_admin && $menuItem->organization_id !== $admin->organization_id) {
            abort(403);
        }

        $categories = MenuCategory::active()
                                 ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                     $q->where('organization_id', $admin->organization_id);
                                 })
                                 ->orderBy('name')
                                 ->get();

        $kitchenStations = KitchenStation::active()
                                        ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                            $q->where('organization_id', $admin->organization_id);
                                        })
                                        ->orderBy('name')
                                        ->get();

        $organizations = $admin->is_super_admin ? Organization::active()->get() : collect([$admin->organization]);
        
        $branches = Branch::active()
                         ->where('organization_id', $menuItem->organization_id)
                         ->orderBy('name')
                         ->get();

        return view('admin.menu-items.edit', compact('menuItem', 'categories', 'kitchenStations', 'organizations', 'branches'));
    }

    /**
     * Update the specified menu item
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $admin = Auth::guard('admin')->user();
        
        // Check access permissions
        if (!$admin->is_super_admin && $menuItem->organization_id !== $admin->organization_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unicode_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'menu_category_id' => 'nullable|exists:menu_categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'promotion_price' => 'nullable|numeric|min:0',
            'promotion_start' => 'nullable|date',
            'promotion_end' => 'nullable|date|after:promotion_start',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'preparation_time' => 'nullable|integer|min:0',
            'station' => 'nullable|string|max:100',
            'kitchen_station_id' => 'nullable|exists:kitchen_stations,id',
            'calories' => 'nullable|integer|min:0',
            'allergens' => 'nullable|array',
            'ingredients' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'spice_level' => ['nullable', Rule::in(array_keys(MenuItem::getSpiceLevels()))],
            'display_order' => 'nullable|integer|min:0',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_vegetarian' => 'boolean',
            'is_vegan' => 'boolean',
            'is_spicy' => 'boolean',
            'contains_alcohol' => 'boolean',
            'requires_preparation' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($menuItem->image_path) {
                Storage::disk('public')->delete($menuItem->image_path);
            }
            
            $imagePath = $request->file('image')->store('menu-items', 'public');
            $validated['image_path'] = $imagePath;
        }

        $menuItem->update($validated);

        return redirect()
            ->route('admin.menu-items.show', $menuItem)
            ->with('success', 'Menu item updated successfully.');
    }

    /**
     * Remove the specified menu item
     */
    public function destroy(MenuItem $menuItem)
    {
        $admin = Auth::guard('admin')->user();
        
        // Check access permissions
        if (!$admin->is_super_admin && $menuItem->organization_id !== $admin->organization_id) {
            abort(403);
        }

        // Check if menu item is used in any orders
        if ($menuItem->orderItems()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete menu item. It has been used in orders.');
        }

        // Delete image if exists
        if ($menuItem->image_path) {
            Storage::disk('public')->delete($menuItem->image_path);
        }

        $menuItem->delete();

        return redirect()
            ->route('admin.menu-items.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    /**
     * Get menu items for AJAX requests
     */
    public function getItems(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $query = MenuItem::with(['menuCategory'])
                        ->active()
                        ->available();

        // Apply organization/branch filtering
        if (!$admin->is_super_admin) {
            $query->where('organization_id', $admin->organization_id);
            
            if ($admin->branch_id) {
                $query->where('branch_id', $admin->branch_id);
            }
        }

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('menu_category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        $menuItems = $query->orderBy('display_order')
                          ->orderBy('name')
                          ->get();

        return response()->json([
            'success' => true,
            'menu_items' => $menuItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'current_price' => $item->current_price,
                    'is_on_promotion' => $item->is_on_promotion,
                    'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                    'category' => $item->menuCategory->name ?? 'Uncategorized',
                    'is_vegetarian' => $item->is_vegetarian,
                    'is_vegan' => $item->is_vegan,
                    'is_spicy' => $item->is_spicy,
                    'spice_level' => $item->spice_level,
                    'allergens' => $item->allergens ?? [],
                    'preparation_time' => $item->preparation_time,
                    'requires_preparation' => $item->requires_preparation,
                ];
            })
        ]);
    }

    /**
     * Create menu items from item master (Auto-determine type: Buy & Sell vs KOT)
     */
    public function createFromItemMaster(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $validated = $request->validate([
            'item_master_ids' => 'required|array',
            'item_master_ids.*' => 'exists:item_master,id',
            'menu_category_id' => 'nullable|exists:menu_categories,id',
        ]);

        $itemMasterIds = $validated['item_master_ids'];
        $categoryId = $validated['menu_category_id'];
        
        $itemMasters = ItemMaster::whereIn('id', $itemMasterIds)
                                ->where('is_menu_item', true)
                                ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                    $q->where('organization_id', $admin->organization_id);
                                })
                                ->get();

        $created = 0;
        $skipped = 0;
        $buyAndSellCount = 0;
        $kotCount = 0;
        $errors = [];

        foreach ($itemMasters as $itemMaster) {
            try {
                // Check if menu item already exists for this item master
                $exists = MenuItem::where('item_master_id', $itemMaster->id)
                                 ->where('organization_id', $itemMaster->organization_id)
                                 ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Validate prices
                if (!$itemMaster->selling_price || $itemMaster->selling_price <= 0) {
                    $errors[] = "Item '{$itemMaster->name}' has invalid selling price";
                    continue;
                }

                // Determine menu item type using refined logic
                $menuItemType = $this->determineMenuItemType($itemMaster);
                $requiresPreparation = ($menuItemType === MenuItem::TYPE_KOT);

                MenuItem::create([
                    'organization_id' => $itemMaster->organization_id,
                    'branch_id' => $itemMaster->branch_id,
                    'menu_category_id' => $categoryId,
                    'item_master_id' => $itemMaster->id,
                    'name' => $itemMaster->name,
                    'unicode_name' => $itemMaster->unicode_name,
                    'description' => $itemMaster->description,
                    'item_code' => $itemMaster->item_code,
                    'price' => $itemMaster->selling_price,
                    'cost_price' => $itemMaster->buying_price,
                    'type' => $menuItemType,
                    'is_available' => $itemMaster->is_active,
                    'requires_preparation' => $requiresPreparation,
                    // Enhanced attributes
                    'preparation_time' => $this->extractPreparationTime($itemMaster, 15),
                    'spice_level' => $this->extractSpiceLevel($itemMaster),
                    'is_vegetarian' => $this->extractDietaryInfo($itemMaster, 'vegetarian'),
                    'is_vegan' => $this->extractDietaryInfo($itemMaster, 'vegan'),
                    'allergen_info' => $this->extractAllergenInfo($itemMaster),
                    'kitchen_station_id' => $requiresPreparation ? 
                                          $this->getDefaultKitchenStation($itemMaster) : null,
                ]);

                $created++;
                
                if ($menuItemType === MenuItem::TYPE_BUY_SELL) {
                    $buyAndSellCount++;
                } else {
                    $kotCount++;
                }

            } catch (\Exception $e) {
                $errors[] = "Failed to create menu item for '{$itemMaster->name}': " . $e->getMessage();
            }
        }

        $message = "Created {$created} menu items successfully.";
        if ($buyAndSellCount > 0 || $kotCount > 0) {
            $message .= " ({$buyAndSellCount} Buy & Sell, {$kotCount} KOT items)";
        }
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} items that already exist.";
        }
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()
            ->route('admin.menu-items.index')
            ->with('success', $message);
    }

    /**
     * Determine the appropriate menu item type based on item master characteristics
     * REFINED LOGIC FOR BETTER TYPE CLASSIFICATION
     */
    private function determineMenuItemType(ItemMaster $itemMaster): int
    {
        // Logic for determining menu item type - REFINED:
        
        // 1. Check explicit attributes first
        $attributes = is_array($itemMaster->attributes) ? $itemMaster->attributes : [];
        if (isset($attributes['menu_item_type'])) {
            return $attributes['menu_item_type'] === 'buy_sell' ? MenuItem::TYPE_BUY_SELL : MenuItem::TYPE_KOT;
        }
        
        // 2. If item has trackable stock and selling price - likely Buy & Sell
        if ($itemMaster->current_stock !== null && 
            $itemMaster->current_stock > 0 && 
            $itemMaster->selling_price > 0 &&
            $itemMaster->is_inventory_item) {
            return MenuItem::TYPE_BUY_SELL;
        }
        
        // 3. Check item type classification
        $buyAndSellTypes = ['finished_product', 'retail', 'beverage', 'packaged_food'];
        $kotTypes = ['prepared', 'cooked', 'recipe', 'dish'];
        
        if (in_array($itemMaster->item_type, $buyAndSellTypes)) {
            return MenuItem::TYPE_BUY_SELL;
        }
        
        if (in_array($itemMaster->item_type, $kotTypes)) {
            return MenuItem::TYPE_KOT;
        }
        
        // 4. If item requires preparation time or has cooking instructions -> KOT
        if (isset($attributes['prep_time_minutes']) && $attributes['prep_time_minutes'] > 0) {
            return MenuItem::TYPE_KOT;
        }
        
        if (isset($attributes['requires_preparation']) && $attributes['requires_preparation'] === true) {
            return MenuItem::TYPE_KOT;
        }
        
        // 5. If item has recipe or ingredients -> KOT
        if (isset($attributes['recipe']) || isset($attributes['cooking_instructions'])) {
            return MenuItem::TYPE_KOT;
        }
        
        // 6. Perishable items with current stock -> Buy & Sell
        if ($itemMaster->is_perishable && $itemMaster->current_stock > 0) {
            return MenuItem::TYPE_BUY_SELL;
        }
        
        // 7. Default for menu items -> KOT (since most restaurant items need preparation)
        return MenuItem::TYPE_KOT;
    }

    /**
     * REFINED: Get menu eligible items with proper type classification
     */
    public function getMenuEligibleItems(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $query = ItemMaster::with(['category'])
                          ->where('is_menu_item', true)
                          ->where('is_active', true);

        // Apply organization/branch filtering based on admin type
        if (!$admin->is_super_admin) {
            $query->where('organization_id', $admin->organization_id);
            
            if ($admin->branch_id) {
                $query->where(function($q) use ($admin) {
                    $q->where('branch_id', $admin->branch_id)
                      ->orWhereNull('branch_id');
                });
            }
        }

        // Filter out items that already have menu items (prevent duplicates)
        $query->whereDoesntHave('menuItems');

        $items = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'items' => $items->map(function($item) {
                // Enhanced item data with type classification
                $menuItemType = $this->determineMenuItemType($item);
                $hasValidPrices = !empty($item->buying_price) && !empty($item->selling_price) && $item->selling_price > 0;
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'item_code' => $item->item_code,
                    'item_type' => $item->item_type,
                    'buying_price' => $item->buying_price,
                    'selling_price' => $item->selling_price,
                    'current_stock' => $item->current_stock,
                    'menu_item_type' => $menuItemType === MenuItem::TYPE_BUY_SELL ? 'Buy & Sell' : 'KOT',
                    'requires_preparation' => $menuItemType === MenuItem::TYPE_KOT,
                    'category' => $item->category,
                    'has_valid_prices' => $hasValidPrices,
                    'can_create_menu_item' => $hasValidPrices,
                    'stock_status' => $this->getItemStockStatus($item),
                    'preparation_time' => $this->extractPreparationTime($item, 15)
                ];
            })
        ]);
    }

    /**
     * Get item stock status for display
     */
    private function getItemStockStatus(ItemMaster $item): array
    {
        if (!$item->is_inventory_item || $item->current_stock === null) {
            return [
                'status' => 'not_tracked',
                'message' => 'Stock not tracked',
                'color' => 'gray'
            ];
        }

        if ($item->current_stock <= 0) {
            return [
                'status' => 'out_of_stock',
                'message' => 'Out of stock',
                'color' => 'red'
            ];
        }

        if ($item->current_stock <= ($item->reorder_level ?? 5)) {
            return [
                'status' => 'low_stock',
                'message' => 'Low stock',
                'color' => 'yellow'
            ];
        }

        return [
            'status' => 'in_stock',
            'message' => 'In stock',
            'color' => 'green'
        ];
    }

    /**
     * Show form for bulk KOT item creation
     */
    /**
     * REFINED: Show form for KOT item creation with better filtering
     */
    public function createKotForm()
    {
        $admin = Auth::guard('admin')->user();
        
        // Get menu categories
        $menuCategories = MenuCategory::active()
                                 ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                     $q->where('organization_id', $admin->organization_id);
                                 })
                                 ->orderBy('name')
                                 ->get();

        $kitchenStations = KitchenStation::active()
                                        ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                            $q->where('organization_id', $admin->organization_id);
                                        })
                                        ->orderBy('name')
                                        ->get();

        // Get item master records suitable for KOT items with enhanced filtering
        $itemMasterRecords = ItemMaster::with(['itemCategory'])
                                      ->where('is_active', true)
                                      ->where('is_menu_item', true)
                                      ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                          $q->where('organization_id', $admin->organization_id);
                                      })
                                      // Exclude items that already have KOT menu items
                                      ->whereDoesntHave('menuItems', function($q) {
                                          $q->where('type', MenuItem::TYPE_KOT);
                                      })
                                      // Enhanced filtering for KOT suitability
                                      ->where(function($q) {
                                          $q->where('item_type', 'prepared')
                                            ->orWhere('item_type', 'cooked')
                                            ->orWhere('item_type', 'recipe')
                                            ->orWhere('item_type', 'dish')
                                            ->orWhere(function($subQ) {
                                                // Items that need preparation
                                                $subQ->where('item_type', 'ingredient')
                                                     ->where(function($attr) {
                                                         $attr->whereJsonContains('attributes->requires_preparation', true)
                                                              ->orWhereNotNull('attributes->prep_time_minutes')
                                                              ->orWhereNotNull('attributes->cooking_instructions');
                                                     });
                                            });
                                      })
                                      ->orderBy('name')
                                      ->get();

        return view('admin.menu-items.create-kot', compact('menuCategories', 'kitchenStations', 'itemMasterRecords'));
    }

    /**
     * Create standalone KOT menu items (not linked to item master)
     */
    public function createStandaloneKotItems(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.preparation_time' => 'nullable|integer|min:0',
            'items.*.kitchen_station_id' => 'nullable|exists:kitchen_stations,id',
            'menu_category_id' => 'nullable|exists:menu_categories,id',
        ]);

        $created = 0;

        foreach ($validated['items'] as $itemData) {
            MenuItem::create([
                'organization_id' => $admin->is_super_admin ? 
                                  $request->organization_id : 
                                  $admin->organization_id,
                'branch_id' => $admin->is_super_admin ? 
                             $request->branch_id : 
                             $admin->branch_id,
                'menu_category_id' => $validated['menu_category_id'],
                'item_master_id' => null, 
                'name' => $itemData['name'],
                'description' => $itemData['description'] ?? null,
                'price' => $itemData['price'],
                'type' => MenuItem::TYPE_KOT,
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => $itemData['preparation_time'] ?? 15,
                'kitchen_station_id' => $itemData['kitchen_station_id'] ?? null,
                'item_code' => $this->generateKotItemCode(),
            ]);

            $created++;
        }

        return redirect()
            ->route('admin.menu-items.index')
            ->with('success', "Created {$created} standalone KOT menu items successfully.");
    }

    /**
     * Show form for standalone KOT item creation
     */
    public function createStandaloneKotForm()
    {
        $admin = Auth::guard('admin')->user();
        
        $menuCategories = MenuCategory::active()
                                 ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                     $q->where('organization_id', $admin->organization_id);
                                 })
                                 ->orderBy('name')
                                 ->get();

        $kitchenStations = KitchenStation::active()
                                        ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                            $q->where('organization_id', $admin->organization_id);
                                        })
                                        ->orderBy('name')
                                        ->get();

        return view('admin.menu-items.create-standalone-kot', compact('menuCategories', 'kitchenStations'));
    }

    /**
     * REFINED: Create KOT items from selected Item Master records with better validation
     */
    public function createKotItems(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $validated = $request->validate([
            'item_master_ids' => 'required|array|min:1',
            'item_master_ids.*' => 'exists:item_master,id',
            'menu_category_id' => 'required|exists:menu_categories,id',
            'preparation_time' => 'nullable|integer|min:1|max:240',
            'kitchen_station_id' => 'nullable|exists:kitchen_stations,id',
            'is_available' => 'boolean'
        ]);

        $itemMasterIds = $validated['item_master_ids'];
        $categoryId = $validated['menu_category_id'];
        $defaultPrepTime = $validated['preparation_time'] ?? 15;
        $kitchenStationId = $validated['kitchen_station_id'] ?? null;
        $isAvailable = $validated['is_available'] ?? true;
        
        // Get the selected item master records with enhanced filtering for KOT suitability
        $itemMasters = ItemMaster::whereIn('id', $itemMasterIds)
                                ->where('is_menu_item', true)
                                ->where('is_active', true)
                                ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                    $q->where('organization_id', $admin->organization_id);
                                })
                                ->get();

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($itemMasters as $itemMaster) {
            try {
                // Check if KOT menu item already exists for this item master
                $exists = MenuItem::where('item_master_id', $itemMaster->id)
                                 ->where('type', MenuItem::TYPE_KOT)
                                 ->where('organization_id', $itemMaster->organization_id)
                                 ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Enhanced validation for KOT items
                if (!$itemMaster->selling_price || $itemMaster->selling_price <= 0) {
                    $errors[] = "Item '{$itemMaster->name}' has invalid selling price";
                    continue;
                }

                // Determine kitchen station
                $finalKitchenStationId = $kitchenStationId ?? $this->getDefaultKitchenStation($itemMaster);

                // Create KOT menu item with enhanced attributes
                MenuItem::create([
                    'organization_id' => $itemMaster->organization_id,
                    'branch_id' => $itemMaster->branch_id,
                    'menu_category_id' => $categoryId,
                    'item_master_id' => $itemMaster->id,
                    'name' => $itemMaster->name,
                    'unicode_name' => $itemMaster->unicode_name,
                    'description' => $itemMaster->description,
                    'item_code' => $itemMaster->item_code ?: $this->generateKotItemCode(),
                    'price' => $itemMaster->selling_price,
                    'cost_price' => $itemMaster->buying_price,
                    'type' => MenuItem::TYPE_KOT,
                    'is_available' => $isAvailable,
                    'is_active' => true,
                    'requires_preparation' => true,
                    'preparation_time' => $this->extractPreparationTime($itemMaster, $defaultPrepTime),
                    'kitchen_station_id' => $finalKitchenStationId,
                    
                    // Enhanced KOT-specific attributes
                    'spice_level' => $this->extractSpiceLevel($itemMaster),
                    'is_vegetarian' => $this->extractDietaryInfo($itemMaster, 'vegetarian'),
                    'is_vegan' => $this->extractDietaryInfo($itemMaster, 'vegan'),
                    'allergen_info' => $this->extractAllergenInfo($itemMaster),
                    'ingredients' => $itemMaster->attributes['main_ingredients'] ?? null,
                    'nutritional_info' => $this->extractNutritionalInfo($itemMaster),
                    'customization_options' => $this->extractCustomizationOptions($itemMaster),
                ]);

                $created++;

            } catch (\Exception $e) {
                $errors[] = "Failed to create KOT item for '{$itemMaster->name}': " . $e->getMessage();
            }
        }

        // Prepare response message
        $message = "Created {$created} KOT menu items successfully.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} items that already exist as KOT items.";
        }
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= " and " . (count($errors) - 3) . " more errors.";
            }
        }

        return redirect()
            ->route('admin.menu-items.index')
            ->with($created > 0 ? 'success' : 'warning', $message);
    }

    /**
     * Extract nutritional information from item master
     */
    private function extractNutritionalInfo(ItemMaster $itemMaster): ?array
    {
        $attributes = is_array($itemMaster->attributes) ? $itemMaster->attributes : [];
        
        $nutritionalInfo = [];
        
        if (isset($attributes['calories'])) {
            $nutritionalInfo['calories'] = $attributes['calories'];
        }
        
        if (isset($attributes['nutritional_facts'])) {
            $nutritionalInfo = array_merge($nutritionalInfo, $attributes['nutritional_facts']);
        }
        
        return empty($nutritionalInfo) ? null : $nutritionalInfo;
    }

    /**
     * Extract customization options for KOT items
     */
    private function extractCustomizationOptions(ItemMaster $itemMaster): ?array
    {
        $attributes = is_array($itemMaster->attributes) ? $itemMaster->attributes : [];
        
        $customizations = [];
        
        // Extract spice level options
        if (isset($attributes['spice_customizable']) && $attributes['spice_customizable']) {
            $customizations['spice_level'] = [
                'type' => 'select',
                'label' => 'Spice Level',
                'options' => ['mild', 'medium', 'hot', 'very_hot'],
                'default' => $attributes['default_spice_level'] ?? 'medium'
            ];
        }
        
        // Extract size options
        if (isset($attributes['size_options'])) {
            $customizations['size'] = [
                'type' => 'select',
                'label' => 'Size',
                'options' => $attributes['size_options'],
                'price_modifiers' => $attributes['size_price_modifiers'] ?? []
            ];
        }
        
        // Extract add-ons
        if (isset($attributes['available_addons'])) {
            $customizations['addons'] = [
                'type' => 'multiselect',
                'label' => 'Add-ons',
                'options' => $attributes['available_addons']
            ];
        }
        
        return empty($customizations) ? null : $customizations;
    }

    /**
     * Extract preparation time from item master attributes
     */
    private function extractPreparationTime(ItemMaster $itemMaster, int $defaultTime = 15): int
    {
        $attributes = is_array($itemMaster->attributes) ? $itemMaster->attributes : [];
        
        if (isset($attributes['prep_time_minutes'])) {
            return (int) $attributes['prep_time_minutes'];
        }
        
        if (isset($attributes['preparation_time'])) {
            return (int) $attributes['preparation_time'];
        }
        
        // Default based on item type
        $typeDefaults = [
            'prepared' => 20,
            'cooked' => 25,
            'recipe' => 30,
            'dish' => 20,
            'beverage' => 5,
            'finished_product' => 0
        ];
        
        return $typeDefaults[$itemMaster->item_type] ?? $defaultTime;
    }

    /**
     * Extract spice level from item master
     */
    private function extractSpiceLevel(ItemMaster $itemMaster): ?string
    {
        $attributes = is_array($itemMaster->attributes) ? $itemMaster->attributes : [];
        
        if (isset($attributes['spice_level'])) {
            return $attributes['spice_level'];
        }
        
        if (isset($attributes['default_spice_level'])) {
            return $attributes['default_spice_level'];
        }
        
        return null;
    }

    /**
     * Extract dietary information from item master
     */
    private function extractDietaryInfo(ItemMaster $itemMaster, string $type): bool
    {
        $attributes = is_array($itemMaster->attributes) ? $itemMaster->attributes : [];
        
        if (isset($attributes['dietary_info'][$type])) {
            return (bool) $attributes['dietary_info'][$type];
        }
        
        if (isset($attributes['is_' . $type])) {
            return (bool) $attributes['is_' . $type];
        }
        
        // Check if mentioned in description
        if ($itemMaster->description) {
            $description = strtolower($itemMaster->description);
            return str_contains($description, $type);
        }
        
        return false;
    }

    /**
     * Extract allergen information from item master
     */
    private function extractAllergenInfo(ItemMaster $itemMaster): ?array
    {
        $attributes = is_array($itemMaster->attributes) ? $itemMaster->attributes : [];
        
        if (isset($attributes['allergens'])) {
            return is_array($attributes['allergens']) ? $attributes['allergens'] : [];
        }
        
        if (isset($attributes['allergen_info'])) {
            return is_array($attributes['allergen_info']) ? $attributes['allergen_info'] : [];
        }
        
        return null;
    }

    /**
     * Get default kitchen station for item
     */
    private function getDefaultKitchenStation(ItemMaster $itemMaster): ?int
    {
        $admin = Auth::guard('admin')->user();
        
        // Try to find station based on item type
        $stationMapping = [
            'cooked' => 'Main Kitchen',
            'prepared' => 'Prep Station',
            'beverage' => 'Beverage Station',
            'dish' => 'Main Kitchen',
            'recipe' => 'Main Kitchen'
        ];
        
        $preferredStationName = $stationMapping[$itemMaster->item_type] ?? 'Main Kitchen';
        
        $station = KitchenStation::where('name', 'ILIKE', "%{$preferredStationName}%")
                                ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                    $q->where('organization_id', $admin->organization_id);
                                })
                                ->first();
        
        return $station ? $station->id : null;
    }

    /**
     * Generate KOT item code
     */
    private function generateKotItemCode(): string
    {
        $count = MenuItem::where('type', MenuItem::TYPE_KOT)->count();
        return 'KOT-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }


}
