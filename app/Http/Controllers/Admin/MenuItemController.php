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

        // Ensure item_master_id is always set (even if not present in request)
        if (!array_key_exists('item_master_id', $validated)) {
            $validated['item_master_id'] = null;
        }

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
        if (!empty($validated['item_master_id'])) {
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
     * Create menu items from item master
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

        foreach ($itemMasters as $itemMaster) {
            // Check if menu item already exists for this item master
            $exists = MenuItem::where('item_master_id', $itemMaster->id)
                             ->where('organization_id', $itemMaster->organization_id)
                             ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Determine menu item type based on item master type or is_menu_item flag
            $menuItemType = MenuItem::TYPE_KOT; // Default to KOT for menu items
            $requiresPreparation = true;

            // If it's linked to inventory and has stock tracking, make it Buy & Sell
            if ($itemMaster->is_inventory_item && $itemMaster->current_stock !== null) {
                $menuItemType = MenuItem::TYPE_BUY_SELL;
                $requiresPreparation = false; // Direct sell items don't need kitchen preparation
            }

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
                // Copy preparation time from item master attributes if available
                'preparation_time' => $itemMaster->attributes['prep_time_minutes'] ?? 15,
                // Copy other menu-specific attributes from item master
                'spice_level' => $itemMaster->attributes['spice_level'] ?? MenuItem::SPICE_MILD,
                'is_vegetarian' => $itemMaster->attributes['dietary_type'] === 'vegetarian',
                'is_vegan' => $itemMaster->attributes['dietary_type'] === 'vegan',
                'allergen_info' => $itemMaster->attributes['allergen_info'] ?? null,
            ]);

            $created++;
        }

        $message = "Created {$created} menu items.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} items that already exist.";
        }

        return redirect()
            ->route('admin.menu-items.index')
            ->with('success', $message);
    }

    /**
     * Create standalone KOT menu items (not linked to item master)
     */
    public function createKotItems(Request $request)
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
                'item_code' => 'KOT-' . str_pad(MenuItem::where('type', MenuItem::TYPE_KOT)->count() + 1, 4, '0', STR_PAD_LEFT),
            ]);

            $created++;
        }

        return redirect()
            ->route('admin.menu-items.index')
            ->with('success', "Created {$created} KOT menu items successfully.");
    }

    /**
     * Show form for bulk KOT item creation
     */
    public function createKotForm()
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

        return view('admin.menu-items.create-kot', compact('categories', 'kitchenStations'));
    }
}
