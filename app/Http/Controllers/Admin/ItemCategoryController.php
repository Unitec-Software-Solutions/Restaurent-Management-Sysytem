<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
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
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemCategory::query();

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin && $user->organization_id) {
            $query->where('organization_id', $user->organization_id);
        }

        $categories = $query->paginate(20);
        return response()->json($categories);
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
     * Store a new category.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $validationRules = [
            'name'        => 'required|string|unique:item_categories,name',
            'code'        => 'required|string|unique:item_categories,code',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ];

        // Super admin must select organization
        if ($isSuperAdmin) {
            $validationRules['organization_id'] = 'required|exists:organizations,id';
        }

        $data = $request->validate($validationRules);

        // Set organization_id based on user type
        if (!$isSuperAdmin) {
            $data['organization_id'] = $user->organization_id;
        }

        $category = ItemCategory::create($data);

        return response()->json($category, 201);
    }

    /**
     * Display a specific category.
     */
    public function show($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemCategory::with('items');

        // Super admin can view any category, non-super admin only their organization's categories
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return response()->json(['error' => 'No organization assigned'], 403);
            }
            $query->where('organization_id', $user->organization_id);
        }

        $category = $query->findOrFail($id);
        return response()->json($category);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemCategory::query();

        // Super admin can update any category, non-super admin only their organization's categories
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return response()->json(['error' => 'No organization assigned'], 403);
            }
            $query->where('organization_id', $user->organization_id);
        }

        $category = $query->findOrFail($id);

        $validationRules = [
            'name'        => 'sometimes|string|unique:item_categories,name,' . $id,
            'code'        => 'sometimes|string|unique:item_categories,code,' . $id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ];

        // Super admin can change organization
        if ($isSuperAdmin) {
            $validationRules['organization_id'] = 'sometimes|exists:organizations,id';
        }

        $data = $request->validate($validationRules);

        $category->update($data);

        return response()->json($category);
    }

    /**
     * Delete the specified category.
     */
    public function destroy($id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $query = ItemCategory::query();

        // Super admin can delete any category, non-super admin only their organization's categories
        if (!$isSuperAdmin) {
            if (!$user->organization_id) {
                return response()->json(['error' => 'No organization assigned'], 403);
            }
            $query->where('organization_id', $user->organization_id);
        }

        $category = $query->findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
