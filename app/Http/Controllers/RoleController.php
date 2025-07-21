<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use App\Models\Module;
use App\Models\Branch;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use App\Services\PermissionSystemService;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index(Request $request)
    {
        $admin = auth('admin')->user();

        if (!($admin instanceof \App\Models\Admin)) {
            abort(403, 'Unauthorized: Only admins can access this section.');
        }

        $rolesQuery = Role::whereNull('deleted_at');

        if ($admin->is_super_admin) {
            $organizations = Organization::with('branches')->get();
        } elseif ($admin->isOrganizationAdmin()) {
            $rolesQuery->where('organization_id', $admin->getAttribute('organization_id'));
            $organizations = Organization::where('id', $admin->getAttribute('organization_id'))->with('branches')->get();
        } else {
            $rolesQuery->where('branch_id', $admin->getAttribute('branch_id'));
            $organizations = Organization::where('id', $admin->getAttribute('organization_id'))->with('branches')->get();
        }

        $selectedOrgId = $request->query('organization_id');
        $selectedBranchId = $request->query('branch_id');

        if ($selectedBranchId && $admin->is_super_admin) {
            $rolesQuery->where('branch_id', $selectedBranchId);
        } elseif ($selectedOrgId && $admin->is_super_admin) {
            $rolesQuery->where('organization_id', $selectedOrgId)->whereNull('branch_id');
        }

        $roles = $rolesQuery->with(['organization', 'branch', 'permissions'])->get();

        return view('admin.roles.index', compact('organizations', 'roles', 'selectedOrgId', 'selectedBranchId'));
    }

    public function create(Request $request)
    {
        $admin = auth('admin')->user();

        if (!($admin instanceof \App\Models\Admin)) {
            abort(403, 'Unauthorized: Only admins can access this section.');
        }

        if ($admin->is_super_admin) {
            $organizations = Organization::with('branches')->get();
            $branches = Branch::all();
        } elseif ($admin->isOrganizationAdmin()) {
            $organizations = Organization::where('id', $admin->getAttribute('organization_id'))->with('branches')->get();
            $branches = Branch::where('organization_id', $admin->getAttribute('organization_id'))->get();
        } elseif ($admin->isBranchAdmin()) {
            $organizations = Organization::where('id', $admin->getAttribute('organization_id'))->with('branches')->get();
            $branches = Branch::where('id', $admin->getAttribute('branch_id') ?? 0)->get();
        } else {
            abort(403, 'Unauthorized: Only admins can access this section.');
        }

        // Get permission definitions and available permissions for the admin guard
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions($admin, $permissionDefinitions);

        // Provide availableTemplates for quick role templates (optional, can be empty)
        $availableTemplates = method_exists($this->permissionService, 'getRoleTemplates')
            ? $this->permissionService->getRoleTemplates()
            : [];

        return view('admin.roles.create', [
            'organizations' => $organizations,
            'branches' => $branches,
            'permissionDefinitions' => $permissionDefinitions,
            'availablePermissions' => $availablePermissions,
            'availableTemplates' => $availableTemplates
        ]);
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ];

        if ($admin->is_super_admin) {
            $rules['organization_id'] = 'required|exists:organizations,id';
            $rules['branch_id'] = 'nullable|exists:branches,id';
        } elseif ($admin->isOrganizationAdmin()) {
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }

        $validated = $request->validate($rules);

        $organizationId = $admin->is_super_admin ? $request->input('organization_id') : $admin->getAttribute('organization_id');
        $branchId = null;
        if ($admin->is_super_admin) {
            $branchId = $request->input('branch_id');
        } elseif ($admin->isOrganizationAdmin()) {
            $branchId = $request->input('branch_id');
        } elseif ($admin->isBranchAdmin()) {
            $branchId = $admin->getAttribute('branch_id');
        }

        // Validate unique role name within scope
        $existingRole = Role::where('name', $request->input('name'))
            ->where('organization_id', $organizationId)
            ->where('branch_id', $branchId)
            ->first();
        if ($existingRole) {
            return back()->withErrors(['name' => 'A role with this name already exists in this scope.']);
        }

        $role = Role::create([
            'name' => $request->input('name'),
            'branch_id' => $branchId,
            'organization_id' => $organizationId,
            'guard_name' => 'admin',
        ]);

        // Always sync permissions by ID (never by name)
        $permissions = $validated['permissions'] ?? [];
        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $admin = auth('admin')->user();

        if (!$admin->is_super_admin) {
            if ($admin->isOrganizationAdmin() && $role->getAttribute('organization_id') !== $admin->getAttribute('organization_id')) {
                abort(403, 'You can only view roles within your organization.');
            } elseif ($admin->isBranchAdmin() && $role->getAttribute('branch_id') !== $admin->getAttribute('branch_id')) {
                abort(403, 'You can only view roles within your branch.');
            }
        }

        $role->load(['permissions', 'organization', 'branch']);

        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $admin = auth('admin')->user();
        $role->load(['permissions', 'organization', 'branch']);

        if (!$admin->is_super_admin) {
            if ($admin->isOrganizationAdmin() && $role->getAttribute('organization_id') !== $admin->getAttribute('organization_id')) {
                abort(403, 'You can only edit roles within your organization.');
            } elseif ($admin->isBranchAdmin() && $role->getAttribute('branch_id') !== $admin->getAttribute('branch_id')) {
                abort(403, 'You can only edit roles within your branch.');
            }
        }

        $branches = Branch::where('organization_id', $role->getAttribute('organization_id'))->get();

        if ($admin->is_super_admin) {
            $organizations = Organization::with('branches')->get();
        } elseif ($admin->isOrganizationAdmin() || $admin->isBranchAdmin()) {
            $organizations = Organization::where('id', $admin->getAttribute('organization_id'))->with('branches')->get();
        } else {
            $organizations = collect();
        }

        // Get permission definitions and available permissions for the admin guard
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions($admin, $permissionDefinitions);

        // Provide availableTemplates for quick role templates (optional, can be empty)
        $availableTemplates = method_exists($this->permissionService, 'getRoleTemplates')
            ? $this->permissionService->getRoleTemplates()
            : [];

        return view('admin.roles.edit', [
            'role' => $role,
            'organizations' => $organizations,
            'branches' => $branches,
            'permissionDefinitions' => $permissionDefinitions,
            'availablePermissions' => $availablePermissions,
            'availableTemplates' => $availableTemplates
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $admin = auth('admin')->user();

        if (!$admin->is_super_admin) {
            if ($admin->isOrganizationAdmin() && $role->getAttribute('organization_id') !== $admin->getAttribute('organization_id')) {
                abort(403, 'You can only update roles within your organization.');
            } elseif ($admin->isBranchAdmin() && $role->getAttribute('branch_id') !== $admin->getAttribute('branch_id')) {
                abort(403, 'You can only update roles within your branch.');
            }
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ];
        if ($admin->is_super_admin) {
            $rules['organization_id'] = 'required|exists:organizations,id';
            $rules['branch_id'] = 'nullable|exists:branches,id';
        } elseif ($admin->isOrganizationAdmin()) {
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }

        $validated = $request->validate($rules);

        $orgId = $admin->is_super_admin ? $request->input('organization_id') : $role->getAttribute('organization_id');
        $branchId = $admin->is_super_admin ? $request->input('branch_id') :
            ($admin->isOrganizationAdmin() ? $request->input('branch_id') : $role->getAttribute('branch_id'));

        $existingRole = Role::where('name', $request->input('name'))
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->where('id', '!=', $role->id)
            ->first();
        if ($existingRole) {
            return back()->withErrors(['name' => 'A role with this name already exists in this scope.']);
        }

        $updateData = [
            'name' => $request->input('name'),
        ];
        if ($admin->is_super_admin) {
            $updateData['organization_id'] = $request->input('organization_id');
            $updateData['branch_id'] = $request->input('branch_id');
        } elseif ($admin->isOrganizationAdmin()) {
            $updateData['branch_id'] = $request->input('branch_id');
        }
        $role->update($updateData);

        // Always sync permissions by ID (never by name)
        $permissions = $validated['permissions'] ?? [];
        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $admin = auth('admin')->user();

        if (!$admin->is_super_admin) {
            if ($admin->isOrganizationAdmin() && $role->getAttribute('organization_id') !== $admin->getAttribute('organization_id')) {
                abort(403, 'You can only delete roles within your organization.');
            } elseif ($admin->isBranchAdmin() && $role->getAttribute('branch_id') !== $admin->getAttribute('branch_id')) {
                abort(403, 'You can only delete roles within your branch.');
            }
        }

        // Check if role is in use
        $adminsUsingRole = Admin::where('current_role_id', $role->id)->count();
        $adminsWithSpatieRole = 0;

        try {
            $adminsWithSpatieRole = Admin::role($role->name, 'admin')->count();
        } catch (\Exception $e) {}

        $totalUsage = $adminsUsingRole + $adminsWithSpatieRole;

        if ($totalUsage > 0) {
            $details = [];
            if ($adminsUsingRole > 0) $details[] = "{$adminsUsingRole} admin(s) via current_role_id";
            if ($adminsWithSpatieRole > 0) $details[] = "{$adminsWithSpatieRole} admin(s) via Spatie roles";

            return back()->withErrors(['role' => "Cannot delete role '{$role->name}' as it is assigned to: " . implode(', ', $details)])
                ->with('error', 'Role deletion failed.');
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$roleName}' deleted successfully.");
    }
}
