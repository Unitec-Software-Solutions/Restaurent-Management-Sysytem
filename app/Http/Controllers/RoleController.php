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
    public function index(Organization $organization)
    {
        Gate::authorize('viewAny', [Role::class, $organization]);
        return $organization->roles()->with('modules')->get();
    }

    public function store(Request $request, Organization $organization)
    {
        Gate::authorize('create', [Role::class, $organization]);
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id,organization_id,'.$organization->id,
            'modules' => 'array|exists:modules,id'
        ]);
        $role = $organization->roles()->create([
            'name' => $request->name,
            'branch_id' => $request->branch_id
        ]);
        $role->modules()->sync($request->modules);
        return response()->json($role->load('modules'), 201);
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
        return response()->json($role->load('modules'));
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

    public function updatePermissions(Request $request, Role $role)
    {
        $role->syncPermissions($request->input('permissions', []));
        return redirect()->route('admin.roles.index')
            ->with('success', 'Permissions updated successfully');
    }
}
