<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DigitalMenuController extends Controller
{
    public function index()
    {
        // Get all active menus for digital display
        $activeMenus = \App\Models\Menu::active()
            ->with(['menuItems.category', 'branch'])
            ->get();

        // Group menus by branch for better organization
        $menusByBranch = $activeMenus->groupBy('branch.name');

        return view('admin.digital-menu.index', compact('menusByBranch', 'activeMenus'));
    }

    /**
     * Display digital menu for a specific branch
     */
    public function show($branchId = null)
    {
        $branch = null;
        if ($branchId) {
            $branch = \App\Models\Branch::findOrFail($branchId);
        }

        $menus = \App\Models\Menu::active()
            ->when($branch, function ($query) use ($branch) {
                return $query->where('branch_id', $branch->id);
            })
            ->with(['menuItems.category'])
            ->get();

        return view('admin.digital-menu.show', compact('menus', 'branch'));
    }

    /**
     * Get menu data for digital display API
     */
    public function api($branchId = null)
    {
        $menus = \App\Models\Menu::active()
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->with(['menuItems.category'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $menus->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'description' => $menu->description,
                    'type' => $menu->type,
                    'items' => $menu->menuItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'price' => $item->price,
                            'category' => $item->category ? 
                                (is_object($item->category) ? $item->category->name : $item->category) : 
                                'Uncategorized',
                            'image' => $item->image_path,
                            'is_available' => $item->is_available ?? true,
                            'allergens' => $item->allergen_info ?? [],
                            'nutritional_info' => $item->nutritional_info ?? []
                        ];
                    })
                ];
            })
        ]);
    }
}
