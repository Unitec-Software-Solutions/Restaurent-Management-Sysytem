<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\ItemCategory;
use App\Models\ItemMaster;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class ItemMasterController extends Controller
{
    use Exportable;

    /**
     * Display a listing of items.
     */
    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'No organization assigned.');
        }

        // For super admin, allow access to all organizations, for others use their org
        $orgId = $isSuperAdmin ? null : $user->organization_id;

        $query = ItemMaster::with(['category', 'organization']);

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin && $orgId) {
            $query->where('organization_id', $orgId);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('item_code', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('item_category_id', $request->input('category'));
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true)->whereNull('deleted_at');
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false)->orWhereNotNull('deleted_at');
            }
        }

        // Apply menu item filter
        if ($request->filled('menu_item')) {
            $query->where('is_menu_item', $request->input('menu_item') === '1');
        }

        // Apply perishable filter
        if ($request->filled('perishable')) {
            $query->where('is_perishable', $request->input('perishable') === '1');
        }

        // Apply price range filter
        if ($request->filled('price_min')) {
            $query->where('selling_price', '>=', $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('selling_price', '<=', $request->input('price_max'));
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Apply sorting - default to newest first
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Validate sort fields
        $allowedSortFields = ['created_at', 'name', 'item_code', 'selling_price', 'buying_price'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Handle export
        if ($request->has('export')) {
            return $this->exportToExcel($request, $query, 'inventory_items_export.xlsx', [
                'Item Code', 'Name', 'Category', 'Unit', 'Cost Price', 'Selling Price', 'Status', 'Created At'
            ]);
        }

        $items = $query->paginate(15);

        $categories = ItemCategory::active();
        if (!$isSuperAdmin && $orgId) {
            $categories->where('organization_id', $orgId);
        }
        $categories = $categories->get();

        // Statistics with organization scope
        $totalItemsQuery = ItemMaster::query();
        $activeItemsQuery = ItemMaster::query();
        $inactiveItemsQuery = ItemMaster::onlyTrashed();
        $newItemsTodayQuery = ItemMaster::query()->whereDate('created_at', today());

        if (!$isSuperAdmin && $orgId) {
            $totalItemsQuery->where('organization_id', $orgId);
            $activeItemsQuery->where('organization_id', $orgId);
            $inactiveItemsQuery->where('organization_id', $orgId);
            $newItemsTodayQuery->where('organization_id', $orgId);
        }

        $totalItems = $totalItemsQuery->count();
        $activeItems = $activeItemsQuery->count();
        $inactiveItems = $inactiveItemsQuery->count();
        $newItemsToday = $newItemsTodayQuery->count();
        $inactiveItemsChange = $inactiveItems;
        $activeItemsChange = $activeItems;

        return view('admin.inventory.items.index', compact(
            'items',
            'categories',
            'totalItems',
            'activeItems',
            'inactiveItems',
            'activeItemsChange',
            'inactiveItemsChange',
            'newItemsToday'
        ));
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // For super admin, require organization_id in request, for others use their org
        $orgId = $isSuperAdmin ? $request->organization_id : $user->organization_id;

        if (!$orgId && !$isSuperAdmin) {
            return response()->json(['message' => 'No organization assigned.'], 403);
        }

        $validationRules = [
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.unicode_name' => 'nullable|string|max:255',
            'items.*.item_category_id' => 'required|exists:item_categories,id' . ($orgId ? ',organization_id,' . $orgId : ''),
            'items.*.item_code' => 'required|string|unique:item_masters,item_code',
            'items.*.unit_of_measurement' => 'required|string|max:50',
            'items.*.reorder_level' => 'nullable|numeric|min:0',
            'items.*.is_perishable' => 'nullable|boolean',
            'items.*.shelf_life_in_days' => 'nullable|integer|min:0',
            'items.*.branch_id' => 'nullable|exists:branches,id' . ($orgId ? ',organization_id,' . $orgId : ''),
            'items.*.buying_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
            'items.*.is_menu_item' => 'nullable|boolean',
            'items.*.is_active' => 'nullable|boolean',
            'items.*.additional_notes' => 'nullable|string',
            'items.*.description' => 'nullable|string',
            'items.*.attributes' => 'nullable|json',
        ];

        // Super admin must select organization
        if ($isSuperAdmin) {
            $validationRules['organization_id'] = 'required|exists:organizations,id';
        }

        $validated = $request->validate($validationRules);

        // Validate menu attributes for menu items
        foreach ($validated['items'] as $index => $itemData) {
            if (isset($itemData['is_menu_item']) && $itemData['is_menu_item']) {
                $attributes = isset($itemData['attributes']) ? json_decode($itemData['attributes'], true) : [];

                // Required menu attributes
                $requiredMenuAttrs = ['cuisine_type', 'prep_time_minutes', 'serving_size'];
                $missingAttrs = [];

                foreach ($requiredMenuAttrs as $attr) {
                    if (empty($attributes[$attr])) {
                        $missingAttrs[] = $attr;
                    }
                }

                if (!empty($missingAttrs)) {
                    $fieldLabels = [
                        'cuisine_type' => 'Cuisine Type',
                        'prep_time_minutes' => 'Preparation Time',
                        'serving_size' => 'Serving Size'
                    ];

                    $missingLabels = array_map(fn($attr) => $fieldLabels[$attr], $missingAttrs);

                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.{$index}.menu_attributes" => "Menu items require the following attributes: " . implode(', ', $missingLabels)
                    ]);
                }
            }
        }

        $createdItems = [];

        foreach ($validated['items'] as $itemData) {
            $itemData['organization_id'] = $orgId;

            $createdItems[] = ItemMaster::create([
                'name' => $itemData['name'],
                'unicode_name' => $itemData['unicode_name'] ?? null,
                'item_category_id' => $itemData['item_category_id'],
                'item_code' => $itemData['item_code'],
                'unit_of_measurement' => $itemData['unit_of_measurement'],
                'reorder_level' => $itemData['reorder_level'] ?? 0,
                'is_perishable' => $itemData['is_perishable'] ?? false,
                'shelf_life_in_days' => $itemData['shelf_life_in_days'] ?? null,
                'branch_id' => $itemData['branch_id'] ?? null,
                'organization_id' => $itemData['organization_id'],
                'buying_price' => $itemData['buying_price'],
                'selling_price' => $itemData['selling_price'],
                'is_menu_item' => $itemData['is_menu_item'] ?? false,
                'is_active' => $itemData['is_active'] ?? true,
                'additional_notes' => $itemData['additional_notes'] ?? null,
                'description' => $itemData['description'] ?? null,
                'attributes' => $itemData['attributes'] ?? null,
            ]);
        }

        // Store the IDs of created items in session for the added items page
        $createdItemIds = collect($createdItems)->pluck('id')->toArray();
        session(['last_created_items' => $createdItemIds]);

        return redirect()->route('admin.inventory.items.added-items')
            ->with('success', 'Items created successfully');
    }

    /**
     * Display the specified item.
     */
    public function show($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemMaster::with(['category', 'branch', 'organization']);

        // Super admin can view any item, non-super admin only their organization's items
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                abort(403, 'No organization assigned.');
            }
            $query->where('organization_id', $user->organization_id);
        }

        $item = $query->findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json($item);
        }

        return view('admin.inventory.items.show', compact('item'));
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemMaster::query();

        // Super admin can update any item, non-super admin only their organization's items
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return response()->json(['message' => 'No organization assigned.'], 403);
            }
            $query->where('organization_id', $user->organization_id);
        }

        $item = $query->findOrFail($id);

        $validationRules = [
            'name' => 'sometimes|string',
            'unicode_name' => 'nullable|string',
            'item_category_id' => 'sometimes|exists:item_categories,id' . ($isSuperAdmin ? '' : ',organization_id,' . $user->organization_id),
            'item_code' => 'sometimes|string|unique:item_master,item_code,' . $id,
            'unit_of_measurement' => 'sometimes|string',
            'reorder_level' => 'nullable|integer',
            'is_perishable' => 'boolean',
            'shelf_life_in_days' => 'nullable|integer',
            'branch_id' => 'nullable|exists:branches,id' . ($isSuperAdmin ? '' : ',organization_id,' . $user->organization_id),
            'buying_price' => 'sometimes|numeric',
            'selling_price' => 'sometimes|numeric',
            'is_menu_item' => 'boolean',
            'additional_notes' => 'nullable|string',
            'description' => 'nullable|string',
            'attributes' => 'nullable|json',
            'menu_attributes' => 'nullable|array',
        ];

        // Super admin can change organization
        if ($isSuperAdmin) {
            $validationRules['organization_id'] = 'sometimes|exists:organizations,id';
        }

        $data = $request->validate($validationRules);

        // Handle menu attributes validation for edit
        if (isset($data['menu_attributes']) && $data['is_menu_item']) {
            // Validate required menu attributes
            $requiredMenuAttrs = ['cuisine_type', 'prep_time_minutes', 'serving_size'];
            $missingAttrs = [];

            foreach ($requiredMenuAttrs as $attr) {
                if (empty($data['menu_attributes'][$attr])) {
                    $missingAttrs[] = $attr;
                }
            }

            if (!empty($missingAttrs)) {
                $fieldLabels = [
                    'cuisine_type' => 'Cuisine Type',
                    'prep_time_minutes' => 'Preparation Time',
                    'serving_size' => 'Serving Size'
                ];

                $missingLabels = array_map(fn($attr) => $fieldLabels[$attr], $missingAttrs);

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'menu_attributes' => "Menu items require the following attributes: " . implode(', ', $missingLabels)
                ]);
            }

            // Get existing attributes or start with empty array
            $existingAttributes = is_array($item->attributes) ? $item->attributes : [];

            // Merge menu attributes with existing attributes
            $attributes = array_merge($existingAttributes, $data['menu_attributes']);

            // Remove empty values
            $attributes = array_filter($attributes, function($value) {
                return $value !== '' && $value !== null;
            });

            $data['attributes'] = $attributes;
        } elseif (!$data['is_menu_item']) {
            // Remove menu-specific attributes if not a menu item
            $existingAttributes = is_array($item->attributes) ? $item->attributes : [];
            $menuAttrKeys = [
                'cuisine_type', 'spice_level', 'prep_time_minutes', 'serving_size',
                'dietary_type', 'availability', 'main_ingredients', 'allergen_info',
                'is_chefs_special', 'is_popular'
            ];

            foreach ($menuAttrKeys as $key) {
                unset($existingAttributes[$key]);
            }

            $data['attributes'] = $existingAttributes;
        }

        // Remove the menu_attributes key as it's been processed
        unset($data['menu_attributes']);

        $item->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Item updated successfully',
                'data' => $item
            ]);
        }

        return redirect()->route('admin.inventory.items.index')
            ->with('success', 'Item updated successfully');
    }

    /**
     * Show recently added items
     */
    public function added(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Get the IDs of items created in the last session
        $lastCreatedItemIds = session('last_created_items', []);

        if (empty($lastCreatedItemIds)) {
            // No items in session, redirect back with message
            return redirect()->route('admin.inventory.items.index')
                ->with('info', 'No recently added items to display.');
        }

        $query = ItemMaster::with('category')
            ->whereIn('id', $lastCreatedItemIds)
            ->orderBy('created_at', 'desc');

        // Super admin can see any items, non-super admin only their organization's items
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return redirect()->route('admin.login')->with('error', 'No organization assigned.');
            }
            $query->where('organization_id', $user->organization_id);
        }

        $items = $query->get();

        // Clear the session after displaying the items
        session()->forget('last_created_items');

        return view('admin.inventory.items.added', compact('items'));
    }

    public function getItemFormPartial($index)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $categoriesQuery = ItemCategory::active();

        // Super admin gets all categories, non-super admin only their organization's categories
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                abort(403, 'No organization assigned.');
            }
            $categoriesQuery->where('organization_id', $user->organization_id);
        }

        $categories = $categoriesQuery->get();

        return view('admin.inventory.items.partials.item-form', [
            'index' => $index,
            'categories' => $categories
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'No organization assigned.');
        }

        $orgId = $isSuperAdmin ? null : $user->organization_id;

        // Get stats for KPI cards with organization scope
        $totalItemsQuery = ItemMaster::query();
        $activeItemsQuery = ItemMaster::query();
        $inactiveItemsQuery = ItemMaster::onlyTrashed();
        $newItemsTodayQuery = ItemMaster::query()->whereDate('created_at', today());

        if (!$isSuperAdmin && $orgId) {
            $totalItemsQuery->where('organization_id', $orgId);
            $activeItemsQuery->where('organization_id', $orgId);
            $inactiveItemsQuery->where('organization_id', $orgId);
            $newItemsTodayQuery->where('organization_id', $orgId);
        }

        $totalItems = $totalItemsQuery->count();
        $activeItems = $activeItemsQuery->count();
        $inactiveItems = $inactiveItemsQuery->count();
        $newItemsToday = $newItemsTodayQuery->count();

        $categoriesQuery = ItemCategory::active();
        $branchesQuery = Branch::where('is_active', true);

        if (!$isSuperAdmin && $orgId) {
            $categoriesQuery->where('organization_id', $orgId);
            $branchesQuery->where('organization_id', $orgId);
        }

        $categories = $categoriesQuery->get();
        $branches = $branchesQuery->get();

        // Get organizations for super admin dropdown
        $organizations = $isSuperAdmin ? \App\Models\Organization::active()->get() : collect();

        return view('admin.inventory.items.create', compact(
            'categories',
            'branches',
            'organizations',
            'totalItems',
            'activeItems',
            'inactiveItems',
            'newItemsToday'
        ));
    }

    public function edit($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemMaster::query();

        // Super admin can edit any item, non-super admin only their organization's items
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return redirect()->route('admin.login')->with('error', 'No organization assigned.');
            }
            $query->where('organization_id', $user->organization_id);
        }

        $item = $query->findOrFail($id);

        $categoriesQuery = ItemCategory::active();
        $branchesQuery = Branch::where('is_active', true);

        if (!$isSuperAdmin) {
            $categoriesQuery->where('organization_id', $user->organization_id);
            $branchesQuery->where('organization_id', $user->organization_id);
        } else {
            // For super admin editing, filter by item's organization
            $categoriesQuery->where('organization_id', $item->organization_id);
            $branchesQuery->where('organization_id', $item->organization_id);
        }

        $categories = $categoriesQuery->get();
        $branches = $branchesQuery->get();

        // Get organizations for super admin dropdown
        $organizations = $isSuperAdmin ? \App\Models\Organization::active()->get() : collect();

        return view('admin.inventory.items.edit', compact('item', 'categories', 'branches', 'organizations'));
    }

    /**
     * Soft delete the specified item.
     */
    public function destroy($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemMaster::query();

        // Super admin can delete any item, non-super admin only their organization's items
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return response()->json(['message' => 'No organization assigned.'], 403);
            }
            $query->where('organization_id', $user->organization_id);
        }

        $item = $query->findOrFail($id);

        $item->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Item deleted successfully.']);
        }

        return redirect()->route('admin.inventory.items.index')
            ->with('success', 'Item deleted successfully');
    }

    /**
     * Get searchable columns for inventory items
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'item_code', 'description'];
    }
}
