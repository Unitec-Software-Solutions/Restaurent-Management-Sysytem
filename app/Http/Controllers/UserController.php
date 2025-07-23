<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\User;
use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\PermissionSystemService;

class UserController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
        }


    public function index()
    {
        $admin = auth('admin')->user();
        // Always use Spatie roles relationship for admins
        if ($admin->isSuperAdmin()) {
            $admins = Admin::with(['roles', 'organization', 'branch'])->get();
        } elseif ($admin->isOrganizationAdmin()) {
            $admins = Admin::where('organization_id', $admin->organization->id ?? null)
                ->with(['roles', 'organization', 'branch'])
                ->get();
        } else {
            $admins = Admin::where('branch_id', $admin->branch->id ?? null)
                ->with(['roles', 'organization', 'branch'])
                ->get();
        }
        return view('admin.users.index', ['admins' => $admins]);
    }

    // Show form to create a new user
    public function create(Request $request)
    {
        $this->authorize('create', User::class);
        $admin = auth('admin')->user();

        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions($admin, $permissionDefinitions);

        // Only show roles that are available for the current admin's org/branch/plan
        $rolesQuery = Role::with('permissions')
            ->where('guard_name', 'admin')
            ->whereNull('deleted_at'); // Exclude soft deleted roles

        if ($admin->isSuperAdmin()) {
            // all roles for admin guard
        } elseif ($admin->isOrganizationAdmin()) {
            $rolesQuery->where('organization_id', $admin->organization->id ?? null);
        } else {
            $rolesQuery->where('branch_id', $admin->branch->id ?? null);
        }
        $roles = $rolesQuery->get();

        // Fallback: If no roles found, show all roles for admin guard
        if ($roles->isEmpty()) {
            $roles = Role::where('guard_name', 'admin')->get();
        }

        if ($admin->is_super_admin) {
            $organizations = Organization::all();
            $allBranches = Branch::with('organization')->get();
            $branches = $allBranches;
            $adminTypes = [
                'org_admin' => 'Organization Admin',
                'branch_admin' => 'Branch Admin'
            ];
        } elseif ($admin->isOrganizationAdmin()) {
            $organizations = Organization::where('id', $admin->organization->id ?? null)->get();
            $branches = Branch::where('organization_id', $admin->organization->id ?? null)->get();
            $adminTypes = [
                'org_admin' => 'Organization Admin',
                'branch_admin' => 'Branch Admin'
            ];
        } else {
            $organizations = Organization::where('id', $admin->organization->id ?? null)->get();
            $branches = Branch::where('id', $admin->branch->id ?? null)->get();
            $adminTypes = [
                'branch_admin' => 'Branch Admin'
            ];
        }

        // Group permissions for better UI
        $permissionGroups = $this->groupPermissions($availablePermissions);
        $availableTemplates = [];

        return view('admin.users.create', compact(
            'organizations',
            'branches',
            'allBranches',
            'roles',
            'adminTypes',
            'availablePermissions',
            'permissionGroups',
            'permissionDefinitions',
            'availableTemplates'
        ));
    }

    /**
     * Group permissions by category for UI display
     */
    private function groupPermissions($permissions)
    {
        $groups = [];

        foreach ($permissions as $permission => $description) {
            $category = explode('.', $permission)[0];
            $categoryName = ucwords(str_replace('_', ' ', $category)) . ' Management';

            if (!isset($groups[$categoryName])) {
                $groups[$categoryName] = [
                    'title' => $categoryName,
                    'permissions' => []
                ];
            }

            $groups[$categoryName]['permissions'][$permission] = $description;
        }

        return $groups;
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();
        $this->authorize('create', Admin::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::where('guard_name', 'admin')->findOrFail($request->input('role_id'));
        $admin = auth('admin')->user();

        // Validate role assignment permissions
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        $rolePermissions = $role->permissions()->pluck('name')->toArray();
        foreach ($rolePermissions as $perm) {
            if (!isset($availablePermissions[$perm])) {
                return back()->withErrors(['role_id' => 'You cannot assign a role with permissions outside your plan/modules.']);
            }
        }

        // Validate permission assignments
        $requestedPermissions = collect();
        if ($request->has('permissions')) {
            $requestedPermissions = Permission::whereIn('id', $request->input('permissions'))
                ->where('guard_name', 'admin')
                ->get();
            foreach ($requestedPermissions as $permission) {
                if (!isset($availablePermissions[$permission->name])) {
                    return back()->withErrors([
                        'permissions' => "You cannot assign permission: {$permission->name}"
                    ]);
                }
            }
        }

        // Create the admin user
        $adminUser = Admin::create([
            'organization_id' => $request->input('organization_id') ?? Auth::guard('admin')->user()->organization_id,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone_number' => $request->input('phone_number'),
            'password' => Hash::make($request->input('password')),
            'branch_id' => $request->input('branch_id'),
            'created_by' => Auth::guard('admin')->user()->id,
            'is_active' => true,
        ]);

        // Assign role to the admin (using Spatie)
        $adminUser->syncRoles([$role->name]);

        // Sync all permissions from the role to the admin
        $adminUser->syncPermissions($role->permissions()->pluck('name')->toArray());

        // Assign additional permissions if specified (additive)
        if ($requestedPermissions->count()) {
            $adminUser->givePermissionTo($requestedPermissions->pluck('name')->toArray());
        }

        Log::info('Admin created with role and permissions', [
            'admin_id' => $adminUser->id,
            'role' => $role->name,
            'permissions' => $adminUser->getPermissionNames()->toArray() ?? [],
            'created_by' => Auth::guard('admin')->user()->id
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Admin created successfully with assigned role and permissions.');
    }

    // Show form to edit a user
    public function edit(Admin $admin)
    {
        $this->authorize('update', $admin);
        $adminUser = auth('admin')->user();
        $admin->load(['roles', 'organization', 'branch']);

        $organizations = collect();
        $branches = collect();
        $allBranches = collect();
        $roles = collect();

        if ($adminUser->isSuperAdmin()) {
            $organizations = Organization::all();
            $allBranches = Branch::with('organization')->get();
            $branches = $allBranches;
            $roles = Role::where('guard_name', 'admin')->get();
        } elseif ($adminUser->isOrganizationAdmin()) {
            $organizations = Organization::where('id', $adminUser->organization->id ?? null)->get();
            $branches = Branch::where('organization_id', $adminUser->organization->id ?? null)->get();
            $allBranches = $branches;
            $roles = Role::where('organization_id', $adminUser->organization->id ?? null)->where('guard_name', 'admin')->get();
        } else {
            $organizations = Organization::where('id', $adminUser->organization->id ?? null)->get();
            $branches = Branch::where('id', $adminUser->branch->id ?? null)->get();
            $allBranches = $branches;
            $roles = Role::where('branch_id', $adminUser->branch->id ?? null)->where('guard_name', 'admin')->get();
        }

        return view('admin.users.edit', compact(
            'admin',
            'organizations',
            'branches',
            'allBranches',
            'roles'
        ));
    }

    // Update a user
    public function update(Request $request, Admin $admin)
    {
        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'branch_id' => $request->input('branch_id'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $admin->update($data);

        // Always sync role and permissions
        $role = null;
        $roleId = $request->input('role_id');
        if (!empty($roleId)) {
            $role = Role::where('guard_name', 'admin')->find($roleId);
            if ($role) {
                // Use assignRole for single role assignment (Spatie)
                $admin->roles()->detach();
                $admin->assignRole($role);
            }
        }

        // Get role permissions (by name)
        $rolePermissions = $role ? $role->permissions()->pluck('name')->toArray() : [];

        // Get custom permissions from request (by id, convert to name)
        $customPermissions = [];
        $permissionIds = $request->input('permissions', []);
        if (is_array($permissionIds) && count($permissionIds) > 0) {
            $customPermissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
        }

        // Merge and sync all permissions (by name)
        $allPermissions = array_unique(array_merge($rolePermissions, $customPermissions));
        $admin->syncPermissions($allPermissions);

        return redirect()->route('admin.users.index')->with('success', 'Admin updated successfully with assigned role and permissions.');
    }

    // Show form to assign/change a role for an admin
    public function assignRoleForm(Admin $admin)
    {
        $roles = Role::where('guard_name', 'admin')->get();
        $allPermissions = Permission::where('guard_name', 'admin')->get();
        return view('admin.users.assign-role', compact('admin', 'roles', 'allPermissions'));
    }

    // Store assigned role and permissions for an admin
    public function assignRoleStore(Request $request, Admin $admin)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $roleId = $request->input('role_id');
        $role = Role::where('guard_name', 'admin')->findOrFail($roleId);
        $admin->syncRoles([$role]);

        // Get role permissions (by name)
        $rolePermissions = $role->permissions()->pluck('name')->toArray();

        // Get custom permissions from request (by id, convert to name)
        $customPermissions = [];
        $permissionIds = $request->input('permissions', []);
        if (is_array($permissionIds) && count($permissionIds) > 0) {
            $customPermissions =Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
        }

        // Merge and sync all permissions (by name)
        $allPermissions = array_unique(array_merge($rolePermissions, $customPermissions));
        $admin->syncPermissions($allPermissions);

    return redirect()->route('admin.users.index')->with('success', 'Role and permissions assigned successfully.');
    }

    // Delete an admin
    public function destroy(Admin $admin)
    {
        $this->authorize('delete', $admin);
        $currentAdmin = Auth::guard('admin')->user();
        if ($currentAdmin && $currentAdmin->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account');
        }

        $admin->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }


    // All User model methods removed. Only Admin management remains.
    
public function show(Admin $admin)
{
    $admin->load(['roles', 'organization', 'branch']);
    $permissions = $admin->getFormattedPermissions(); // Get formatted permissions

    return view('admin.users.summary', compact('admin', 'permissions'));
}
}


