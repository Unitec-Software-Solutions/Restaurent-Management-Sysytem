<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Admin;
use App\Services\PermissionSystemService;
use Spatie\Permission\Models\Permission;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        if (!($admin instanceof Admin)) {
            abort(403, 'Unauthorized: Only admins can access this section.');
        }

        $rolesQuery = Role::whereNull('deleted_at');

        if ($admin->is_super_admin) {
            $organizations = Organization::with('branches')->get();
        } elseif ($admin->isOrganizationAdmin()) {
            $rolesQuery->where('organization_id', $admin->organization_id);
            $organizations = Organization::where('id', $admin->organization_id)
                ->with('branches')
                ->get();
        } else {
            $rolesQuery->where('branch_id', $admin->branch_id);
            $organizations = Organization::where('id', $admin->organization_id)
                ->with('branches')
                ->get();
        }

        // Apply filters for super admins
        $selectedOrgId = $request->query('organization_id');
        $selectedBranchId = $request->query('branch_id');

        if ($admin->is_super_admin) {
            if ($selectedBranchId) {
                $rolesQuery->where('branch_id', $selectedBranchId);
            } elseif ($selectedOrgId) {
                $rolesQuery->where('organization_id', $selectedOrgId)
                    ->whereNull('branch_id');
            }
        }

        $roles = $rolesQuery->with(['organization', 'branch', 'permissions'])->get();

        return view('admin.roles.index', compact(
            'organizations',
            'roles',
            'selectedOrgId',
            'selectedBranchId'
        ));
    }

    /**
     * Show role details
     */
    public function show(Role $role)
    {
        $this->authorizeAccess($role);
        $role->load(['permissions', 'organization', 'branch']);
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Edit role form
     */
    public function edit(Role $role)
    {
        $this->authorizeAccess($role);
        $role->load(['permissions', 'organization', 'branch']);

        $admin = auth('admin')->user();
        $organizations = $this->getAuthorizedOrganizations($admin);
        $permissions = Permission::where('guard_name', 'admin')->get();

        // Get branches based on admin level
        $branches = $this->getAuthorizedBranches($admin);

        // Get permissions definitions
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions(
            $admin,
            $permissionDefinitions
        );

        // Provide availableTemplates for quick role templates (optional, can be empty)
        $availableTemplates = method_exists($this->permissionService, 'getRoleTemplates')
            ? $this->permissionService->getRoleTemplates()
            : [];

        return view('admin.roles.edit', compact(
            'role',
            'organizations',
            'permissions',
            'branches',
            'permissionDefinitions',
            'availablePermissions',
            'availableTemplates'
        ));
    }

    /**
     * Create role form
     */
    public function create(Request $request)
    {
        $admin = auth('admin')->user();

        if (!($admin instanceof Admin)) {
            abort(403, 'Unauthorized: Only admins can access this section.');
        }

        $organizations = $this->getAuthorizedOrganizations($admin);
        $branches = $this->getAuthorizedBranches($admin);

        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions(
            $admin,
            $permissionDefinitions
        );

        return view('admin.roles.create', [
            'organizations' => $organizations,
            'branches' => $branches,
            'permissionDefinitions' => $permissionDefinitions,
            'availablePermissions' => $availablePermissions
        ]);
    }

    /**
     * Store new role - FIXED PERMISSION SYNCING
     */
    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'required|string'
        ];

        // Add organization/branch rules for super admins
        if ($admin->is_super_admin) {
            $rules['organization_id'] = 'required|exists:organizations,id';
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }

        $validated = $request->validate($rules);
        $permissions = $validated['permissions'] ?? [];

        // Verify permission access
        $this->validatePermissionAccess($admin, $permissions);

        // Determine scope
        [$organizationId, $branchId] = $this->determineScope($admin, $request);

        // Check for duplicate role
        $this->checkDuplicateRole($request->name, $organizationId, $branchId);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'branch_id' => $branchId,
                'organization_id' => $organizationId,
                'guard_name' => 'admin',
            ]);

            // FIX: Properly sync permissions
            $this->syncRolePermissions($role, $permissions);

            DB::commit();

            Log::info('Role created with permissions', [
                'role_id' => $role->id,
                'name' => $role->name,
                'permissions' => $permissions,
                'by_admin' => $admin->id
            ]);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->withErrors(['error' => 'Role creation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Update existing role - FIXED PERMISSION SYNCING
     */
    public function update(Request $request, Role $role)
    {
        $admin = auth('admin')->user();
        $this->authorizeAccess($role);

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'required|string'
        ];

        // Add organization/branch rules for super admins
        if ($admin->is_super_admin) {
            $rules['organization_id'] = 'required|exists:organizations,id';
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }

        $validated = $request->validate($rules);
        $permissions = $validated['permissions'] ?? [];

        // Verify permission access
        $this->validatePermissionAccess($admin, $permissions);

        DB::beginTransaction();
        try {
            // Update basic info
            $updateData = ['name' => $request->name];

            if ($admin->is_super_admin) {
                $updateData['organization_id'] = $request->organization_id;
                $updateData['branch_id'] = $request->branch_id;
            }

            $role->update($updateData);

            // FIX: Properly sync permissions
            $this->syncRolePermissions($role, $permissions);

            DB::commit();

            Log::info('Role updated with permissions', [
                'role_id' => $role->id,
                'name' => $role->name,
                'permissions' => $permissions,
                'by_admin' => $admin->id
            ]);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role update failed', [
                'role_id' => $role->id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->withErrors(['error' => 'Role update failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete role
     */
    public function destroy(Role $role)
    {
        $admin = auth('admin')->user();
        $this->authorizeAccess($role);

        if ($role->users()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete role assigned to users'
            ]);
        }

        $role->delete();

        Log::info('Role deleted', [
            'id' => $role->id,
            'by_admin' => $admin->id
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully');
    }

    /**
     * --------------------------
     * Helper Methods
     * --------------------------
     */

    private function authorizeAccess(Role $role)
    {
        $admin = auth('admin')->user();

        if ($admin->is_super_admin) return;

        if ($admin->isOrganizationAdmin() &&
            $role->organization_id !== $admin->organization_id) {
            abort(403, 'Unauthorized for this organization');
        }

        if ($admin->isBranchAdmin() &&
            $role->branch_id !== $admin->branch_id) {
            abort(403, 'Unauthorized for this branch');
        }
    }

    private function validatePermissionAccess(Admin $admin, array $permissions)
    {
        if ($admin->is_super_admin) return;

        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions(
            $admin,
            $permissionDefinitions
        );

        foreach ($permissions as $permission) {
            if (!isset($availablePermissions[$permission])) {
                abort(403, "Unauthorized to assign permission: {$permission}");
            }
        }
    }

    private function determineScope(Admin $admin, Request $request): array
    {
        return [
            $admin->is_super_admin
                ? $request->organization_id
                : $admin->organization_id,

            $admin->is_super_admin
                ? $request->branch_id
                : ($admin->isOrganizationAdmin()
                    ? $request->branch_id
                    : $admin->branch_id)
        ];
    }

    private function checkDuplicateRole(string $name, $organizationId, $branchId)
    {
        $exists = Role::where('name', $name)
            ->where('organization_id', $organizationId)
            ->where('branch_id', $branchId)
            ->exists();

        if ($exists) {
            abort(422, 'Role name already exists in this scope');
        }
    }

    private function getAuthorizedOrganizations(Admin $admin)
    {
        if ($admin->is_super_admin) {
            return Organization::with('branches')->get();
        }
        return Organization::where('id', $admin->organization_id)
            ->with('branches')
            ->get();
    }

    private function getAuthorizedBranches(Admin $admin)
    {
        if ($admin->is_super_admin) {
            return Branch::all();
        }
        if ($admin->isOrganizationAdmin()) {
            return Branch::where('organization_id', $admin->organization_id)->get();
        }
        return Branch::where('id', $admin->branch_id)->get();
    }

    /**
     * FIX: Properly sync role permissions
     * This ensures permissions are correctly attached to the role
     */
    private function syncRolePermissions(Role $role, array $permissionNames)
    {
        // Convert permission names to actual permission models
        $permissions = Permission::whereIn('name', $permissionNames)
            ->where('guard_name', 'admin')
            ->get();

        // Verify all permissions were found
        if ($permissions->count() !== count($permissionNames)) {
            $missing = array_diff($permissionNames, $permissions->pluck('name')->toArray());
            throw new \Exception("Some permissions do not exist: " . implode(', ', $missing));
        }

        // Sync permissions to role
        $role->syncPermissions($permissions);

        // Log detailed permission sync
        Log::debug('Permissions synced to role', [
            'role_id' => $role->id,
            'permission_ids' => $permissions->pluck('id'),
            'permission_names' => $permissions->pluck('name')
        ]);
    }
}
