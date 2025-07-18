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
        $admin = Auth::guard('admin')->user();

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

        return view('admin.users.index', ['users' => $admins]);
    }

    // Show form to create a new user
    public function create(Request $request)
    {
        $this->authorize('create', User::class);

        $admin = auth('admin')->user();

        // Get permission definitions and role templates
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $roleTemplates = $this->permissionService->getRoleTemplates();
        $availableTemplates = $this->permissionService->filterTemplatesByScope($roleTemplates, $admin);
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
        // Allow super admin to create and manage all admin users
        $admin = auth('admin')->user();
        if (!$admin) {
            abort(403, 'You do not have permission to create admin users.');
        }
        // Only restrict non-super admins
        if (!$admin->is_super_admin && !$admin->can('create', \App\Models\Admin::class)) {
            abort(403, 'You do not have permission to create admin users.');
        }

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
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $admin = Auth::guard('admin')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(function ($query) use ($admin) {
                    if (!$admin->is_super_admin && $admin->organization_id) {
                        $query->where('organization_id', $admin->organization_id);
                    }
                })
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

        $user->update($data);

        // Assign role using Spatie's pivot table
        if ($request->filled('role_id')) {
            $role = Role::where('guard_name', 'admin')->find($request->role_id);
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }
        // Reload user with roles for edit and index views
        $user->load(['roles']);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    // Show form to assign/change a role for a user
    public function assignRoleForm(User $user)
    {
        $this->authorize('assignRole', $user);

        $admin = Auth::guard('admin')->user();
        $roles = Role::query();

        if (!$admin->is_super_admin && $admin->organization_id) {
            $roles->where('organization_id', $admin->organization_id);
        }

        $roles = $roles->get();

        return view('admin.users.assign-role', compact('user', 'roles'));
    }

    // Assign/change a role for a user
    public function assignRole(Request $request, User $user)
    {
        $this->authorize('assignRole', $user);

        $admin = Auth::guard('admin')->user();

        $request->validate([
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(function ($query) use ($admin) {
                    if (!$admin->is_super_admin && $admin->organization_id) {
                        $query->where('organization_id', $admin->organization_id);
                    }
                })
            ],
        ]);

        $user->update(['role_id' => $request->role_id]);

        return redirect()->route('admin.users.index')->with('success', 'Role assigned successfully');
    }

    // Delete a user
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if (optional(Auth::guard('admin')->user())->id === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }

    public function invite(Request $request, Organization $organization)
    {
        Gate::authorize('invite', [User::class, $organization]);
        $request->validate([
            'email' => 'required|email',
            'branch_id' => 'nullable|exists:branches,id,organization_id,'.$organization->id,
            'role_id' => 'required|exists:roles,id,organization_id,'.$organization->id
        ]);
        $invitationToken = Str::random(40);
        $user = $organization->users()->create([
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'role_id' => $request->role_id,
            'invitation_token' => $invitationToken,
            'password' => Hash::make(Str::random(24)),
        ]);
        Mail::to($request->email)->send(new UserInvitation($user));
        return response()->json(['message' => 'Invitation sent']);
    }

    public function completeRegistration(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed'
        ]);
        $user = User::where('invitation_token', $request->token)
                    ->whereNull('email_verified_at')
                    ->firstOrFail();
        $user->update([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'invitation_token' => null
        ]);
        return response()->json(['message' => 'Registration completed']);
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('updateRole', $user);
        $request->validate([
            'role_id' => 'required|exists:roles,id,organization_id,'.$user->organization_id
        ]);
        $user->update(['role_id' => $request->role_id]);
        return response()->json($user);
    }

    public function deactivate(User $user)
    {
        Gate::authorize('deactivate', $user);
        $user->update(['is_active' => false]);
        return response()->json(['message' => 'User deactivated']);
    }

    public function hasPermission($permission)
    {
        $user = Auth::guard('admin')->user();
        if (!$user || !is_object($user->role)) return false;
        return optional($user->role->permissions)->pluck('name')->contains($permission);
    }

    public function show(\App\Models\Admin $admin)
    {
        $admin->load(['roles', 'organization', 'branch', 'creator']);
        return view('admin.users.summary', compact('admin'));
    }
}
