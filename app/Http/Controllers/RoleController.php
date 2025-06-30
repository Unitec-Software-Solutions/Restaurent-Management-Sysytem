<?php

namespace App\Http\Controllers;


use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Organization;
use App\Models\User;
use App\Models\Module;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $organizations = \App\Models\Organization::with('branches')->get();

        $selectedOrgId = $request->query('organization_id');
        $selectedBranchId = $request->query('branch_id');

        $rolesQuery = \App\Models\Role::query();

        if ($selectedBranchId) {
            $rolesQuery->where('branch_id', $selectedBranchId);
        } elseif ($selectedOrgId) {
            $rolesQuery->where('organization_id', $selectedOrgId)->whereNull('branch_id');
        }

        $roles = $rolesQuery->with('organization', 'branch')->get();

        return view('admin.roles.index', compact('organizations', 'roles', 'selectedOrgId', 'selectedBranchId'));
    }

    public function create(Request $request)
    {
        $admin = auth('admin')->user();

        if ($admin->is_super_admin) {
            // Super admin: can select any organization and branch
            $organizations = \App\Models\Organization::with('branches')->get();
            $branches = \App\Models\Branch::all();
        } elseif ($admin->admin) {
            // Organization admin: only their org and its branches
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->with('branches')->get();
            $branches = \App\Models\Branch::where('organization_id', $admin->organization_id)->get();
        } else {
            // Branch admin: only their branch
            $organizations = \App\Models\Organization::where('id', $admin->organization_id)->with('branches')->get();
            $branches = \App\Models\Branch::where('id', $admin->branch_id)->get();
        }

        $modules = \App\Models\Module::all();

        return view('admin.roles.create', compact('organizations', 'branches', 'modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles')->where(function ($query) use ($request) {
                    return $query->where('guard_name', 'admin')
                                 ->where('organization_id', $request->organization_id);
                }),
            ],
            'branch_id' => 'nullable|exists:branches,id',
            'organization_id' => 'required|exists:organizations,id',
            'permissions' => 'array',
            'permissions.*' => 'string'
        ]);

        $role = \App\Models\Role::create([
            'name' => $request->name,
            'branch_id' => $request->branch_id,
            'organization_id' => $request->organization_id,
            'guard_name' => 'admin',
        ]);

        // Sync permissions (by name)
        $permissionIds = \App\Models\Permission::whereIn('name', $request->permissions ?? [])->pluck('id')->toArray();
        $role->permissions()->sync($permissionIds);

        return redirect()->route('admin.roles.index')->with('success', 'Role created for organization.');
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('update', $role);
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'string'
        ]);
        $role->update($request->only('name'));

        // Sync permissions (by name)
        $permissionIds = \App\Models\Permission::whereIn('name', $request->permissions ?? [])->pluck('id')->toArray();
        $role->permissions()->sync($permissionIds);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('delete', $role);
        $role->delete();
        return response()->noContent();
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
    public function edit(\App\Models\Role $role)
    {
        $modules = \App\Models\Module::all();
        $branches = \App\Models\Branch::where('organization_id', $role->organization_id)
            ->where('is_active', true)
            ->get();

        // Add this line to get organizations
        $organizations = \App\Models\Organization::all();

        return view('admin.roles.edit', compact('role', 'modules', 'branches', 'organizations'));
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
