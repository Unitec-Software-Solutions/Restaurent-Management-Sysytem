<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Branch;
use App\Services\MenuScheduleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    protected MenuScheduleService $menuScheduleService;

    public function __construct(MenuScheduleService $menuScheduleService)
    {
        $this->menuScheduleService = $menuScheduleService;
    }

    /**
     * Display the menu management dashboard
     */
    public function index(): View
    {
        $activeMenus = Menu::active()->with(['menuItems', 'branch'])->get();
        $upcomingMenus = Menu::where('valid_from', '>', now())
            ->with(['menuItems', 'branch'])
            ->orderBy('valid_from')
            ->take(5)
            ->get();
        
        $todayActivations = Menu::whereDate('valid_from', today())
            ->count();
        
        $totalMenus = Menu::count();
        $totalActiveMenus = Menu::active()->count();
        
        return view('admin.menus.index', compact(
            'activeMenus',
            'upcomingMenus',
            'todayActivations',
            'totalMenus',
            'totalActiveMenus'
        ));
    }

    /**
     * Display all menus with filters
     */
    public function list(Request $request): View
    {
        $query = Menu::with(['menuItems', 'branch']);

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            } elseif ($request->status === 'upcoming') {
                $query->where('date_from', '>', now());
            } elseif ($request->status === 'expired') {
                $query->where('date_to', '<', now());
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_from', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_to', '<=', $request->date_to);
        }

        $menus = $query->orderBy('date_from', 'desc')->paginate(15);
        $branches = Branch::all();
        
        return view('admin.menus.list', compact('menus', 'branches'));
    }

    /**
     * Show the form for creating a new menu
     */
    public function create(): View
    {
        $branches = Branch::all();
        $categories = MenuCategory::with('menuItems')->get();
        $menuItems = MenuItem::active()->get();
        
        return view('admin.menus.create', compact('branches', 'categories', 'menuItems'));
    }

    /**
     * Store a newly created menu
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:breakfast,lunch,dinner,all_day,special,seasonal',
            'branch_id' => 'required|exists:branches,id',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'available_days' => 'required|array|min:1',
            'available_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'menu_items' => 'required|array|min:1',
            'menu_items.*' => 'exists:menu_items,id',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Check for overlapping menus of the same type
            $this->validateMenuOverlap($validated);

            $menu = Menu::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'branch_id' => $validated['branch_id'],
                'organization_id' => $validated['organization_id'] ?? Auth::user()->organization_id ?? 1,
                'date_from' => $validated['valid_from'],
                'date_to' => $validated['valid_until'],
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'],
                'available_days' => $validated['available_days'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'is_active' => $validated['is_active'] ?? false,
                'created_by' => Auth::user()->id
            ]);

            // Attach menu items
            $menu->menuItems()->attach($validated['menu_items']);

            DB::commit();

            return redirect()
                ->route('admin.menus.show', $menu)
                ->with('success', 'Menu created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Menu creation failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create menu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified menu
     */
    public function show(Menu $menu): View
    {
        $menu->load(['menuItems.menuCategory', 'branch', 'creator']);
        
        // Provide safe analytics - actual order relationships may need setup
        $analytics = [
            'total_orders' => 0,
            'total_revenue' => 0.00,
            'popular_items' => $this->getPopularItems($menu),
            'availability_status' => $menu->shouldBeActiveNow()
        ];
        
        return view('admin.menus.show', compact('menu', 'analytics'));
    }

    /**
     * Show the form for editing the specified menu
     */
    public function edit(Menu $menu): View
    {
        $branches = Branch::all();
        $categories = MenuCategory::with('menuItems')->get();
        $menuItems = MenuItem::active()->get();
        $attachedItems = $menu->menuItems->pluck('id')->toArray();
        
        return view('admin.menus.edit', compact('menu', 'branches', 'categories', 'menuItems', 'attachedItems'));
    }

    /**
     * Update the specified menu
     */
    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:breakfast,lunch,dinner,all_day,special,seasonal',
            'branch_id' => 'required|exists:branches,id',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'available_days' => 'required|array|min:1',
            'available_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'menu_items' => 'required|array|min:1',
            'menu_items.*' => 'exists:menu_items,id',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Check for overlapping menus (excluding current menu)
            $this->validateMenuOverlap($validated, $menu->id);

            $menu->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'branch_id' => $validated['branch_id'],
                'date_from' => $validated['valid_from'],
                'date_to' => $validated['valid_until'],
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'],
                'available_days' => $validated['available_days'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'is_active' => $validated['is_active'] ?? false,
                'updated_by' => Auth::user()->id
            ]);

            // Sync menu items
            $menu->menuItems()->sync($validated['menu_items']);

            DB::commit();

            return redirect()
                ->route('admin.menus.show', $menu)
                ->with('success', 'Menu updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Menu update failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update menu: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified menu from storage
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        try {
            // Check if menu has active orders
            if ($menu->orders()->whereIn('status', ['pending', 'confirmed', 'preparing'])->exists()) {
                return back()->with('error', 'Cannot delete menu with active orders.');
            }

            $menu->delete();

            return redirect()
                ->route('admin.menus.list')
                ->with('success', 'Menu deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Menu deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete menu: ' . $e->getMessage());
        }
    }

    /**
     * Calendar view for menu scheduling
     */
    public function calendar(Request $request): View
    {
        $date = $request->get('date', now()->format('Y-m'));
        $startDate = Carbon::parse($date)->startOfMonth();
        $endDate = Carbon::parse($date)->endOfMonth();
        
        $menus = Menu::whereBetween('date_from', [$startDate, $endDate])
            ->orWhereBetween('date_to', [$startDate, $endDate])
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->where('date_from', '<=', $startDate)
                      ->where(function ($q) use ($endDate) {
                          $q->where('date_to', '>=', $endDate)
                            ->orWhereNull('date_to');
                      });
            })
            ->with(['branch'])
            ->get();
        
        $branches = Branch::all();
        
        return view('admin.menus.calendar', compact('menus', 'startDate', 'endDate', 'branches'));
    }

    /**
     * Activate a menu manually
     */    public function activate(Menu $menu): JsonResponse
    {
        try {
            if ($menu->shouldBeActiveNow()) {
                $menu->activate();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Menu activated successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu cannot be activated at this time (outside valid date/time range)'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Menu activation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate a menu manually
     */    public function deactivate(Menu $menu): JsonResponse
    {
        try {
            $menu->deactivate();
            
            return response()->json([
                'success' => true,
                'message' => 'Menu deactivated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Menu deactivation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview menu for customers
     */
    public function preview(Menu $menu): View
    {
        $menu->load(['menuItems.menuCategory']);
        
        return view('admin.menus.preview', compact('menu'));
    }

    /**
     * Bulk create menus for a date range
     */
    public function bulkCreate(): View
    {
        $branches = Branch::all();
        $categories = MenuCategory::with('menuItems')->get();
        
        return view('admin.menus.bulk-create', compact('branches', 'categories'));
    }

    /**
     * Handle bulk operations - create or store
     */
    public function bulk(Request $request): View|RedirectResponse
    {
        // Check if this is a store operation (POST request)
        if ($request->isMethod('post')) {
            return $this->bulkStore($request);
        }
        
        // Otherwise, return the bulk create view
        return $this->bulkCreate();
    }

    /**
     * Store bulk created menus
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_template' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:breakfast,lunch,dinner,all_day,special,seasonal',
            'branch_id' => 'required|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'available_days' => 'required|array|min:1',
            'available_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'menu_items' => 'required|array|min:1',
            'menu_items.*' => 'exists:menu_items,id'
        ]);

        try {
            DB::beginTransaction();

            $createdMenus = [];
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            while ($startDate->lte($endDate)) {
                $dayName = strtolower($startDate->format('l'));
                
                if (in_array($dayName, $validated['available_days'])) {
                    $menu = Menu::create([
                        'name' => str_replace('{date}', $startDate->format('Y-m-d'), $validated['name_template']),
                        'description' => $validated['description'],
                        'type' => $validated['type'],
                        'branch_id' => $validated['branch_id'],
                        'organization_id' => Auth::user()->organization_id ?? 1,
                        'date_from' => $startDate->toDateString(),
                        'date_to' => $startDate->toDateString(),
                        'valid_from' => $startDate->toDateString(),
                        'valid_until' => $startDate->toDateString(),
                        'available_days' => [$dayName],
                        'start_time' => $validated['start_time'],
                        'end_time' => $validated['end_time'],
                        'is_active' => false,
                        'created_by' => Auth::user()->id
                    ]);

                    $menu->menuItems()->attach($validated['menu_items']);
                    $createdMenus[] = $menu;
                }

                $startDate->addDay();
            }

            DB::commit();

            return redirect()
                ->route('admin.menus.list')
                ->with('success', count($createdMenus) . ' menus created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk menu creation failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create menus: ' . $e->getMessage());
        }
    }

    /**
     * Get menu data for calendar view
     */
    public function getCalendarData(Request $request): JsonResponse
    {
        $start = $request->get('start');
        $end = $request->get('end');
        
        $menus = Menu::whereBetween('date_from', [$start, $end])
            ->orWhereBetween('date_to', [$start, $end])
            ->with(['branch'])
            ->get();

        $events = $menus->map(function ($menu) {
            return [
                'id' => $menu->id,
                'title' => $menu->name,
                'start' => $menu->date_from,
                'end' => $menu->date_to ? Carbon::parse($menu->date_to)->addDay()->toDateString() : null,
                'backgroundColor' => $menu->is_active ? '#10b981' : '#6b7280',
                'borderColor' => $menu->is_active ? '#059669' : '#4b5563',
                'textColor' => 'white',
                'extendedProps' => [
                    'type' => $menu->type,
                    'branch' => $menu->branch->name,
                    'status' => $menu->is_active ? 'active' : 'inactive'
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Validate menu overlap
     */
    private function validateMenuOverlap(array $data, ?int $excludeId = null): void
    {
        $query = Menu::where('branch_id', $data['branch_id'])
            ->where('type', $data['type'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('date_from', [$data['valid_from'], $data['valid_until'] ?? '9999-12-31'])
                  ->orWhereBetween('date_to', [$data['valid_from'], $data['valid_until'] ?? '9999-12-31'])
                  ->orWhere(function ($sq) use ($data) {
                      $sq->where('date_from', '<=', $data['valid_from'])
                         ->where(function ($ssq) use ($data) {
                             $ssq->where('date_to', '>=', $data['valid_until'] ?? '9999-12-31')
                                ->orWhereNull('date_to');
                         });
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \Exception('Menu dates overlap with existing menu of the same type and branch.');
        }
    }

    /**
     * Get popular items for a menu
     */
    private function getPopularItems(Menu $menu): array
    {
        // This would need to be implemented based on order item tracking
        return [];
    }

    /**
     * Show menu safety dashboard
     */
    public function safetyDashboard()
    {
        $admin = auth('admin')->user();
        
        // Get branches based on admin permissions
        $branches = Branch::when(!$admin->is_super_admin && $admin->organization_id, 
            fn($q) => $q->where('organization_id', $admin->organization_id)
        )->active()->get();

        return view('admin.menus.safety-dashboard', compact('branches'));
    }

    /**
     * Bulk activate menus
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_ids' => 'required|array|min:1',
            'menu_ids.*' => 'exists:menus,id'
        ]);

        try {
            DB::beginTransaction();

            $activatedCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($validated['menu_ids'] as $menuId) {
                $menu = Menu::find($menuId);
                if ($menu && $menu->shouldBeActiveNow()) {
                    if ($menu->activate()) {
                        $activatedCount++;
                    } else {
                        $failedCount++;
                        $errors[] = "Failed to activate menu: {$menu->name}";
                    }
                } else {
                    $failedCount++;
                    $errors[] = $menu ? "Menu '{$menu->name}' cannot be activated at this time" : "Menu not found";
                }
            }

            DB::commit();

            $message = "Successfully activated {$activatedCount} menu(s)";
            if ($failedCount > 0) {
                $message .= ", {$failedCount} failed";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'activated' => $activatedCount,
                'failed' => $failedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk menu activation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to activate menus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk deactivate menus
     */
    public function bulkDeactivate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_ids' => 'required|array|min:1',
            'menu_ids.*' => 'exists:menus,id'
        ]);

        try {
            DB::beginTransaction();

            $deactivatedCount = Menu::whereIn('id', $validated['menu_ids'])
                                   ->update(['is_active' => false]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deactivated {$deactivatedCount} menu(s)",
                'deactivated' => $deactivatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk menu deactivation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate menus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display menu items manager - manage which items are included in menus
     */
    public function manager(): View
    {
        $user = Auth::user();
        
        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access.');
        }

        // Get all items with menu-related information
        $items = \App\Models\ItemMaster::where('organization_id', $user->organization_id)
            ->with(['category'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->name ?? 'Uncategorized',
                    'selling_price' => $item->selling_price,
                    'is_menu_item' => $item->is_menu_item,
                    'attributes' => $item->attributes,
                    'cuisine_type' => $item->attributes['cuisine_type'] ?? null,
                    'spice_level' => $item->attributes['spice_level'] ?? null,
                    'prep_time_minutes' => $item->attributes['prep_time_minutes'] ?? null,
                    'availability' => $item->attributes['availability'] ?? null,
                    'is_chefs_special' => $item->attributes['is_chefs_special'] ?? false,
                    'is_popular' => $item->attributes['is_popular'] ?? false,
                ];
            });

        // Get categories for filtering
        $categories = \App\Models\ItemCategory::where('organization_id', $user->organization_id)->get();

        // Statistics
        $totalItems = $items->count();
        $menuItems = $items->where('is_menu_item', true)->count();
        $nonMenuItems = $totalItems - $menuItems;
        $chefsSpecials = $items->where('is_chefs_special', true)->count();

        return view('admin.menus.manager', compact(
            'items',
            'categories',
            'totalItems',
            'menuItems',
            'nonMenuItems',
            'chefsSpecials'
        ));
    }

    /**
     * Toggle menu status for an item
     */
    public function toggleMenuStatus(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->organization_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'item_id' => 'required|exists:item_master,id',
            'is_menu' => 'required|boolean'
        ]);

        $item = \App\Models\ItemMaster::where('organization_id', $user->organization_id)
            ->findOrFail($request->item_id);

        $item->update(['is_menu_item' => $request->is_menu]);

        return response()->json([
            'success' => true,
            'message' => $request->is_menu ? 'Item added to menu' : 'Item removed from menu'
        ]);
    }

    /**
     * Bulk update menu status for multiple items
     */
    public function bulkUpdateMenuStatus(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->organization_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:item_master,id',
            'is_menu' => 'required|boolean'
        ]);

        $updated = \App\Models\ItemMaster::where('organization_id', $user->organization_id)
            ->whereIn('id', $request->item_ids)
            ->update(['is_menu_item' => $request->is_menu]);

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} items",
            'count' => $updated
        ]);
    }
}
