<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $admin = auth('admin')->user();

        if ($admin->is_super_admin) {
            // Super admin: see all users
            $users = User::with(['userRole', 'branch', 'creator', 'organization'])->paginate(20);
        } elseif ($admin->isOrganizationAdmin()) {
            // Org admin: see all users in their organization
            $users = User::where('organization_id', $admin->organization_id)
                ->with(['userRole', 'branch', 'creator', 'organization'])
                ->paginate(20);
        } else {
            // Branch admin: see all users in their branch
            $users = User::where('branch_id', $admin->branch_id)
                ->with(['userRole', 'branch', 'creator', 'organization'])
                ->paginate(20);
        }

        return view('admin.users.index', compact('users'));
    }

    // Show form to create a new user
    public function create(Request $request)
    {
        $this->authorize('create', User::class);

        $organizations = collect();
        $branches = collect();
        $allBranches = collect();
        $roles = collect();
        $adminTypes = [];

        $admin = auth('admin')->user();

        if ($admin->is_super_admin) {
            $organizations = Organization::all();
            $allBranches = Branch::with('organization')->get();
            $branches = $allBranches;
            $roles = Role::all();
            $adminTypes = [
                'org_admin' => 'Organization Admin',
                'branch_admin' => 'Branch Admin'
            ];

        } elseif ($admin->isOrganizationAdmin()) {
            $organizations = Organization::where('id', $admin->organization_id)->get();
            $branches = Branch::where('organization_id', $admin->organization_id)->get();
            $roles = Role::where('organization_id', $admin->organization_id)->get();
            $adminTypes = [
                'org_admin' => 'Organization Admin',
                'branch_admin' => 'Branch Admin'
            ];
        } else {
            $organizations = Organization::where('id', $admin->organization_id)->get();
            $branches = Branch::where('id', $admin->branch_id)->get();
            $roles = Role::where('branch_id', $admin->branch_id)->get();
            $adminTypes = [
                'branch_admin' => 'Branch Admin'
            ];
        }

        return view('admin.users.create', compact(
            'organizations',
            'branches',
            'allBranches',
            'roles',
            'adminTypes'
        ));
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
        ]);

        $role = Role::findOrFail($request->role_id);

      

        // Example mapping logic (customize as needed)
        if ($role->name === 'Super Admin') {
            $userType = 'super_admin';
        } elseif ($role->name === 'Organization Admin') {
            $userType = 'org_admin';
        } elseif ($role->name === 'Branch Admin') {
            $userType = 'branch_admin';
        }

        $user = User::create([
            'organization_id' => $request->organization_id ?? auth('admin')->user()->organization_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'branch_id' => $request->branch_id,
            'role_id' => $request->role_id,
            'created_by' => auth('admin')->id(),
        ]);

        Log::info('User created', $user->toArray());

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
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
