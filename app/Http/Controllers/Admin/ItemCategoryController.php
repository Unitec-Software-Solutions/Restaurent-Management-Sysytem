<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
class ItemCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please login to continue.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemCategory::with(['organization', 'items'])->orderBy('name');

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin && $user->organization_id) {
            $query->where('organization_id', $user->organization_id);
        }

        $categories = $query->withCount('items')->paginate(20);

        // For blade view
        if (request()->expectsJson()) {
            return response()->json($categories);
        }

        return view('admin.item-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $admin = Auth::guard('admin')->user();

        // For super admin, get all organizations
        $organizations = collect();
        if ($admin->is_super_admin) {
            $organizations = Organization::active()->orderBy('name')->get();
        }

        return view('admin.item-categories.create', compact('organizations'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validationRules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:item_categories,code',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];

        // Super admin must select organization
        if ($admin->is_super_admin) {
            $validationRules['organization_id'] = 'required|exists:organizations,id';
        }

        $validated = $request->validate($validationRules);

        // Add organization context for non-super admin
        if (!$admin->is_super_admin) {
            $validated['organization_id'] = $admin->organization_id;
        }

        // Set default active status if not provided
        $validated['is_active'] = $validated['is_active'] ?? true;

        try {
            $category = ItemCategory::create($validated);

            if ($request->expectsJson()) {
                return response()->json($category, 201);
            }

            return redirect()->route('admin.item-categories.index')
                ->with('success', 'Item Category created successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to create category'], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to create category. Please try again.');
        }
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemCategory::with(['items', 'organization']);

        // Super admin can view any category, non-super admin only their organization's categories
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return response()->json(['error' => 'No organization assigned'], 403);
            }
            $query->where('organization_id', $user->organization_id);
        }

        $category = $query->findOrFail($id);

        if (request()->expectsJson()) {
            return response()->json($category);
        }

        return view('admin.item-categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id): View
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemCategory::with('organization');

        // Apply organization filter for non-super admin
        if (!$isSuperAdmin) {
            $query->where('organization_id', $user->organization_id);
        }

        $category = $query->findOrFail($id);

        // For super admin, get all organizations
        $organizations = collect();
        if ($isSuperAdmin) {
            $organizations = Organization::active()->orderBy('name')->get();
        }

        return view('admin.item-categories.edit', compact('category', 'organizations'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemCategory::query();

        // Apply organization filter for non-super admin
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return back()->with('error', 'No organization assigned');
            }
            $query->where('organization_id', $user->organization_id);
        }

        $category = $query->findOrFail($id);

        $validationRules = [
            'name' => 'required|string|max:255|unique:item_categories,name,' . $id,
            'code' => 'required|string|max:10|unique:item_categories,code,' . $id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];

        // Super admin can change organization
        if ($isSuperAdmin) {
            $validationRules['organization_id'] = 'sometimes|exists:organizations,id';
        }

        $validated = $request->validate($validationRules);

        try {
            $category->update($validated);

            if ($request->expectsJson()) {
                return response()->json($category);
            }

            return redirect()->route('admin.item-categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to update category'], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to update category. Please try again.');
        }
    }

    /**
     * Delete the specified category.
     */
    public function destroy($id)
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemCategory::query();

        // Apply organization filter for non-super admin
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return response()->json(['error' => 'No organization assigned'], 403);
            }
            $query->where('organization_id', $user->organization_id);
        }

        $category = $query->findOrFail($id);

        try {
            // Check if category has associated items
            if ($category->items()->count() > 0) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'Cannot delete category with associated items'], 422);
                }
                return back()->with('error', 'Cannot delete category with associated items.');
            }

            $category->delete();

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Category deleted successfully.']);
            }

            return redirect()->route('admin.item-categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Failed to delete category'], 500);
            }

            return back()->with('error', 'Failed to delete category. Please try again.');
        }
    }

    /**
     * Get categories by organization ID (for super admin use)
     */
    public function getByOrganization($organizationId)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Only super admin can access categories from any organization
        if (!$user->is_super_admin) {
            // Non-super admin can only access their own organization's categories
            if ($user->organization_id != $organizationId) {
                return response()->json(['error' => 'Unauthorized access to organization'], 403);
            }
        }

        $categories = ItemCategory::active()
            ->where('organization_id', $organizationId)
            ->select('id', 'name', 'code', 'description')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Generate unique category code
     */
    private function generateCategoryCode(string $name, int $organizationId): string
    {
        $baseCode = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($name)), 0, 3));
        $code = $baseCode;
        $counter = 1;

        while (ItemCategory::where('code', $code)->where('organization_id', $organizationId)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }

        return $code;
    }
}
