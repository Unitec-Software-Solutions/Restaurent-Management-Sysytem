<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Organization;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use App\Services\PermissionSystemService;

class RoleController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        // Filter roles based on admin scope
        $rolesQuery = \App\Models\Role::query();

        if ($admin->is_super_admin) {
            // Super admin sees all roles
            $organizations = \App\Models\Organization::with('branches')->get();
        } elseif ($admin->isOrganizationAdmin()) {
            // Org admin sees only their org's roles
            $rolesQuery->where('organization_id', $admin->organization_id);
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->with('branches')->get();
        } else {
            // Branch admin sees only their branch's roles
            $rolesQuery->where('branch_id', $admin->branch_id);
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->with('branches')->get();
        }

        // Apply filters from request
        $selectedOrgId = $request->query('organization_id');
        $selectedBranchId = $request->query('branch_id');

        if ($selectedBranchId && $admin->is_super_admin) {
            $rolesQuery->where('branch_id', $selectedBranchId);
        } elseif ($selectedOrgId && $admin->is_super_admin) {
            $rolesQuery->where('organization_id', $selectedOrgId)->whereNull('branch_id');
        }

        $roles = $rolesQuery->with('organization', 'branch')->get();

        return view('admin.roles.index', compact('organizations', 'roles', 'selectedOrgId', 'selectedBranchId'));
    }

    public function create(Request $request)
    {
        $admin = auth('admin')->user();

        // Get available organizations and branches based on admin scope
        if ($admin->is_super_admin) {
            $organizations = \App\Models\Organization::with('branches')->get();
            $branches = \App\Models\Branch::all();
        } elseif ($admin->isOrganizationAdmin()) {
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->with('branches')->get();
            $branches = \App\Models\Branch::where('organization_id', $admin->organization_id)->get();
        } else {
            // Branch admin
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->with('branches')->get();
            $branches = \App\Models\Branch::where('id', $admin->branch_id)->get();
        }

        // Get permission definitions and role templates from the service
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $roleTemplates = $this->permissionService->getRoleTemplates();

        // Filter role templates based on admin scope
        $availableTemplates = $this->filterTemplatesByScope($roleTemplates, $admin);

        // Get available permissions based on admin scope
        $availablePermissions = $this->getAvailablePermissions($admin, $permissionDefinitions);

        return view('admin.roles.create', compact(
            'organizations', 
            'branches', 
            'permissionDefinitions',
            'availableTemplates',
            'availablePermissions'
        ));
    }

    /**
     * Filter role templates based on admin scope
     */
    private function filterTemplatesByScope($roleTemplates, $admin): array
    {
        return $this->permissionService->filterTemplatesByScope($roleTemplates, $admin);
    }

    /**
     * Get available permissions based on admin scope
     */
    private function getAvailablePermissions($admin, $permissionDefinitions): array
    {
        return $this->permissionService->getAvailablePermissions($admin, $permissionDefinitions);
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        // Validate based on admin scope
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'permissions' => 'array',
            'permissions.*' => 'string'
        ];

        // Scope-specific validations
        if ($admin->is_super_admin) {
            $rules['organization_id'] = 'required|exists:organizations,id';
            $rules['branch_id'] = 'nullable|exists:branches,id';
        } elseif ($admin->isOrganizationAdmin()) {
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }

        $request->validate($rules);

        // Determine organization and branch based on admin scope
        $organizationId = $admin->is_super_admin ? $request->organization_id : $admin->organization_id;
        $branchId = null;

        if ($admin->is_super_admin) {
            $branchId = $request->branch_id;
        } elseif ($admin->isOrganizationAdmin()) {
            $branchId = $request->branch_id;
        } elseif ($admin->isBranchAdmin()) {
            $branchId = $admin->branch_id;
        }

        // Validate unique role name within scope
        $existingRole = \App\Models\Role::where('name', $request->name)
            ->where('organization_id', $organizationId)
            ->where('branch_id', $branchId)
            ->first();

        if ($existingRole) {
            return back()->withErrors(['name' => 'A role with this name already exists in this scope.']);
        }

        // Create the role
        $role = \App\Models\Role::create([
            'name' => $request->name,
            'branch_id' => $branchId,
            'organization_id' => $organizationId,
            'guard_name' => 'admin',
        ]);

        // Validate and assign permissions
        if ($request->permissions) {
            $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
            $availablePermissions = $this->getAvailablePermissions($admin, $permissionDefinitions);
            
            // Filter requested permissions to only those available to this admin
            $validPermissions = array_intersect($request->permissions, array_keys($availablePermissions));
            
            if (!empty($validPermissions)) {
                $permissionIds = \App\Models\Permission::whereIn('name', $validPermissions)
                    ->pluck('id')->toArray();
                $role->permissions()->sync($permissionIds);
            }
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully with ' . count($validPermissions ?? []) . ' permissions.');
    }

    public function edit(\App\Models\Role $role)
    {
        $admin = auth('admin')->user();

        // Check if admin can edit this role
        if (!$admin->is_super_admin) {
            if ($admin->isOrganizationAdmin() && $role->organization_id !== $admin->organization_id) {
                abort(403, 'You can only edit roles within your organization.');
            } elseif ($admin->isBranchAdmin() && $role->branch_id !== $admin->branch_id) {
                abort(403, 'You can only edit roles within your branch.');
            }
        }

        // Get available organizations and branches based on admin scope
        if ($admin->is_super_admin) {
            $organizations = \App\Models\Organization::all();
            $branches = \App\Models\Branch::where('organization_id', $role->organization_id)->get();
        } elseif ($admin->isOrganizationAdmin()) {
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->get();
            $branches = \App\Models\Branch::where('organization_id', $admin->organization_id)->get();
        } else {
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->get();
            $branches = \App\Models\Branch::where('id', $admin->branch_id)->get();
        }

        // Get permission definitions and available permissions
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->getAvailablePermissions($admin, $permissionDefinitions);
        
        // Get role templates and filter by scope
        $roleTemplates = $this->permissionService->getRoleTemplates();
        $availableTemplates = $this->filterTemplatesByScope($roleTemplates, $admin);

        return view('admin.roles.edit', compact(
            'role', 
            'organizations', 
            'branches', 
            'permissionDefinitions',
            'availablePermissions',
            'availableTemplates'
        ));
    }

    public function update(Request $request, \App\Models\Role $role)
    {
        $admin = auth('admin')->user();

        // Check if admin can update this role
        if (!$admin->is_super_admin) {
            if ($admin->isOrganizationAdmin() && $role->organization_id !== $admin->organization_id) {
                abort(403, 'You can only update roles within your organization.');
            } elseif ($admin->isBranchAdmin() && $role->branch_id !== $admin->branch_id) {
                abort(403, 'You can only update roles within your branch.');
            }
        }

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'permissions' => 'array',
            'permissions.*' => 'string'
        ];

        $request->validate($rules);

        // Check for unique name within scope (excluding current role)
        $existingRole = \App\Models\Role::where('name', $request->name)
            ->where('organization_id', $role->organization_id)
            ->where('branch_id', $role->branch_id)
            ->where('id', '!=', $role->id)
            ->first();

        if ($existingRole) {
            return back()->withErrors(['name' => 'A role with this name already exists in this scope.']);
        }

        // Update basic role information
        $role->update([
            'name' => $request->name,
        ]);

        // Validate and update permissions
        if ($request->has('permissions')) {
            $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
            $availablePermissions = $this->getAvailablePermissions($admin, $permissionDefinitions);
            
            // Filter requested permissions to only those available to this admin
            $validPermissions = array_intersect($request->permissions, array_keys($availablePermissions));
            
            $permissionIds = \App\Models\Permission::whereIn('name', $validPermissions)
                ->pluck('id')->toArray();
            $role->permissions()->sync($permissionIds);
        } else {
            // No permissions selected, remove all
            $role->permissions()->sync([]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(\App\Models\Role $role)
    {
        $admin = auth('admin')->user();

        // Check if admin can delete this role
        if (!$admin->is_super_admin) {
            if ($admin->isOrganizationAdmin() && $role->organization_id !== $admin->organization_id) {
                abort(403, 'You can only delete roles within your organization.');
            } elseif ($admin->isBranchAdmin() && $role->branch_id !== $admin->branch_id) {
                abort(403, 'You can only delete roles within your branch.');
            }
        }

        // Check if role is in use by admins or users
        $adminsUsingRole = \App\Models\Admin::where('current_role_id', $role->id)->count();
        
        // Also check if role is assigned via Spatie's role system
        $adminsWithSpatieRole = 0;
        $usersWithSpatieRole = 0;
        
        try {
            // Check admin guard if role has admin guard
            if ($role->guard_name === 'admin') {
                $adminsWithSpatieRole = \App\Models\Admin::role($role->name, 'admin')->count();
            }
        } catch (\Exception $e) {
            // Role doesn't exist for admin guard, which is fine
        }
        
        try {
            // Check web guard if role has web guard
            if ($role->guard_name === 'web') {
                $usersWithSpatieRole = \App\Models\User::role($role->name, 'web')->count();
            }
        } catch (\Exception $e) {
            // Role doesn't exist for web guard, which is fine
        }
        
        $totalUsage = $adminsUsingRole + $adminsWithSpatieRole + $usersWithSpatieRole;
        
        if ($totalUsage > 0) {
            $details = [];
            if ($adminsUsingRole > 0) $details[] = "{$adminsUsingRole} admin(s) via current_role_id";
            if ($adminsWithSpatieRole > 0) $details[] = "{$adminsWithSpatieRole} admin(s) via Spatie roles";
            if ($usersWithSpatieRole > 0) $details[] = "{$usersWithSpatieRole} user(s) via Spatie roles";
            
            return back()->withErrors(['role' => "Cannot delete role '{$role->name}' as it is assigned to: " . implode(', ', $details)])
                ->with('error', 'Role deletion failed.');
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$roleName}' deleted successfully.");
    }

    public function assignRole(Request $request, $userId)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($userId);
        $user->roles()->sync([$validated['role_id']]);

        return redirect()->route('admin.users.show', $userId)->with('success', 'Role assigned successfully.');
    }

    public function assignModules(Request $request, Organization $org, Role $role)
    {
        $request->validate(['modules' => 'array']);

        $permissions = \App\Models\Module::whereIn('id', $request->modules)
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('id');

        $role->syncPermissions($permissions);

        return response()->json([
            'message' => 'Permissions updated',
            'permissions' => $role->permissions
        ]);
    }     
    public function updatePermissions(Request $request, \App\Models\Role $role)
    {
        $role->syncPermissions($request->input('permissions', []));
        return redirect()->route('admin.roles.index')
            ->with('success', 'Permissions updated successfully');
    }

    public function permissions($id)
    {
        $role = \App\Models\Role::findOrFail($id);
        $permissions = \App\Models\Permission::all();
        return view('admin.roles.permissions', compact('role', 'permissions'));
    }


    public function assign(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_id' => 'required|exists:roles,id'
            ]);
            
            $user = \App\Models\User::findOrFail($request->user_id);
            $role = \App\Models\Role::findOrFail($request->role_id);
            
            $user->assignRole($role);
            
            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
