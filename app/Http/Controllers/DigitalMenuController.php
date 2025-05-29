<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ItemCategory;
use App\Models\ItemMaster;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use Illuminate\Support\Facades\Gate;

class DigitalMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch items with their category names from test_db
        $items = DB::connection('test_db')
            ->table('item_master')
            ->join('item_categories', 'item_master.item_category_id', '=', 'item_categories.id')
            ->select(
                'item_master.id',
                'item_master.name as item_name',
                'item_master.selling_price',
                'item_categories.name as category_name'
            )
            ->orderBy('item_categories.name')
            ->orderBy('item_master.name')
            ->get();

        // Group items by category
        $groupedItems = $items->groupBy('category_name');

        return view('admin.digital-menu.index', compact('groupedItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.digital-menu.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('digital-menu', 'public');
        }

        DigitalMenu::create($validated);

        return redirect()->route('admin.digital-menu.index')
            ->with('success', 'Menu item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DigitalMenu $digitalMenu)
    {
        return view('admin.digital-menu.show', compact('digitalMenu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DigitalMenu $digitalMenu)
    {
        return view('admin.digital-menu.edit', compact('digitalMenu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DigitalMenu $digitalMenu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('digital-menu', 'public');
        }

        $digitalMenu->update($validated);

        return redirect()->route('admin.digital-menu.index')
            ->with('success', 'Menu item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DigitalMenu $digitalMenu)
    {
        $digitalMenu->delete();
        return redirect()->route('admin.digital-menu.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    public function adminindex()
    {
        // Get unique categories for the tabs
        $categories = DB::connection('test_db')
                      ->table('item_master')
                      ->select('category')
                      ->where('is_active', 1)
                      ->distinct()
                      ->orderBy('category')
                      ->pluck('category');

        return view('admin.digital-menu.index', compact('categories'));
    }

    public function getMenuItems()
    {
        // Fetch active items from item_master in test_db
        $items = DB::connection('test_db')
                 ->table('item_master')
                 ->select('category', 'name', 'price', 'description', 'image')
                 ->where('is_active', 1)
                 ->orderBy('category')
                 ->orderBy('name')
                 ->get();

        // Group by category and transform for JSON response
        $groupedItems = $items->groupBy('category')->map(function ($categoryItems) {
            return $categoryItems->map(function ($item) {
                return [
                    'name' => $item->name,
                    'price' => $item->price,
                    'description' => $item->description ?? 'Freshly prepared',
                    'image' => $item->image ?? 'https://img.icons8.com/fluency/48/ingredients-list.png'
                ];
            });
        });

        return response()->json($groupedItems);
    }

    /**
     * Show the admin digital menu view.
     *
     * @return \Illuminate\View\View
     */
    public function showDigitalMenu()
    {
        // Fetch categories with their items using Eloquent relationships
        $categories = ItemCategory::with('items')->get();
        
        // Correct view path (no extra dot at the end)
        return view('admin.digital-menu.index', compact('categories'));
    }

    public function itemList()
    {
        // Fetch categories with their items ordered by category name
        $categories = ItemCategory::with(['items' => function($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();

        return view('admin.digital-menu.itemlist', compact('categories'));
    }
}