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

    /**
     * Get all permission definitions organized by categories
     */
    public function getPermissionDefinitions(): array
    {
        return [
            // 1. ORGANIZATION MANAGEMENT
            'organizations' => [
                'name' => 'Organization Management',
                'icon' => 'building',
                'description' => 'Manage organizations and their settings',
                'permissions' => [
                    'organizations.view' => 'View organizations',
                    'organizations.create' => 'Create organizations',
                    'organizations.edit' => 'Edit organization details',
                    'organizations.delete' => 'Delete organizations',
                    'organizations.activate' => 'Activate/deactivate organizations',
                    'organizations.manage' => 'Full organization management',
                    'organizations.settings' => 'Manage organization settings',
                    'organizations.subscription' => 'Manage organization subscription',
                ]
            ],

            // 2. BRANCH MANAGEMENT
            'branches' => [
                'name' => 'Branch Management',
                'icon' => 'store',
                'description' => 'Manage branches and locations',
                'permissions' => [
                    'branches.view' => 'View branches',
                    'branches.create' => 'Create new branches',
                    'branches.edit' => 'Edit branch details',
                    'branches.delete' => 'Delete branches',
                    'branches.activate' => 'Activate/deactivate branches',
                    'branches.manage' => 'Full branch management',
                    'branches.settings' => 'Manage branch settings',
                    'branches.reports' => 'View branch reports',
                ]
            ],

            // 3. USER & ADMIN MANAGEMENT
            'users' => [
                'name' => 'User Management',
                'icon' => 'users',
                'description' => 'Manage users and administrators',
                'permissions' => [
                    'users.view' => 'View users',
                    'users.create' => 'Create users',
                    'users.edit' => 'Edit user details',
                    'users.delete' => 'Delete users',
                    'users.activate' => 'Activate/deactivate users',
                    'users.manage' => 'Full user management',
                    'users.permissions' => 'Manage user permissions',
                    'users.roles' => 'Assign user roles',
                ]
            ],

            // 4. ROLES & PERMISSIONS
            'roles' => [
                'name' => 'Roles & Permissions',
                'icon' => 'shield-check',
                'description' => 'Manage roles and permissions',
                'permissions' => [
                    'roles.view' => 'View roles',
                    'roles.create' => 'Create roles',
                    'roles.edit' => 'Edit roles',
                    'roles.delete' => 'Delete roles',
                    'roles.manage' => 'Full role management',
                    'roles.assign' => 'Assign roles to users',
                    'permissions.view' => 'View permissions',
                    'permissions.assign' => 'Assign permissions',
                ]
            ],

            // 5. MENU MANAGEMENT
            'menus' => [
                'name' => 'Menu Management',
                'icon' => 'book-open',
                'description' => 'Manage menus and menu items',
                'permissions' => [
                    'menus.view' => 'View menus',
                    'menus.create' => 'Create menus',
                    'menus.edit' => 'Edit menus',
                    'menus.delete' => 'Delete menus',
                    'menus.activate' => 'Activate/deactivate menus',
                    'menus.manage' => 'Full menu management',
                    'menus.categories' => 'Manage menu categories',
                    'menus.pricing' => 'Manage menu pricing',
                ]
            ],

            // 6. ORDER MANAGEMENT
            'orders' => [
                'name' => 'Order Management',
                'icon' => 'shopping-cart',
                'description' => 'Manage customer orders',
                'permissions' => [
                    'orders.view' => 'View orders',
                    'orders.create' => 'Create orders',
                    'orders.edit' => 'Edit orders',
                    'orders.delete' => 'Delete orders',
                    'orders.process' => 'Process orders',
                    'orders.cancel' => 'Cancel orders',
                    'orders.refund' => 'Process refunds',
                    'orders.manage' => 'Full order management',
                ]
            ],

            // 7. INVENTORY MANAGEMENT
            'inventory' => [
                'name' => 'Inventory Management',
                'icon' => 'box',
                'description' => 'Manage inventory and stock',
                'permissions' => [
                    'inventory.view' => 'View inventory',
                    'inventory.create' => 'Add inventory items',
                    'inventory.edit' => 'Edit inventory items',
                    'inventory.delete' => 'Delete inventory items',
                    'inventory.adjust' => 'Adjust stock levels',
                    'inventory.manage' => 'Full inventory management',
                    'inventory.alerts' => 'Manage inventory alerts',
                    'inventory.reports' => 'View inventory reports',
                ]
            ],

            // 8. SUPPLIER MANAGEMENT
            'suppliers' => [
                'name' => 'Supplier Management',
                'icon' => 'truck',
                'description' => 'Manage suppliers and procurement',
                'permissions' => [
                    'suppliers.view' => 'View suppliers',
                    'suppliers.create' => 'Create suppliers',
                    'suppliers.edit' => 'Edit suppliers',
                    'suppliers.delete' => 'Delete suppliers',
                    'suppliers.manage' => 'Full supplier management',
                    'suppliers.orders' => 'Manage supplier orders',
                    'suppliers.payments' => 'Manage supplier payments',
                    'suppliers.reports' => 'View supplier reports',
                ]
            ],

            // 9. RESERVATION MANAGEMENT
            'reservations' => [
                'name' => 'Reservation Management',
                'icon' => 'calendar',
                'description' => 'Manage table reservations',
                'permissions' => [
                    'reservations.view' => 'View reservations',
                    'reservations.create' => 'Create reservations',
                    'reservations.edit' => 'Edit reservations',
                    'reservations.delete' => 'Delete reservations',
                    'reservations.manage' => 'Full reservation management',
                    'reservations.confirm' => 'Confirm reservations',
                    'reservations.cancel' => 'Cancel reservations',
                    'reservations.reports' => 'View reservation reports',
                ]
            ],

            // 10. KITCHEN OPERATIONS
            'kitchen' => [
                'name' => 'Kitchen Operations',
                'icon' => 'chef-hat',
                'description' => 'Manage kitchen operations',
                'permissions' => [
                    'kitchen.view' => 'View kitchen operations',
                    'kitchen.manage' => 'Manage kitchen operations',
                    'kitchen.orders' => 'Manage kitchen orders',
                    'kitchen.stations' => 'Manage kitchen stations',
                    'kitchen.staff' => 'Manage kitchen staff',
                    'kitchen.reports' => 'View kitchen reports',
                    'kitchen.settings' => 'Manage kitchen settings',
                    'kitchen.inventory' => 'Manage kitchen inventory',
                ]
            ],

            // 11. REPORTS & ANALYTICS
            'reports' => [
                'name' => 'Reports & Analytics',
                'icon' => 'chart-bar',
                'description' => 'View and generate reports',
                'permissions' => [
                    'reports.view' => 'View reports',
                    'reports.create' => 'Create custom reports',
                    'reports.export' => 'Export reports',
                    'reports.manage' => 'Full report management',
                    'reports.sales' => 'View sales reports',
                    'reports.financial' => 'View financial reports',
                    'reports.inventory' => 'View inventory reports',
                    'reports.analytics' => 'View analytics dashboard',
                ]
            ],

            // 12. STAFF MANAGEMENT
            'staff' => [
                'name' => 'Staff Management',
                'icon' => 'user-group',
                'description' => 'Manage staff and employees',
                'permissions' => [
                    'staff.view' => 'View staff',
                    'staff.create' => 'Create staff accounts',
                    'staff.edit' => 'Edit staff details',
                    'staff.delete' => 'Delete staff accounts',
                    'staff.manage' => 'Full staff management',
                    'staff.schedules' => 'Manage staff schedules',
                    'staff.permissions' => 'Manage staff permissions',
                    'staff.payroll' => 'Manage staff payroll',
                ]
            ],

            // 13. SUBSCRIPTION MANAGEMENT
            'subscription' => [
                'name' => 'Subscription Management',
                'icon' => 'credit-card',
                'description' => 'Manage subscriptions and billing',
                'permissions' => [
                    'subscription.view' => 'View subscription details',
                    'subscription.edit' => 'Edit subscription',
                    'subscription.manage' => 'Full subscription management',
                    'subscription.billing' => 'Manage billing',
                    'subscription.plans' => 'Manage subscription plans',
                    'subscription.upgrade' => 'Upgrade/downgrade subscription',
                    'subscription.cancel' => 'Cancel subscription',
                    'subscription.reports' => 'View subscription reports',
                ]
            ],

            // 14. MODULE MANAGEMENT
            'modules' => [
                'name' => 'Module Management',
                'icon' => 'grid',
                'description' => 'Manage system modules',
                'permissions' => [
                    'modules.view' => 'View modules',
                    'modules.create' => 'Create modules',
                    'modules.edit' => 'Edit modules',
                    'modules.delete' => 'Delete modules',
                    'modules.manage' => 'Full module management',
                    'modules.activate' => 'Activate/deactivate modules',
                    'modules.configure' => 'Configure modules',
                    'modules.analytics' => 'View module analytics',
                ]
            ],

            // 15. SYSTEM SETTINGS
            'settings' => [
                'name' => 'System Settings',
                'icon' => 'cog',
                'description' => 'Manage system settings',
                'permissions' => [
                    'settings.view' => 'View system settings',
                    'settings.edit' => 'Edit system settings',
                    'settings.manage' => 'Full settings management',
                    'settings.security' => 'Manage security settings',
                    'settings.backup' => 'Manage system backups',
                    'settings.logs' => 'View system logs',
                    'settings.maintenance' => 'System maintenance',
                    'settings.integrations' => 'Manage integrations',
                ]
            ],

            // 16. PRODUCTION MANAGEMENT
            'production' => [
                'name' => 'Production Management',
                'icon' => 'factory',
                'description' => 'Manage production processes',
                'permissions' => [
                    'production.view' => 'View production',
                    'production.create' => 'Create production orders',
                    'production.edit' => 'Edit production orders',
                    'production.delete' => 'Delete production orders',
                    'production.manage' => 'Full production management',
                    'production.schedule' => 'Schedule production',
                    'production.track' => 'Track production progress',
                    'production.reports' => 'View production reports',
                ]
            ],

            // 17. DASHBOARD & GENERAL
            'dashboard' => [
                'name' => 'Dashboard Access',
                'icon' => 'gauge',
                'description' => 'Dashboard and general access',
                'permissions' => [
                    'dashboard.view' => 'View dashboard',
                    'dashboard.manage' => 'Manage dashboard layout',
                    'dashboard.widgets' => 'Manage dashboard widgets',
                    'dashboard.export' => 'Export dashboard data',
                ]
            ],
        ];
    }

    /**
     * Get role templates with their predefined permissions
     */
    public function getRoleTemplates(): array
    {
        return [
            'Organization Administrator' => [
                'guard_name' => 'admin',
                'level' => 'organization',
                'description' => 'Full access within organization scope',
                'icon' => 'building',
                'color' => 'purple',
                'permissions' => [
                    'organizations.view', 'organizations.edit', 'organizations.settings',
                    'branches.view', 'branches.create', 'branches.edit', 'branches.delete', 'branches.activate', 'branches.manage',
                    'users.view', 'users.create', 'users.edit', 'users.delete', 'users.activate', 'users.manage',
                    'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.manage', 'roles.assign',
                    'menus.view', 'menus.create', 'menus.edit', 'menus.delete', 'menus.activate', 'menus.manage',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.process', 'orders.cancel', 'orders.manage',
                    'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.adjust', 'inventory.manage',
                    'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.manage',
                    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.manage',
                    'kitchen.view', 'kitchen.manage', 'kitchen.orders', 'kitchen.staff',
                    'reports.view', 'reports.export', 'reports.sales', 'reports.financial',
                    'staff.view', 'staff.create', 'staff.edit', 'staff.manage', 'staff.schedules',
                    'production.view', 'production.create', 'production.edit', 'production.manage',
                    'dashboard.view', 'dashboard.manage'
                ]
            ],

            'Branch Administrator' => [
                'guard_name' => 'admin',
                'level' => 'branch',
                'description' => 'Full access within branch scope',
                'icon' => 'store',
                'color' => 'green',
                'permissions' => [
                    'branches.view', 'branches.edit',
                    'users.view', 'users.create', 'users.edit', 'users.activate',
                    'roles.view', 'roles.assign',
                    'menus.view', 'menus.create', 'menus.edit', 'menus.activate',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.process', 'orders.manage',
                    'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.adjust', 'inventory.manage',
                    'suppliers.view', 'suppliers.edit',
                    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.manage',
                    'kitchen.view', 'kitchen.manage', 'kitchen.orders', 'kitchen.staff',
                    'reports.view', 'reports.export', 'reports.sales',
                    'staff.view', 'staff.create', 'staff.edit', 'staff.manage', 'staff.schedules',
                    'production.view', 'production.create', 'production.edit', 'production.manage',
                    'dashboard.view', 'dashboard.manage'
                ]
            ],

            'Kitchen Manager' => [
                'guard_name' => 'admin',
                'level' => 'branch',
                'description' => 'Kitchen operations and order processing',
                'icon' => 'chef-hat',
                'color' => 'orange',
                'permissions' => [
                    'kitchen.view', 'kitchen.manage', 'kitchen.orders', 'kitchen.stations', 'kitchen.staff',
                    'orders.view', 'orders.process',
                    'menus.view', 'menus.edit',
                    'inventory.view', 'inventory.adjust', 'inventory.alerts',
                    'staff.view', 'staff.schedules',
                    'production.view', 'production.create', 'production.edit', 'production.manage',
                    'reports.view', 'reports.inventory',
                    'dashboard.view'
                ]
            ],

            'Operations Manager' => [
                'guard_name' => 'admin',
                'level' => 'branch',
                'description' => 'Daily operations and staff management',
                'icon' => 'clipboard-check',
                'color' => 'blue',
                'permissions' => [
                    'orders.view', 'orders.create', 'orders.edit', 'orders.process', 'orders.cancel',
                    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.confirm',
                    'staff.view', 'staff.create', 'staff.edit', 'staff.schedules',
                    'inventory.view', 'inventory.adjust',
                    'menus.view',
                    'reports.view', 'reports.sales',
                    'dashboard.view'
                ]
            ],

            'Staff Member' => [
                'guard_name' => 'admin',
                'level' => 'staff',
                'description' => 'Basic operational access',
                'icon' => 'user',
                'color' => 'gray',
                'permissions' => [
                    'orders.view', 'orders.create',
                    'reservations.view', 'reservations.create',
                    'menus.view',
                    'inventory.view',
                    'dashboard.view'
                ]
            ]
        ];
    }

    /**
     * Get available permissions based on admin scope
     */
    public function getAvailablePermissions($admin, $permissionDefinitions): array
    {
        if ($admin->is_super_admin) {
            return $this->flattenPermissions($permissionDefinitions);
        }

        if ($admin->isOrganizationAdmin()) {
            $excludedCategories = ['modules', 'settings'];
            $excludedPermissions = ['organizations.create', 'organizations.delete'];
            return $this->flattenPermissions($permissionDefinitions, $excludedCategories, $excludedPermissions);
        }

        if ($admin->isBranchAdmin()) {
            $allowedCategories = ['orders', 'reservations', 'menus', 'inventory', 'kitchen', 'staff', 'reports', 'dashboard'];
            $allowedPermissions = ['users.view', 'users.create', 'users.edit', 'roles.view', 'roles.assign'];
            return $this->flattenPermissions($permissionDefinitions, [], [], $allowedCategories, $allowedPermissions);
        }

        return [];
    }

    /**
     * Flatten permission definitions into a simple array
     */
    private function flattenPermissions($permissionDefinitions, $excludedCategories = [], $excludedPermissions = [], $allowedCategories = [], $allowedPermissions = []): array
    {
        $permissions = [];

        foreach ($permissionDefinitions as $category => $definition) {
            if (in_array($category, $excludedCategories)) {
                continue;
            }

            if (!empty($allowedCategories) && !in_array($category, $allowedCategories)) {
                continue;
            }

            foreach ($definition['permissions'] as $permission => $description) {
                if (in_array($permission, $excludedPermissions)) {
                    continue;
                }
                $permissions[$permission] = $description;
            }
        }

        foreach ($allowedPermissions as $permission) {
            if (!isset($permissions[$permission])) {
                $permissions[$permission] = ucwords(str_replace(['.', '_'], ' ', $permission));
            }
        }

        return $permissions;
    }

    /**
     * Filter role templates based on admin scope
     */
    public function filterTemplatesByScope($roleTemplates, $admin): array
    {
        if ($admin->is_super_admin) {
            return $roleTemplates;
        }

        $filtered = [];

        foreach ($roleTemplates as $name => $template) {
            if ($admin->isOrganizationAdmin()) {
                if ($name !== 'Super Administrator') {
                    $filtered[$name] = $template;
                }
            } elseif ($admin->isBranchAdmin()) {
                if (in_array($template['level'], ['branch', 'staff'])) {
                    $filtered[$name] = $template;
                }
            }
        }

        return $filtered;
    }
}