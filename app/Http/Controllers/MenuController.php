<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MenuSystemService;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ItemMaster;
use App\Models\Organization;

class MenuController extends Controller
{
    private MenuSystemService $menuService;

    public function __construct(MenuSystemService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index(Request $request, Branch $branch)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();
        $menu = $this->menuService->getDailyMenu($branch, $date);

        return view('menu.index', [
            'branch' => $branch,
            'menu' => $menu,
            'selectedDate' => $date,
            'availableDates' => $this->getAvailableDates($branch)
        ]);
    }

    public function show(Request $request, Branch $branch, $itemId)
    {
        $item = \App\Models\MenuItem::where('branch_id', $branch->id)
            ->findOrFail($itemId);

        return view('menu.show', [
            'branch' => $branch,
            'item' => $item,
            'availability' => $this->menuService->checkItemAvailability($item)
        ]);
    }

    private function getAvailableDates(Branch $branch): array
    {
        return collect(range(0, 7))->map(function ($days) {
            $date = Carbon::now()->addDays($days);
            return [
                'date' => $date->format('Y-m-d'),
                'display' => $date->format('l, M j'),
                'is_today' => $days === 0
            ];
        })->toArray();
    }

    /**
     * Get menus for organization (AJAX)
     */
    public function getMenus()
    {
        $admin = auth('admin')->user();
        $organization = $admin->organization;

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'No organization found'
            ]);
        }

        $menus = $organization->menus()
            ->with(['menuItems.category'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'menus' => $menus
        ]);
    }

    /**
     * Store new menu (AJAX)
     */
    public function storeMenu(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $admin = auth('admin')->user();
            $organization = $admin->organization;

            if (!$organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'No organization found'
                ]);
            }

            $menu = $organization->menus()->create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Menu created successfully',
                'menu' => $menu
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create menu'
            ]);
        }
    }

    /**
     * Store new menu item (AJAX)
     */
    public function storeMenuItem(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:menu_categories,id',
            'is_available' => 'boolean',
        ]);

        try {
            $admin = auth('admin')->user();
            $organization = $admin->organization;

            // Verify menu belongs to organization
            $menu = $organization->menus()->findOrFail($request->menu_id);

            $menuItem = $menu->menuItems()->create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'is_available' => $request->boolean('is_available', true),
                'organization_id' => $organization->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Menu item created successfully',
                'menu_item' => $menuItem->load('category')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create menu item'
            ]);
        }
    }

    /**
     * Validate menu item assignments
     */
    public function validateMenuAssignments(Organization $organization)
    {
        // Get all items marked as menu items
        $menuItemMasters = ItemMaster::where('organization_id', $organization->id)
            ->where('is_menu', true)
            ->get();

        // Get all menu items for this organization
        $assignedMenuItems = \App\Models\MenuItem::where('organization_id', $organization->id)
            ->get()
            ->pluck('item_master_id')
            ->toArray();

        $unassignedItems = $menuItemMasters->filter(function($item) use ($assignedMenuItems) {
            return !in_array($item->id, $assignedMenuItems);
        });

        $results = [
            'total_menu_items_in_master' => $menuItemMasters->count(),
            'assigned_to_menu' => count($assignedMenuItems),
            'unassigned_items' => $unassignedItems->count(),
            'unassigned_list' => $unassignedItems->pluck('name', 'id')->toArray()
        ];

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        }

        return view('menus.validation', compact('results', 'organization'));
    }
}
