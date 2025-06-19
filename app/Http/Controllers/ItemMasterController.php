<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\ItemMaster;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class ItemMasterController extends Controller
{
    /**
     * Display a listing of items.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        $orgId = $user->organization_id;

        $items = ItemMaster::with('category')
            ->where('organization_id', $orgId)
            ->when(request('search'), function ($query) {
                $search = strtolower(request('search'));
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(item_code) LIKE ?', ['%' . $search . '%']);
                });
            })
            ->when(request('category'), function ($query) {
                return $query->where('item_category_id', request('category'));
            })
            ->when(request('status'), function ($query) {
                $status = request('status');
                if ($status === 'active') {
                    $query->whereNull('deleted_at'); // Non-trashed items
                } elseif ($status === 'inactive') {
                    $query->onlyTrashed(); // Only soft-deleted items
                }
            })
            ->paginate(15);

        $categories = ItemCategory::active()
            ->where('organization_id', $orgId)
            ->get();


        $totalItems = ItemMaster::where('organization_id', $orgId)->count();
        $activeItems = ItemMaster::where('organization_id', $orgId)->count();
        $inactiveItems = ItemMaster::onlyTrashed()->where('organization_id', $orgId)->count();
        $newItemsToday = ItemMaster::where('organization_id', $orgId)->whereDate('created_at', today())->count();
        $inactiveItemsChange = ItemMaster::onlyTrashed()->where('organization_id', $orgId)->count();
        $activeItemsChange = ItemMaster::where('organization_id', $orgId)->count();

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
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.unicode_name' => 'nullable|string|max:255',
            'items.*.item_category_id' => 'required|exists:item_categories,id,organization_id,' . $user->organization_id,
            'items.*.item_code' => 'required|string|unique:item_master,item_code',
            'items.*.unit_of_measurement' => 'required|string|max:50',
            'items.*.reorder_level' => 'nullable|numeric|min:0',
            'items.*.is_perishable' => 'nullable|boolean',
            'items.*.shelf_life_in_days' => 'nullable|integer|min:0',
            'items.*.branch_id' => 'nullable|exists:branches,id,organization_id,' . $user->organization_id,
            'items.*.buying_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
            'items.*.is_menu_item' => 'nullable|boolean',
            'items.*.is_active' => 'nullable|boolean',
            'items.*.additional_notes' => 'nullable|string',
            'items.*.description' => 'nullable|string',
            'items.*.attributes' => 'nullable|json',
        ]);

        $createdItems = [];

        foreach ($validated['items'] as $itemData) {
            $itemData['organization_id'] = $user->organization_id;

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

        return redirect()->route('admin.inventory.items.added-items')
            ->with('success', 'Items created successfully');
    }

    /**
     * Display the specified item.
     */
    public function show($id)
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access.');
        }

        $item = ItemMaster::where('organization_id', $user->organization_id)
            ->with(['category', 'branch', 'organization']) // Add 'branch' here
            ->findOrFail($id);

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
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $item = ItemMaster::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string',
            'unicode_name' => 'nullable|string',
            'item_category_id' => 'sometimes|exists:item_categories,id,organization_id,' . $user->organization_id,
            'item_code' => 'sometimes|string|unique:item_master,item_code,' . $id,
            'unit_of_measurement' => 'sometimes|string',
            'reorder_level' => 'nullable|integer',
            'is_perishable' => 'boolean',
            'shelf_life_in_days' => 'nullable|integer',
            'branch_id' => 'nullable|exists:branches,id,organization_id,' . $user->organization_id,
            'buying_price' => 'sometimes|numeric',
            'selling_price' => 'sometimes|numeric',
            'is_menu_item' => 'boolean',
            'additional_notes' => 'nullable|string',
            'description' => 'nullable|string',
            'attributes' => 'nullable|json',
        ]);

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
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        // Get the last 10 items added
        $items = ItemMaster::with('category')
            ->where('organization_id', $user->organization_id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();



        return view('admin.inventory.items.added', compact(
            'items',
        ));
    }

    public function getItemFormPartial($index)
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access.');
        }

        $categories = ItemCategory::active()
            ->where('organization_id', $user->organization_id)
            ->get();

        return view('admin.inventory.items.partials.item-form', [
            'index' => $index,
            'categories' => $categories
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        $orgId = $user->organization_id;

        // Get stats for KPI cards
        $totalItems = ItemMaster::where('organization_id', $orgId)->count();
        $activeItems = ItemMaster::where('organization_id', $orgId)->count();
        $inactiveItems = ItemMaster::onlyTrashed()->where('organization_id', $orgId)->count();
        $newItemsToday = ItemMaster::where('organization_id', $orgId)
            ->whereDate('created_at', today())
            ->count();

        $categories = ItemCategory::active()
            ->where('organization_id', $orgId)
            ->get();

        $branches = Branch::where('is_active', true)
            ->where('organization_id', $orgId)
            ->get();

        return view('admin.inventory.items.create', compact(
            'categories',
            'branches',
            'totalItems',
            'activeItems',
            'inactiveItems',
            'newItemsToday'
        ));
    }

    public function edit($id)
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized access.');
        }

        $item = ItemMaster::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $categories = ItemCategory::active()
            ->where('organization_id', $user->organization_id)
            ->get();

        $branches = Branch::where('is_active', true)
            ->where('organization_id', $user->organization_id)
            ->get();

        return view('admin.inventory.items.edit', compact('item', 'categories', 'branches'));
    }

    /**
     * Soft delete the specified item.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user || !$user->organization_id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $item = ItemMaster::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $item->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Item deleted successfully.']);
        }

        return redirect()->route('admin.inventory.items.index')
            ->with('success', 'Item deleted successfully');
    }
}
