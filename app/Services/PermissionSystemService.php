<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class PermissionSystemService
{
    public function installScopedPermissions()
    {
        DB::transaction(function () {
            $this->createScopedRoles();
            $this->assignScopedPermissions();
            $this->setupPermissionCascade();
        });
    }

    private function createScopedRoles()
    {
        $roleStructure = [
            // Organization Level
            'org_admin' => [
                'name' => 'Organization Administrator',
                'scope' => 'organization',
                'permissions' => [
                    'organization.view',
                    'organization.edit',
                    'branches.manage',
                    'users.manage',
                    'subscription.manage',
                    'reports.view_all'
                ]
            ],
            
            // Branch Level
            'branch_admin' => [
                'name' => 'Branch Administrator',
                'scope' => 'branch',
                'permissions' => [
                    'branch.view',
                    'branch.edit',
                    'staff.manage',
                    'inventory.manage',
                    'orders.manage',
                    'reports.view_branch'
                ]
            ],
            
            // Staff Level - Task-specific
            'shift_manager' => [
                'name' => 'Shift Manager',
                'scope' => 'branch',
                'permissions' => [
                    'orders.view',
                    'orders.edit',
                    'staff.assign_tasks',
                    'inventory.view',
                    'reports.view_shift'
                ]
            ],
            
            'cashier' => [
                'name' => 'Cashier',
                'scope' => 'branch',
                'permissions' => [
                    'orders.create',
                    'orders.payment',
                    'reports.view_sales'
                ]
            ],
            
            'waiter' => [
                'name' => 'Waiter/Waitress',
                'scope' => 'branch',
                'permissions' => [
                    'orders.create',
                    'orders.view_assigned',
                    'menu.view',
                    'tables.manage'
                ]
            ],
            
            'kitchen_staff' => [
                'name' => 'Kitchen Staff',
                'scope' => 'branch',
                'permissions' => [
                    'kitchen.view_orders',
                    'kitchen.update_status',
                    'inventory.view',
                    'inventory.update_usage'
                ]
            ]
        ];

        foreach ($roleStructure as $key => $roleData) {
            $this->createRoleWithPermissions($key, $roleData);
        }
    }

    private function createRoleWithPermissions(string $key, array $roleData)
    {
        // Create permissions first
        foreach ($roleData['permissions'] as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ], [
                'display_name' => ucwords(str_replace(['.', '_'], ' ', $permissionName)),
                'scope' => $roleData['scope']
            ]);
        }

        // Create role for each organization/branch as needed
        if ($roleData['scope'] === 'organization') {
            $this->createOrganizationRoles($key, $roleData);
        } elseif ($roleData['scope'] === 'branch') {
            $this->createBranchRoles($key, $roleData);
        }
    }

    private function createOrganizationRoles(string $key, array $roleData)
    {
        Organization::each(function ($org) use ($key, $roleData) {
            $role = Role::firstOrCreate([
                'name' => $key,
                'organization_id' => $org->id,
                'guard_name' => 'web'
            ], [
                'display_name' => $roleData['name'],
                'scope' => 'organization'
            ]);

            $role->syncPermissions($roleData['permissions']);
        });
    }

    private function createBranchRoles(string $key, array $roleData)
    {
        Branch::each(function ($branch) use ($key, $roleData) {
            $role = Role::firstOrCreate([
                'name' => $key,
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'guard_name' => 'web'
            ], [
                'display_name' => $roleData['name'],
                'scope' => 'branch'
            ]);

            $role->syncPermissions($roleData['permissions']);
        });
    }

    private function assignScopedPermissions()
    {
        // Implement permission inheritance logic
        $this->setupPermissionInheritance();
    }

    private function setupPermissionCascade()
    {
        // Create permission cascade rules
        // OrgAdmin can access all branches
        // BranchAdmin can only access their branch
        // Staff can only access assigned tasks
    }

    private function setupPermissionInheritance()
    {
        // Organization admins inherit all branch permissions for their org
        $orgRoles = Role::where('scope', 'organization')->get();
        
        foreach ($orgRoles as $orgRole) {
            $branchPermissions = Permission::whereIn('name', [
                'branch.view', 'staff.manage', 'inventory.manage', 'orders.manage'
            ])->get();
            
            $orgRole->givePermissionTo($branchPermissions);
        }
    }

    public function validateUserAccess($user, $resource, $action)
    {
        // Check if user has permission for specific action on resource
        $permission = "{$resource}.{$action}";
        
        if ($user->can($permission)) {
            return $this->validateScope($user, $resource);
        }
        
        return false;
    }

    private function validateScope($user, $resource)
    {
        // Implement scope validation logic
        // Ensure users can only access resources within their scope
        return true;
    }
}