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
        
        // Determine validation rules based on admin type
        $validationRules = [
            'name' => 'required|string|max:255',
            'unicode_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'menu_category_id' => 'required|exists:menu_categories,id',
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
            'type' => ['required', Rule::in([MenuItem::TYPE_BUY_SELL,MenuItem::TYPE_PRODUCTION, MenuItem::TYPE_KOT])],
            'spice_level' => ['nullable', Rule::in(array_keys(MenuItem::getSpiceLevels()))],
            'display_order' => 'nullable|integer|min:0',
            'item_code' => 'nullable|string|max:50',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_vegetarian' => 'boolean',
            'is_vegan' => 'boolean',
            'is_spicy' => 'boolean',
            'contains_alcohol' => 'boolean',
            'requires_preparation' => 'boolean',
        ];

        // Add organization and branch validation for super admins
        if ($admin->is_super_admin) {
            $validationRules['organization_id'] = 'required|exists:organizations,id';
            $validationRules['branch_id'] = 'nullable|exists:branches,id';
        }

        $validated = $request->validate($validationRules);

        // Set organization and branch based on admin type
        if ($admin->is_super_admin) {
            // Super admin must provide organization_id
            $validated['organization_id'] = $request->organization_id;
            $validated['branch_id'] = $request->branch_id ?? null;
        } else if ($admin->organization_id) {
            // Organization/Branch admin uses their own org
            $validated['organization_id'] = $admin->organization_id;
            $validated['branch_id'] = $admin->branch_id ?? null;
        } else {
            // Fallback error - this should not happen
            return redirect()->back()
                ->withErrors(['error' => 'Unable to determine organization context.'])
                ->withInput();
        }

        // Verify organization_id is set
        if (empty($validated['organization_id'])) {
            return redirect()->back()
                ->withErrors(['organization_id' => 'Organization must be specified.'])
                ->withInput();
        }

        // If created from item master, inherit organization and branch from the item master
        $itemMasterId = $validated['item_master_id'] ?? null;
        if ($itemMasterId) {
            $itemMaster = ItemMaster::find($itemMasterId);
            if ($itemMaster) {
                // For non-super admins, ensure item master belongs to their org
                if (!$admin->is_super_admin && $itemMaster->organization_id !== $admin->organization_id) {
                    return redirect()->back()
                        ->withErrors(['item_master_id' => 'Selected item does not belong to your organization.'])
                        ->withInput();
                }
                
                // Inherit data from item master
                $validated['name'] = $validated['name'] ?: $itemMaster->name;
                $validated['description'] = $validated['description'] ?: $itemMaster->description;
                $validated['cost_price'] = $validated['cost_price'] ?: $itemMaster->buying_price;
                $validated['price'] = $validated['price'] ?: $itemMaster->selling_price;
                $validated['type'] = MenuItem::TYPE_BUY_SELL;
                
                // Use item master's organization and branch for consistency
                // But ensure organization_id is never null
                if ($itemMaster->organization_id) {
                    $validated['organization_id'] = $itemMaster->organization_id;
                    $validated['branch_id'] = $itemMaster->branch_id;
                }
                // If item master has no organization, keep the current validated organization_id
            }
        }

        // Generate item code if not provided
        if (empty($validated['item_code'])) {
            $validated['item_code'] = 'MI-' . str_pad(MenuItem::count() + 1, 4, '0', STR_PAD_LEFT);
        }

        // Provide default station if not provided (database constraint requires it)
        if (empty($validated['station'])) {
            $validated['station'] = 'Kitchen';
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menu-items', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Final safety check - ensure organization_id is never null
        if (empty($validated['organization_id'])) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to determine organization context. Please try again.'])
                ->withInput();
        }

        // Ensure menu_category_id is set for database constraint
        if (empty($validated['menu_category_id'])) {
            return redirect()->back()
                ->withErrors(['menu_category_id' => 'Menu category is required.'])
                ->withInput();
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
        return view('admin.menu-items.edit', compact('menuItem', 'categories', 'kitchenStations'));
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
            'allergens' => 'nullable|string',
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

        // Handle allergens as JSON array
        if (isset($validated['allergens'])) {
            $validated['allergens'] = json_decode($validated['allergens'], true) ?? [];
        }

        if ($request->hasFile('image')) {
            if ($menuItem->image_path) {
                Storage::disk('public')->delete($menuItem->image_path);
            }
            $imagePath = $request->file('image')->store('menu-items', 'public');
            $validated['image_path'] = $imagePath;
        }

        $menuItem->update($validated);

        return redirect()->route('admin.menu-items.show', $menuItem)->with('success', 'Menu item updated successfully.');
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
                                ->where('selling_price', '>', 0)
                                ->when(!$admin->is_super_admin, function($q) use ($admin) {
                                    $q->where('organization_id', $admin->organization_id);
                                })
                                ->get();

        $created = 0;
        $skipped = 0;
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

                // Create Buy & Sell menu item
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
                    'type' => MenuItem::TYPE_BUY_SELL, 
                    'is_available' => $itemMaster->is_active,
                    'requires_preparation' => false, // Buy & Sell items don't require preparation
                ]);

                $created++;

            } catch (\Exception $e) {
                $errors[] = "Failed to create menu item for '{$itemMaster->name}': " . $e->getMessage();
            }
        }

        $message = "Created {$created} Buy & Sell menu items successfully.";
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
     * ENHANCED: Show form for KOT item creation with better filtering
     * Only shows items suitable for KOT (production items)
     */
    public function createKotForm()
    {
        $admin = Auth::guard('admin')->user();
        
        // Get organizations and branches for super admin
        $organizations = collect();
        $branches = collect();
        
        if ($admin->is_super_admin) {
            $organizations = Organization::active()->orderBy('name')->get();
            $branches = Branch::active()->orderBy('name')->get();
        }
        
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

        // For KOT creation, do not retrieve from item master; allow manual entry
        $itemMasterRecords = collect();

        return view('admin.menu-items.create-kot', compact('menuCategories', 'kitchenStations', 'itemMasterRecords', 'organizations', 'branches'));
    }

    /**
     * REFINED: Determine the appropriate menu item type based on item master characteristics
     * Enhanced logic for better type classification with clear rules
     */
    private function determineMenuItemType(ItemMaster $itemMaster): int
    {
        // CLEAR CLASSIFICATION RULES:
        
        // 1. EXPLICIT TYPE CHECK: Check the item_type field first (most reliable)
        if ($itemMaster->item_type === 'buy_sell') {
            return MenuItem::TYPE_BUY_SELL;
        }
        
        if ($itemMaster->item_type === 'kot_production') {
            return MenuItem::TYPE_KOT;
        }
        
        // 2. PRODUCTION REQUIREMENT CHECK: If requires production, it's definitely KOT
        if ($itemMaster->requires_production === true) {
            return MenuItem::TYPE_KOT;
        }
        
        // 3. INVENTORY & STOCK CHECK: If has trackable inventory and current stock, likely Buy & Sell
        if ($itemMaster->is_inventory_item && 
            $itemMaster->current_stock !== null && 
            $itemMaster->current_stock > 0 && 
            $itemMaster->selling_price > 0) {
            return MenuItem::TYPE_BUY_SELL;
        }
        
        // 4. ATTRIBUTES CHECK: Check JSON attributes for specific indicators
        $attributes = is_array($itemMaster->attributes) ? $itemMaster->attributes : [];
        
        // Check for explicit menu item type in attributes
        if (isset($attributes['menu_item_type'])) {
            return $attributes['menu_item_type'] === 'buy_sell' ? MenuItem::TYPE_BUY_SELL : MenuItem::TYPE_KOT;
        }
        
        // Check for preparation indicators
        if (isset($attributes['prep_time_minutes']) && $attributes['prep_time_minutes'] > 0) {
            return MenuItem::TYPE_KOT;
        }
        
        if (isset($attributes['requires_preparation']) && $attributes['requires_preparation'] === true) {
            return MenuItem::TYPE_KOT;
        }
        
        // Check for recipe/cooking instructions
        if (isset($attributes['recipe']) || isset($attributes['cooking_instructions']) || isset($attributes['ingredients'])) {
            return MenuItem::TYPE_KOT;
        }
        
        // 5. STOCK STATUS CHECK: Items with no stock but valid selling price -> KOT
        if ((!$itemMaster->current_stock || $itemMaster->current_stock <= 0) && 
            $itemMaster->selling_price > 0) {
            return MenuItem::TYPE_KOT;
        }
        
        // 6. PERISHABLE ITEMS: Perishable items with stock -> Buy & Sell
        if ($itemMaster->is_perishable && $itemMaster->current_stock > 0) {
            return MenuItem::TYPE_BUY_SELL;
        }
        
        // 7. DEFAULT CLASSIFICATION: For restaurant items, default to KOT unless explicitly set otherwise
        // This ensures items requiring preparation aren't missed
        return MenuItem::TYPE_KOT;
    }

    /**
     * Get menu eligible items from ItemMaster (ONLY Buy & Sell items with is_menu_item = true)
     */
    public function getMenuEligibleItems(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $query = ItemMaster::with(['itemCategory'])
                          ->where('is_menu_item', true)  // Only items marked for menu
                          ->where('is_active', true)
                          ->where('selling_price', '>', 0); // Must have selling price

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
        $query->whereDoesntHave('menuItems', function($q) {
            $q->where('is_active', true);
        });

        $items = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'items' => $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'item_code' => $item->item_code,
                    'buying_price' => $item->buying_price,
                    'selling_price' => $item->selling_price,
                    'current_stock' => $item->current_stock,
                    'unit_of_measurement' => $item->unit_of_measurement,
                    'menu_item_type' => 'Buy & Sell', // All items from ItemMaster are Buy & Sell
                    'category' => $item->itemCategory,
                    'can_create_menu_item' => true,
                    'stock_status' => $item->current_stock > 0 ? 'In Stock' : 'Out of Stock',
                    'type_indicator' => [
                        'type' => 'stock',
                        'icon' => 'fas fa-boxes',
                        'color' => 'blue',
                        'description' => 'Ready for direct sale with stock tracking'
                    ]
                ];
            })
        ]);
    }

    /**
     * Get item stock status for display purposes
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
     * REFINED: Create KOT-specific menu items with enhanced validation and attributes
     * Updated: Manual KOT creation does NOT require item master ids
     */
    public function createKotItems(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'menu_category_id' => 'required|exists:menu_categories,id',
            'preparation_time' => 'nullable|integer|min:1|max:240',
            'kitchen_station_id' => 'nullable|exists:kitchen_stations,id',
            'description' => 'nullable|string|max:1000',
            'is_available' => 'boolean',
        ];
        if ($admin->is_super_admin) {
            $rules['organization_id'] = 'required|exists:organizations,id';
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }
        $validated = $request->validate($rules);

        $validated['type'] = MenuItem::TYPE_KOT;
        $validated['requires_preparation'] = true;
        $validated['is_active'] = true;
        $validated['organization_id'] = $admin->is_super_admin ? $request->organization_id : $admin->organization_id;
        $validated['branch_id'] = $admin->is_super_admin ? $request->branch_id : $admin->branch_id;
        $validated['station'] = 'Kitchen';
        if (empty($validated['preparation_time'])) {
            $validated['preparation_time'] = 15;
        }
        if (empty($validated['is_available'])) {
            $validated['is_available'] = true;
        }
        $validated['price'] = $request->input('price', 0);
        $validated['currency'] = 'LKR';
        $validated['spice_level'] = $request->input('spice_level', 'mild');
        // Generate item code for KOT
        $validated['item_code'] = $this->generateKotItemCode();

        // Final safety check for organization_id
        if (empty($validated['organization_id'])) {
            return redirect()->back()->withErrors(['organization_id' => 'Organization must be specified.'])->withInput();
        }

        $menuItem = MenuItem::create($validated);

        return redirect()
            ->route('admin.menu-items.show', $menuItem)
            ->with('success', 'KOT menu item created successfully.');
    }

    /**
     * Get menu items for order creation
     * Only returns menu items from activated menus
     */
    public function getActivatedMenuItems(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $branchId = $request->get('branch_id', $admin->branch_id);
        
        // Get menu items that belong to activated menus
        $query = MenuItem::with(['menuCategory', 'itemMaster'])
            ->where('is_active', true)
            ->where('is_available', true)
            // Add check for activated menus here when menu activation system is implemented
            ->when($branchId, function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when(!$admin->is_super_admin, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            });
            
        $menuItems = $query->get()->map(function($item) use ($branchId) {
            // Determine current stock for Buy & Sell items
            $currentStock = 0;
            $itemType = $item->type ?? MenuItem::TYPE_KOT;
            
            if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id && $item->itemMaster) {
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $branchId);
            }
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'category' => $item->menuCategory?->name ?? 'Uncategorized',
                'type' => $itemType,
                'type_name' => $itemType === MenuItem::TYPE_BUY_SELL ? 'Buy & Sell' : 'KOT',
                'requires_preparation' => $item->requires_preparation,
                'preparation_time' => $item->preparation_time,
                'current_stock' => $currentStock,
                'availability' => $this->determineItemAvailability($item, $currentStock, $itemType),
                'item_master_id' => $item->item_master_id,
                'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                'is_vegetarian' => $item->is_vegetarian,
                'is_vegan' => $item->is_vegan,
                'spice_level' => $item->spice_level,
                'allergens' => $item->allergen_info ?? [],
            ];
        });
        
        return response()->json([
            'success' => true,
            'items' => $menuItems
        ]);
    }
    
    /**
     * Determine item availability based on type and stock
     */
    private function determineItemAvailability($item, $currentStock, $itemType): string
    {
        if (!$item->is_available || !$item->is_active) {
            return 'unavailable';
        }
        
        if ($itemType === MenuItem::TYPE_BUY_SELL) {
            if ($currentStock <= 0) {
                return 'out_of_stock';
            } elseif ($currentStock <= 5) {
                return 'low_stock';
            } else {
                return 'available';
            }
        } else {
            // KOT items are always available if active (made to order)
            return 'available';
        }
    }

    /**
     * Get all menu items for display - combines buy & sell items and KOT items
     */
    public function getAllMenuItems(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $branchId = $request->get('branch_id', $admin->branch_id);
        
        // Get all menu items (both buy & sell and KOT types)
        $query = MenuItem::with(['menuCategory', 'itemMaster', 'recipes.ingredient'])
            ->where('is_active', true)
            ->when($branchId, function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when(!$admin->is_super_admin, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            });
            
        // Apply filters if provided
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
        
        $menuItems = $query->get()->map(function($item) use ($branchId) {
            $itemType = $item->type ?? MenuItem::TYPE_KOT;
            $currentStock = 0;
            $canMake = true;
            
            if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id && $item->itemMaster) {
                // Buy & Sell item - check inventory stock
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $branchId);
                $canMake = $currentStock > 0;
            } elseif ($itemType === MenuItem::TYPE_KOT) {
                // KOT item - check if all ingredients are available
                $canMake = $this->checkKotIngredientAvailability($item, $branchId);
            }
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->current_price,
                'category' => $item->menuCategory?->name ?? 'Uncategorized',
                'category_id' => $item->menu_category_id,
                'type' => $itemType,
                'type_name' => $itemType === MenuItem::TYPE_BUY_SELL ? 'Buy & Sell' : 'KOT Recipe',
                'source' => $itemType === MenuItem::TYPE_BUY_SELL ? 'Item Master' : 'KOT Recipe',
                'requires_preparation' => $item->requires_preparation,
                'preparation_time' => $item->preparation_time,
                'current_stock' => $currentStock,
                'can_make' => $canMake,
                'availability_status' => $canMake ? 'available' : 'out_of_stock',
                'item_master_id' => $item->item_master_id,
                'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                'is_vegetarian' => $item->is_vegetarian,
                'is_vegan' => $item->is_vegan,
                'is_spicy' => $item->is_spicy,
                'spice_level' => $item->spice_level,
                'allergens' => $item->allergen_info ?? [],
                'ingredient_count' => $itemType === MenuItem::TYPE_KOT ? $item->recipes->count() : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Menu items retrieved successfully',
            'items' => $menuItems,
            'total_count' => $menuItems->count(),
            'buy_sell_count' => $menuItems->where('type', MenuItem::TYPE_BUY_SELL)->count(),
            'kot_count' => $menuItems->where('type', MenuItem::TYPE_KOT)->count(),
        ]);
    }
    
    /**
     * Check if KOT item can be made based on ingredient availability
     */
    private function checkKotIngredientAvailability($menuItem, $branchId): bool
    {
        if (!$menuItem->recipes || $menuItem->recipes->isEmpty()) {
            return true; // If no recipe defined, assume available
        }
        
        foreach ($menuItem->recipes as $recipe) {
            if (!$recipe->ingredient) continue;
            
            $availableStock = \App\Models\ItemTransaction::stockOnHand($recipe->ingredient_item_id, $branchId);
            $requiredStock = $recipe->actual_quantity_needed; // This includes waste percentage
            
            if ($availableStock < $requiredStock) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Generate a unique KOT item code (e.g., KOT-0001)
     */
    private function generateKotItemCode(): string
    {
        $count = \App\Models\MenuItem::where('type', \App\Models\MenuItem::TYPE_KOT)->count();
        return 'KOT-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

}
