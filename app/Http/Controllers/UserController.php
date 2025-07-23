<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            $admins = \App\Models\Admin::with(['roles', 'organization', 'branch'])->get();
        } elseif ($admin->isOrganizationAdmin()) {
            $admins = \App\Models\Admin::where('organization_id', $admin->organization_id)
                ->with(['roles', 'organization', 'branch'])
                ->get();
        } else {
            $admins = \App\Models\Admin::where('branch_id', $admin->branch_id)
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
            ->where('guard_name', 'admin'); // Use 'admin' guard for system users

        if ($admin->isSuperAdmin()) {
            // all roles for admin guard
        } elseif ($admin->isOrganizationAdmin()) {
            $rolesQuery->where('organization_id', $admin->organization_id);
        } else {
            $rolesQuery->where('branch_id', $admin->branch_id);
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
            $organizations = Organization::where('id', $admin->organization_id)->get();
            $branches = Branch::where('organization_id', $admin->organization_id)->get();
            $adminTypes = [
                'org_admin' => 'Organization Admin',
                'branch_admin' => 'Branch Admin'
            ];
        } else {
            $organizations = Organization::where('id', $admin->organization_id)->get();
            $branches = Branch::where('id', $admin->branch_id)->get();
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
        $this->authorize('create', \App\Models\Admin::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::where('guard_name', 'admin')->findOrFail($request->role_id);
        $admin = auth('admin')->user();

        // Validate role assignment permissions
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        foreach ($rolePermissions as $perm) {
            if (!isset($availablePermissions[$perm])) {
                return back()->withErrors(['role_id' => 'You cannot assign a role with permissions outside your plan/modules.']);
            }
        }

        // Validate permission assignments
        $requestedPermissions = collect();
        if ($request->permissions) {
            $requestedPermissions = Permission::whereIn('id', $request->permissions)
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
        $adminUser = \App\Models\Admin::create([
            'organization_id' => $request->organization_id ?? $admin->organization_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'branch_id' => $request->branch_id,
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        // Assign role to the admin (using Spatie)
        $adminUser->assignRole($role);

        // Assign additional permissions if specified
        if ($requestedPermissions->count()) {
            $adminUser->givePermissionTo($requestedPermissions->pluck('name')->toArray());
        }

        Log::info('Admin created with role and permissions', [
            'admin_id' => $adminUser->id,
            'role' => $role->name,
            'permissions' => $requestedPermissions->pluck('name')->toArray() ?? [],
            'created_by' => $admin->id
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Admin created successfully with assigned role and permissions.');
    }

    // Show form to edit a user
    public function edit(\App\Models\Admin $admin)
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
            $organizations = Organization::where('id', $adminUser->organization_id)->get();
            $branches = Branch::where('organization_id', $adminUser->organization_id)->get();
            $allBranches = $branches;
            $roles = Role::where('organization_id', $adminUser->organization_id)->where('guard_name', 'admin')->get();
        } else {
            $organizations = Organization::where('id', $adminUser->organization_id)->get();
            $branches = Branch::where('id', $adminUser->branch_id)->get();
            $allBranches = $branches;
            $roles = Role::where('branch_id', $adminUser->branch_id)->where('guard_name', 'admin')->get();
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
    public function update(Request $request, \App\Models\Admin $admin)
    {
        $this->authorize('update', $admin);
        $currentAdmin = Auth::guard('admin')->user();


        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(function ($query) use ($currentAdmin) {
                    if (!$currentAdmin->is_super_admin && $currentAdmin->organization_id) {
                        $query->where('organization_id', $currentAdmin->organization_id);
                    }
                }),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'branch_id' => $request->branch_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        // Assign role using Spatie's pivot table for Admin model
        if ($request->filled('role_id')) {
            $role = Role::where('guard_name', 'admin')->find($request->role_id);
            if ($role) {
                $admin->roles()->sync([$role->id]);
            }
        }
        // Reload admin with roles for edit and index views
        $admin->load(['roles']);

        return redirect()->route('admin.users.index')->with('success', 'Admin updated successfully');
    }

    // Show form to assign/change a role for an admin
    public function assignRoleForm(\App\Models\Admin $admin)
    {
        $this->authorize('assignRole', $admin);
        $currentAdmin = Auth::guard('admin')->user();
        $rolesQuery = Role::query();
        if (!$currentAdmin->is_super_admin && $currentAdmin->organization_id) {
            $rolesQuery->where('organization_id', $currentAdmin->organization_id);
        }
        $roles = $rolesQuery->get();
        return view('admin.users.assign-role', compact('admin', 'roles'));
    }

    // Assign/change a role for an admin
    public function assignRole(Request $request, \App\Models\Admin $admin)
    {
        $this->authorize('assignRole', $admin);
        $currentAdmin = Auth::guard('admin')->user();
        $request->validate([
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(function ($query) use ($currentAdmin) {
                    if (!$currentAdmin->is_super_admin && $currentAdmin->organization_id) {
                        $query->where('organization_id', $currentAdmin->organization_id);
                    }
                })
            ],
        ]);
        $role = Role::find($request->role_id);
        if ($role) {
            $admin->roles()->sync([$role->id]);
        }
        return redirect()->route('admin.users.index')->with('success', 'Role assigned successfully');
    }

    // Delete an admin
    public function destroy(\App\Models\Admin $admin)
    {
        $this->authorize('delete', $admin);
        if (optional(Auth::guard('admin')->user())->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account');
        }

        $admin->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }


    // All User model methods removed. Only Admin management remains.

    public function show(\App\Models\Admin $admin)
    {
        $admin->load(['roles', 'organization', 'branch']);
        return view('admin.users.summary', compact('admin'));
    }

}


