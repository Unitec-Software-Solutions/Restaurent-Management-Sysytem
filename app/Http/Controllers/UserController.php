<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionSystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $admin = auth('admin')->user();

        if ($admin->is_super_admin) {
            // Super admin: see all users (excluding guests when column exists)
            $query = User::with([
                'userRole', 
                'branch.organization', 
                'creator', 
                'organization', 
                'roles',
                'permissions'
            ]);
            
            // Only filter by is_guest if the column exists
            if (Schema::hasColumn('users', 'is_guest')) {
                $query->where('is_guest', false);
            }
            
            $users = $query->orderBy('created_at', 'desc')->paginate(20);
        } elseif ($admin->isOrganizationAdmin()) {
            // Org admin: see all users in their organization (excluding guests when column exists)
            $query = User::where('organization_id', $admin->organization_id)
                ->with([
                    'userRole', 
                    'branch.organization', 
                    'creator', 
                    'organization', 
                    'roles',
                    'permissions'
                ]);
                
            // Only filter by is_guest if the column exists
            if (Schema::hasColumn('users', 'is_guest')) {
                $query->where('is_guest', false);
            }
            
            $users = $query->orderBy('created_at', 'desc')->paginate(20);
        } else {
            // Branch admin: see all users in their branch (excluding guests when column exists)
            $query = User::where('branch_id', $admin->branch_id)
                ->with([
                    'userRole', 
                    'branch.organization', 
                    'creator', 
                    'organization', 
                    'roles',
                    'permissions'
                ]);
                
            // Only filter by is_guest if the column exists
            if (Schema::hasColumn('users', 'is_guest')) {
                $query->where('is_guest', false);
            }
            
            $users = $query->orderBy('created_at', 'desc')->paginate(20);
        }

        return view('admin.users.index', compact('users'));
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
        $rolesQuery = \Spatie\Permission\Models\Role::with('permissions')
            ->where('guard_name', 'web')
            ->whereHas('permissions', function($q) use ($availablePermissions) {
                $q->whereIn('name', array_keys($availablePermissions));
            });
        if ($admin->is_super_admin) {
            // all roles
        } elseif ($admin->isOrganizationAdmin()) {
            $rolesQuery->where('organization_id', $admin->organization_id);
        } else {
            $rolesQuery->where('branch_id', $admin->branch_id);
        }
        $roles = $rolesQuery->get();

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
        $this->authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = \Spatie\Permission\Models\Role::where('guard_name', 'web')->findOrFail($request->role_id);
        $admin = auth('admin')->user();

        // Validate role assignment permissions (must be in available roles)
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
            $requestedPermissions = \Spatie\Permission\Models\Permission::whereIn('id', $request->permissions)
                ->where('guard_name', 'web')
                ->get();
            foreach ($requestedPermissions as $permission) {
                if (!isset($availablePermissions[$permission->name])) {
                    return back()->withErrors([
                        'permissions' => "You cannot assign permission: {$permission->name}"
                    ]);
                }
            }
        }

        // Create the user
        $user = User::create([
            'organization_id' => $request->organization_id ?? $admin->organization_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'branch_id' => $request->branch_id,
            'role_id' => $request->role_id,
            'created_by' => $admin->id,
        ]);

        // Assign role to the user (using Spatie)
        $user->assignRole($role);

        // Assign additional permissions if specified
        if ($requestedPermissions->count()) {
            $user->givePermissionTo($requestedPermissions->pluck('name')->toArray());
        }

        Log::info('User created with role and permissions', [
            'user_id' => $user->id,
            'role' => $role->name,
            'permissions' => $requestedPermissions->pluck('name')->toArray() ?? [],
            'created_by' => $admin->id
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully with assigned role and permissions.');
    }

    // Show form to edit a user
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $admin = auth('admin')->user();

        $organizations = collect();
        $branches = collect();
        $allBranches = collect();
        $roles = collect();

        if ($admin->isSuperAdmin()) {
            $organizations = \App\Models\Organization::all();
            $allBranches = \App\Models\Branch::with('organization')->get();
            $branches = $allBranches;
            $roles = \App\Models\Role::all();
        } elseif ($admin->isOrganizationAdmin()) {
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->get();
            $branches = \App\Models\Branch::where('organization_id', $admin->organization_id)->get();
            $allBranches = $branches;
            $roles = \App\Models\Role::where('organization_id', $admin->organization_id)->get();
        } else {
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->get();
            $branches = \App\Models\Branch::where('id', $admin->branch_id)->get();
            $allBranches = $branches;
            $roles = \App\Models\Role::where('branch_id', $admin->branch_id)->get();
        }

        return view('admin.users.edit', compact(
            'user',
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

        return redirect()->route('users.index')->with('success', 'User updated successfully');
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

        return redirect()->route('users.index')->with('success', 'Role assigned successfully');
    }

    // Delete a user
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if (optional(Auth::guard('admin')->user())->id === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully');
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

    public function show(User $user)
    {
        $user->load(['creator']);
        return view('admin.users.summary', compact('user'));
    }
}
