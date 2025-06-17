<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Organization;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;

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

    public function create()
    {
        $this->authorize('create', \App\Models\Role::class);

        $modules = \App\Models\Module::all();
        $branches = \App\Models\Branch::where('organization_id', Auth::user()->organization_id)
            ->where('is_active', true)
            ->get();
        $organizations = \App\Models\Organization::all(); // <-- Add this line

        return view('admin.roles.create', compact('modules', 'branches', 'organizations'));
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
            'modules' => 'array|exists:modules,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $role = \App\Models\Role::create([
            'name' => $request->name,
            'branch_id' => $request->branch_id,
            'organization_id' => $request->organization_id,
            'guard_name' => 'admin',
        ]);

        // Sync modules
        $role->modules()->sync($request->modules);

        // Get all permissions for the selected modules (as strings)
        $permissionNames = \App\Models\Module::whereIn('id', $request->modules)
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique()
            ->toArray();

        $permissionIds = \App\Models\Permission::whereIn('name', $permissionNames)->pluck('id')->toArray();

        // Sync permissions to the role
        $role->permissions()->sync($permissionIds);

        return redirect()->route('admin.roles.index')->with('success', 'Role created for organization.');
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('update', $role);
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'modules' => 'array|exists:modules,id'
        ]);
        $role->update($request->only('name'));
        if ($request->has('modules')) {
            $role->modules()->sync($request->modules);
        }
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

        return redirect()->route('users.show', $userId)->with('success', 'Role assigned successfully.');
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
    public function showAssignModulesForm(Role $role)
    {
        $this->authorize('assign_modules', $role);
        $modules = Module::with('permissions')->get();
        return view('admin.roles.assign-modules', compact('role', 'modules'));
    }
    public function editPermissions(Role $role)
    {
        $modules = Module::with('permissions')->get();
        return view('admin.roles.permissions', compact('role', 'modules'));
    }

    public function permissions(\App\Models\Role $role)
    {
        $modules = \App\Models\Module::all();
        return view('admin.roles.permissions', compact('role', 'modules'));
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

        return view('admin.roles.edit', compact('role', 'modules', 'branches'));
    }
}
