<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MenuCategoryController extends Controller
{
    /**
     * Display a listing of menu categories
     */
    public function index(Request $request): View
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            abort(403, 'Unauthorized access.');
        }

        $query = MenuCategory::with(['branch', 'organization'])
            ->withCount('menuItems');

        // Apply admin scope restrictions
        if (!$this->isSuperAdmin($admin)) {
            if ($admin->branch_id) {
                $query->where('branch_id', $admin->branch_id);
            } elseif ($admin->organization_id) {
                $query->where('organization_id', $admin->organization_id);
            }
        }

        // Apply filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        // Get filter options
        $branches = $this->getAccessibleBranches($admin);
        $organizations = $this->getAccessibleOrganizations($admin);

        return view('admin.menu-categories.index', compact(
            'categories', 
            'branches', 
            'organizations'
        ));
    }

    /**
     * Show the form for creating a new menu category
     */
    public function create(): View
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            abort(403, 'Unauthorized access.');
        }

        $branches = $this->getAccessibleBranches($admin);
        $organizations = $this->getAccessibleOrganizations($admin);

        return view('admin.menu-categories.create', compact('branches', 'organizations'));
    }

    /**
     * Store a newly created menu category
     */
    public function store(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            abort(403, 'Unauthorized access.');
        }

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean'
        ];

        // Add branch and organization validation based on admin level
        if ($this->isSuperAdmin($admin)) {
            $rules['branch_id'] = 'required|exists:branches,id';
            $rules['organization_id'] = 'required|exists:organizations,id';
        } elseif ($admin->organization_id && !$admin->branch_id) {
            // Organization admin can choose branch
            $rules['branch_id'] = [
                'required',
                'exists:branches,id',
                Rule::exists('branches', 'id')->where('organization_id', $admin->organization_id)
            ];
        }

        // Add unique name validation scoped to branch
        $branchId = $request->branch_id ?? $admin->branch_id;
        if ($branchId) {
            $rules['name'][] = Rule::unique('menu_categories', 'name')
                ->where('branch_id', $branchId)
                ->whereNull('deleted_at');
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Set branch and organization based on admin level
            if (!$this->isSuperAdmin($admin)) {
                if ($admin->branch_id) {
                    $validated['branch_id'] = $admin->branch_id;
                    $validated['organization_id'] = $admin->organization_id;
                } elseif ($admin->organization_id) {
                    $validated['organization_id'] = $admin->organization_id;
                    // branch_id should be validated and set from request
                }
            }

            // Set default sort order if not provided
            if (!isset($validated['sort_order'])) {
                $maxOrder = MenuCategory::where('branch_id', $validated['branch_id'])
                    ->max('sort_order') ?? 0;
                $validated['sort_order'] = $maxOrder + 1;
            }

            $category = MenuCategory::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.menu-categories.show', $category)
                ->with('success', 'Menu category created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Menu category creation failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create menu category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified menu category
     */
    public function show(MenuCategory $menuCategory): View
    {
        $admin = Auth::guard('admin')->user();
        
        // Check access permissions
        if (!$this->canAccessCategory($admin, $menuCategory)) {
            abort(403, 'Unauthorized access to this category.');
        }

        $menuCategory->load(['branch', 'organization', 'menuItems' => function($query) {
            $query->with(['itemMaster'])->orderBy('name');
        }]);

        // Get category statistics
        $stats = [
            'total_menu_items' => $menuCategory->menuItems->count(),
            'active_menu_items' => $menuCategory->menuItems->where('is_active', true)->count(),
            'available_menu_items' => $menuCategory->menuItems->where('is_available', true)->count(),
        ];

        return view('admin.menu-categories.show', compact('menuCategory', 'stats'));
    }

    /**
     * Show the form for editing the specified menu category
     */
    public function edit(MenuCategory $menuCategory): View
    {
        $admin = Auth::guard('admin')->user();
        
        // Check access permissions
        if (!$this->canAccessCategory($admin, $menuCategory)) {
            abort(403, 'Unauthorized access to this category.');
        }

        $branches = $this->getAccessibleBranches($admin);
        $organizations = $this->getAccessibleOrganizations($admin);

        return view('admin.menu-categories.edit', compact('menuCategory', 'branches', 'organizations'));
    }

    /**
     * Update the specified menu category
     */
    public function update(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        
        // Check access permissions
        if (!$this->canAccessCategory($admin, $menuCategory)) {
            abort(403, 'Unauthorized access to this category.');
        }

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menu_categories', 'name')
                    ->where('branch_id', $menuCategory->branch_id)
                    ->ignore($menuCategory->id)
                    ->whereNull('deleted_at')
            ],
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean'
        ];

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $menuCategory->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.menu-categories.show', $menuCategory)
                ->with('success', 'Menu category updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Menu category update failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update menu category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified menu category from storage
     */
    public function destroy(MenuCategory $menuCategory): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        
        // Check access permissions
        if (!$this->canAccessCategory($admin, $menuCategory)) {
            abort(403, 'Unauthorized access to this category.');
        }

        try {
            DB::beginTransaction();

            // Check if category has menu items
            if ($menuCategory->menuItems()->count() > 0) {
                return back()->with('error', 'Cannot delete category that contains menu items. Please move or delete the menu items first.');
            }

            $menuCategory->delete();

            DB::commit();

            return redirect()
                ->route('admin.menu-categories.index')
                ->with('success', 'Menu category deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Menu category deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete menu category: ' . $e->getMessage());
        }
    }

    /**
     * Get categories for a specific branch (AJAX)
     */
    public function getCategoriesForBranch(Request $request, Branch $branch): JsonResponse
    {
        $admin = Auth::guard('admin')->user();
        
        // Check access permissions
        if (!$this->canAccessBranch($admin, $branch)) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $categories = MenuCategory::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'sort_order']);

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    /**
     * Update category sort order (AJAX)
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:menu_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['categories'] as $categoryData) {
                $category = MenuCategory::find($categoryData['id']);
                
                // Check access permissions
                if (!$this->canAccessCategory($admin, $category)) {
                    continue;
                }

                $category->update(['sort_order' => $categoryData['sort_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category order updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category sort order update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if admin is super admin
     */
    private function isSuperAdmin($admin): bool
    {
        return $admin && $admin->is_super_admin;
    }

    /**
     * Check if admin can access a specific category
     */
    private function canAccessCategory($admin, MenuCategory $category): bool
    {
        if (!$admin) {
            return false;
        }

        if ($this->isSuperAdmin($admin)) {
            return true;
        }

        if ($admin->branch_id) {
            return $category->branch_id === $admin->branch_id;
        }

        if ($admin->organization_id) {
            return $category->organization_id === $admin->organization_id;
        }

        return false;
    }

    /**
     * Check if admin can access a specific branch
     */
    private function canAccessBranch($admin, Branch $branch): bool
    {
        if (!$admin) {
            return false;
        }

        if ($this->isSuperAdmin($admin)) {
            return true;
        }

        if ($admin->branch_id) {
            return $branch->id === $admin->branch_id;
        }

        if ($admin->organization_id) {
            return $branch->organization_id === $admin->organization_id;
        }

        return false;
    }

    /**
     * Get branches accessible by admin
     */
    private function getAccessibleBranches($admin)
    {
        if ($this->isSuperAdmin($admin)) {
            return Branch::orderBy('name')->get();
        }

        if ($admin->branch_id) {
            return Branch::where('id', $admin->branch_id)->get();
        }

        if ($admin->organization_id) {
            return Branch::where('organization_id', $admin->organization_id)
                ->orderBy('name')->get();
        }

        return collect([]);
    }

    /**
     * Get organizations accessible by admin
     */
    private function getAccessibleOrganizations($admin)
    {
        if ($this->isSuperAdmin($admin)) {
            return Organization::orderBy('name')->get();
        }

        if ($admin->organization_id) {
            return Organization::where('id', $admin->organization_id)->get();
        }

        return collect([]);
    }
}
