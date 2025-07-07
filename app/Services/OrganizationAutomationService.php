<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\Role;
use App\Models\KitchenStation;
use App\Models\ItemCategory;
use App\Mail\OrganizationWelcomeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrganizationAutomationService
{
    protected $branchAutomationService;

    public function __construct(BranchAutomationService $branchAutomationService)
    {
        $this->branchAutomationService = $branchAutomationService;
    }


    public function setupNewOrganization(array $organizationData): Organization
    {
        return DB::transaction(function () use ($organizationData) {

            $organization = Organization::create($organizationData);

            // Check if head office already exists (created by OrganizationObserver)
            $headOffice = $organization->branches()->where('is_head_office', true)->first();

            if (!$headOffice) {
                // Create head office only if it doesn't exist
                $headOffice = $this->createHeadOfficeBranch($organization);
            } else {
                Log::info('Head office already exists, using existing one', [
                    'organization_id' => $organization->id,
                    'head_office_id' => $headOffice->id,
                    'head_office_name' => $headOffice->name
                ]);
            }

            // Ensure kitchen stations exist for head office
            if ($headOffice->kitchenStations()->count() === 0) {
                $this->createDefaultKitchenStations($headOffice);
            }

            $orgAdmin = $this->createOrganizationAdmin($organization, $headOffice);

            $branchAdmin = $this->createBranchAdmin($organization, $headOffice);

            // 6. Setup organization-specific roles and permissions
            $this->setupOrganizationRoles($organization);

            // 7. Assign permissions based on subscription plan modules
            $this->assignSubscriptionPermissions($organization, $orgAdmin);
            $this->assignBranchPermissions($organization, $branchAdmin, $headOffice);

            // 6. Create default item categories
            Log::info('About to create default item categories', ['organization_id' => $organization->id]);
            $this->createDefaultItemCategories($organization);

            // 7. Send welcome email
            $this->sendWelcomeEmail($organization, $orgAdmin);

            // 8. Log organization creation


            return $organization->load(['branches', 'admins']);
        });
    }

    /**
     * Create head office branch
     */
    protected function createHeadOfficeBranch(Organization $organization): Branch
    {
        $branchData = [
            'organization_id' => $organization->id,
            'name' => $organization->name . ' - Head Office',
            'slug' => Str::slug($organization->name . '-head-office'),
            'type' => 'head_office',
            'is_head_office' => true,
            'address' => $organization->address,
            'phone' => $organization->phone,
            'contact_person' => $organization->contact_person,
            'contact_person_designation' => $organization->contact_person_designation ?? 'Manager',
            'contact_person_phone' => $organization->contact_person_phone ?? $organization->phone,
            'opening_time' => '09:00:00', // Set default opening time if not provided
            'closing_time' => '22:00:00', // Ensure closing_time is set
            'total_capacity' => 50, // Default capacity for head office
            'reservation_fee' => 0.00, // Default reservation fee
            'cancellation_fee' => 0.00, // Default cancellation fee
            'is_active' => true,
        ];

        return Branch::create($branchData);
    }

    /**
     * Create organization administrator
     */
    protected function createOrganizationAdmin(Organization $organization, Branch $headOffice = null): Admin
    {
        // Use the default admin password for organization admins
        $defaultPassword = config('auto_system_settings.default_org_admin_password', 'AdminPassword123!');

        $adminData = [
            'organization_id' => $organization->id,
            'branch_id' => null, // Organization admin is not tied to specific branch
            'name' => $organization->contact_person ?? 'Organization Administrator',
            'email' => $organization->email,
            'password' => Hash::make($defaultPassword),
            'phone' => $organization->contact_person_phone ?? $organization->phone,
            'job_title' => 'Organization Administrator',
            'is_active' => true,
        ];

        $admin = Admin::create($adminData);

        // Assign organization admin role (use firstOrCreate to avoid duplicates)
        $orgAdminRole = Role::firstOrCreate(
            [
                'name' => 'Organization Administrator',
                'organization_id' => $organization->id,
                'guard_name' => 'admin'
            ],
            [
                'scope' => 'organization',
                'description' => 'Full administrative access to organization-wide operations'
            ]
        );

        $admin->assignRole($orgAdminRole);

        // Store the plain text password for welcome email
        $admin->temporary_password = $defaultPassword;

        Log::info('Organization admin created with default password', [
            'admin_id' => $admin->id,
            'organization_id' => $organization->id,
            'email' => $admin->email,
            'password_used' => 'default_admin_password'
        ]);

        return $admin;
    }

    /**
     * Create branch administrator for head office
     */
    protected function createBranchAdmin(Organization $organization, Branch $headOffice): Admin
    {
        // Use the default branch admin password
        $defaultPassword = config('auto_system_settings.default_branch_admin_password', 'BranchAdmin123!');

        $adminData = [
            'organization_id' => $organization->id,
            'branch_id' => $headOffice->id,
            'name' => ($organization->contact_person ?? 'Head Office') . ' - Branch Admin',
            'email' => 'branch.admin@' . str_replace('@', '.', $organization->email),
            'password' => Hash::make($defaultPassword),
            'phone' => $organization->contact_person_phone ?? $organization->phone,
            'job_title' => 'Branch Administrator - Head Office',
            'is_active' => true,
        ];

        $admin = Admin::create($adminData);

        // Assign branch admin role (use firstOrCreate to avoid duplicates)
        $branchAdminRole = Role::firstOrCreate(
            [
                'name' => 'Branch Administrator',
                'organization_id' => $organization->id,
                'guard_name' => 'admin'
            ],
            [
                'scope' => 'branch',
                'description' => 'Full administrative access to branch operations'
            ]
        );

        $admin->assignRole($branchAdminRole);

        // Store password for welcome email
        $admin->temporary_password = $defaultPassword;

        Log::info('Branch admin created for head office with default password', [
            'admin_id' => $admin->id,
            'organization_id' => $organization->id,
            'branch_id' => $headOffice->id,
            'email' => $admin->email,
            'password_used' => 'default_branch_password'
        ]);

        return $admin;
    }

    /**
     * Setup only essential roles for organization
     */
    protected function setupOrganizationRoles(Organization $organization): void
    {
        // Only create the essential roles that will actually be used
        $essentialRoles = [
            [
                'name' => 'Organization Administrator',
                'scope' => 'organization',
                'description' => 'Full administrative access to organization-wide operations',
                'is_system_role' => true
            ],
            [
                'name' => 'Branch Administrator',
                'scope' => 'branch',
                'description' => 'Full administrative access to branch operations',
                'is_system_role' => true
            ]
        ];

        foreach ($essentialRoles as $roleData) {
            Role::firstOrCreate(
                [
                    'name' => $roleData['name'],
                    'organization_id' => $organization->id,
                    'guard_name' => 'admin'
                ],
                [
                    'scope' => $roleData['scope'],
                    'description' => $roleData['description'],
                    'is_system_role' => $roleData['is_system_role']
                ]
            );
        }

        Log::info('Essential organization roles created', [
            'organization_id' => $organization->id,
            'roles_created' => count($essentialRoles)
        ]);
    }

    /**
     * Assign comprehensive permissions to organization admin
     */
    protected function assignSubscriptionPermissions(Organization $organization, Admin $admin): void
    {
        // Define COMPREHENSIVE organization admin permissions (all admin functions)
        $orgAdminPermissions = [
            // Organization Management
            'organizations.view',
            'organizations.edit',
            'organizations.settings',
            'organization.view',
            'organization.manage',
            'organization.update',
            'organization.create',

            // Branch Management
            'branches.view',
            'branches.create',
            'branches.edit',
            'branches.delete',
            'branches.activate',
            'branch.view',
            'branch.create',
            'branch.manage',
            'branch.update',

            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.activate',
            'user.view',
            'user.create',
            'user.manage',
            'user.update',
            'user.delete',

            // Role & Permission Management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.assign',
            'role.view',
            'role.create',
            'role.manage',
            'role.update',
            'role.delete',
            'permission.view',
            'permission.manage',

            // Subscription & Billing Management
            'subscription.view',
            'subscription.manage',
            'billing.view',
            'billing.manage',
            'billing.create',

            // Staff Management (Organization-wide)
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'staff.manage',
            'staff.schedule',
            'staff.performance',
            'staff.attendance',
            'staff.update',

            // Reports and Analytics (All levels)
            'reports.view',
            'reports.export',
            'reports.analytics',
            'reports.branch',
            'report.view',
            'report.generate',
            'report.export',
            'report.dashboard',
            'report.sales',
            'report.financial',
            'report.inventory',
            'report.staff',

            // Menu Management (Organization-wide)
            'menus.view',
            'menus.edit',
            'menus.activate',
            'menu.view',
            'menu.create',
            'menu.manage',
            'menu.update',
            'menu.delete',
            'menu.categories',
            'menu.pricing',
            'menu.publish',
            'menu.schedule',

            // Order Management (Organization-wide)
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.process',
            'orders.cancel',
            'order.view',
            'order.create',
            'order.manage',
            'order.update',
            'order.delete',
            'order.process',
            'order.cancel',
            'order.refund',
            'order.print_kot',

            // Kitchen Management (Organization-wide)
            'kitchen.view',
            'kitchen.manage',
            'kitchen.stations',
            'kitchen.orders',
            'kitchen.production',
            'kitchen.recipes',
            'kitchen.status',

            // Inventory Management (Organization-wide)
            'inventory.view',
            'inventory.adjust',
            'inventory.count',
            'inventory.create',
            'inventory.delete',
            'inventory.manage',
            'inventory.update',
            'inventory.transfer',
            'inventory.audit',

            // Reservation Management (Organization-wide)
            'reservations.view',
            'reservations.create',
            'reservations.edit',
            'reservations.cancel',
            'reservation.view',
            'reservation.create',
            'reservation.manage',
            'reservation.update',
            'reservation.delete',
            'reservation.approve',
            'reservation.cancel',
            'reservation.checkin',

            // Customer Management
            'customer.view',
            'customer.create',
            'customer.manage',
            'customer.update',
            'customer.delete',
            'customer.communications',
            'customer.loyalty',

            // Payment Management
            'payment.view',
            'payment.manage',
            'payment.process',
            'payment.refund',

            // KOT Management
            'kot.view',
            'kot.create',
            'kot.manage',
            'kot.update',
            'kot.print',

            // System Settings
            'settings.view',
            'settings.edit',
            'system.manage',
            'system.settings',
            'system.backup',
            'system.logs',

            // Dashboard Access
            'dashboard.view',
            'dashboard.manage',

            // Profile Management
            'profile.view',
            'profile.update'
        ];

        // Create and assign permissions
        foreach ($orgAdminPermissions as $permissionName) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'admin'
            ]);

            try {
                $admin->givePermissionTo($permission);
            } catch (\Exception $e) {
                Log::warning('Failed to assign permission to organization admin', [
                    'permission' => $permissionName,
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Comprehensive organization admin permissions assigned', [
            'organization_id' => $organization->id,
            'admin_id' => $admin->id,
            'permissions_assigned' => count($orgAdminPermissions)
        ]);
    }

    /**
     * Assign comprehensive permissions to branch admin
     */
    protected function assignBranchPermissions(Organization $organization, Admin $branchAdmin, Branch $branch): void
    {
        // Define COMPREHENSIVE branch admin permissions (all branch-level admin functions)
        $branchAdminPermissions = [
            // Branch Operations
            'branches.view',
            'branches.edit',
            'branch.view',
            'branch.manage',
            'branch.update',

            // Staff Management (Branch-specific)
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'staff.schedule',
            'staff.manage',
            'staff.performance',
            'staff.attendance',
            'staff.update',

            // Order Management (Branch-specific)
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.process',
            'orders.cancel',
            'order.view',
            'order.create',
            'order.manage',
            'order.update',
            'order.delete',
            'order.process',
            'order.cancel',
            'order.refund',
            'order.print_kot',

            // Kitchen Management (Branch-specific)
            'kitchen.view',
            'kitchen.manage',
            'kitchen.stations',
            'kitchen.orders',
            'kitchen.production',
            'kitchen.recipes',
            'kitchen.status',

            // Inventory Management (Branch-specific)
            'inventory.view',
            'inventory.adjust',
            'inventory.count',
            'inventory.create',
            'inventory.delete',
            'inventory.manage',
            'inventory.update',
            'inventory.transfer',
            'inventory.audit',

            // Menu Management (Branch-specific)
            'menus.view',
            'menus.edit',
            'menus.activate',
            'menu.view',
            'menu.create',
            'menu.manage',
            'menu.update',
            'menu.delete',
            'menu.categories',
            'menu.pricing',
            'menu.publish',
            'menu.schedule',

            // Reservation Management (Branch-specific)
            'reservations.view',
            'reservations.create',
            'reservations.edit',
            'reservations.cancel',
            'reservation.view',
            'reservation.create',
            'reservation.manage',
            'reservation.update',
            'reservation.delete',
            'reservation.approve',
            'reservation.cancel',
            'reservation.checkin',

            // Customer Management (Branch-specific)
            'customer.view',
            'customer.create',
            'customer.manage',
            'customer.update',
            'customer.delete',
            'customer.communications',
            'customer.loyalty',

            // Payment Management (Branch-specific)
            'payment.view',
            'payment.manage',
            'payment.process',
            'payment.refund',

            // KOT Management (Branch-specific)
            'kot.view',
            'kot.create',
            'kot.manage',
            'kot.update',
            'kot.print',

            // Reports (Branch-specific)
            'reports.view',
            'reports.branch',
            'reports.export',
            'report.view',
            'report.generate',
            'report.export',
            'report.dashboard',
            'report.sales',
            'report.financial',
            'report.inventory',
            'report.staff',

            // User Management (Branch-level)
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.activate',
            'user.view',
            'user.create',
            'user.manage',
            'user.update',
            'user.delete',

            // Basic Settings (Branch-level)
            'settings.view',
            'settings.edit',

            // Dashboard Access
            'dashboard.view',
            'dashboard.manage',

            // Profile Management
            'profile.view',
            'profile.update'
        ];

        // Create and assign permissions
        foreach ($branchAdminPermissions as $permissionName) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'admin'
            ]);

            try {
                $branchAdmin->givePermissionTo($permission);
            } catch (\Exception $e) {
                Log::warning('Failed to assign permission to branch admin', [
                    'permission' => $permissionName,
                    'admin_id' => $branchAdmin->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Comprehensive branch admin permissions assigned', [
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'admin_id' => $branchAdmin->id,
            'permissions_assigned' => count($branchAdminPermissions)
        ]);
    }

    /**
     * Create default kitchen stations for head office
     */
    protected function createDefaultKitchenStations(Branch $branch): void
    {
        $defaultStations = [
            [
                'name' => 'Main Kitchen',
                'code' => $this->generateStationCode('MAIN', $branch->id, 1),
                'type' => 'cooking',
                'description' => 'Primary cooking station',
                'order_priority' => 1,
                'max_capacity' => 50.00,

            ],
            [
                'name' => 'Prep Station',
                'code' => $this->generateStationCode('PREP', $branch->id, 2),
                'type' => 'prep',
                'description' => 'Food preparation area',
                'order_priority' => 2,
                'max_capacity' => 30.00,

            ],
            [
                'name' => 'Service Station',
                'code' => $this->generateStationCode('SERV', $branch->id, 3),
                'type' => 'service',
                'description' => 'Final preparation and plating',
                'order_priority' => 3,
                'max_capacity' => 25.00,

            ]
        ];

        foreach ($defaultStations as $stationData) {
            $stationData['branch_id'] = $branch->id;
            $stationData['organization_id'] = $branch->organization_id;
            $stationData['is_active'] = true;


            KitchenStation::create($stationData);
        }
    }

    /**
     * Generate unique station code
     */
    protected function generateStationCode(string $typePrefix, int $branchId, int $sequence): string
    {
        $branchCode = str_pad($branchId, 2, '0', STR_PAD_LEFT);
        $sequenceCode = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return $typePrefix . '-' . $branchCode . '-' . $sequenceCode;
    }

    /**
     * Create default item categories for organization
     */
    protected function createDefaultItemCategories(Organization $organization): void
    {
        Log::info('Creating default item categories for organization', [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name
        ]);

        $defaultCategories = [
            [
                'name' => 'Production Items',
                'code' => 'PI' . $organization->id,
                'description' => 'Items that are produced in-house like buns, bread, etc.',
            ],
            [
                'name' => 'Buy & Sell',
                'code' => 'BS' . $organization->id,
                'description' => 'Items that are bought and sold directly',
            ],
            [
                'name' => 'Ingredients',
                'code' => 'IG' . $organization->id,
                'description' => 'Raw cooking ingredients and supplies',
            ],
            // [
            //     'name' => 'Beverages',
            //     'code' => 'BV' . $organization->id,
            //     'description' => 'Drinks and beverage items',
            // ],
            // [
            //     'name' => 'Kitchen Supplies',
            //     'code' => 'KS' . $organization->id,
            //     'description' => 'Kitchen equipment and supplies',
            // ],
        ];

        $categoriesCreated = 0;
        $categoriesSkipped = 0;

        foreach ($defaultCategories as $categoryData) {
            try {
                // Check if category already exists
                $exists = ItemCategory::where('organization_id', $organization->id)
                    ->where(function ($query) use ($categoryData) {
                        $query->where('name', $categoryData['name'])
                            ->orWhere('code', $categoryData['code']);
                    })
                    ->exists();

                if (!$exists) {
                    $category = ItemCategory::create([
                        'name' => $categoryData['name'],
                        'code' => $categoryData['code'],
                        'description' => $categoryData['description'],
                        'is_active' => true,
                        'organization_id' => $organization->id,
                    ]);

                    Log::info('Item category created successfully', [
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                        'category_code' => $category->code,
                        'organization_id' => $organization->id
                    ]);

                    $categoriesCreated++;
                } else {
                    Log::info('Item category already exists, skipping', [
                        'category_name' => $categoryData['name'],
                        'category_code' => $categoryData['code'],
                        'organization_id' => $organization->id
                    ]);

                    $categoriesSkipped++;
                }
            } catch (\Exception $e) {
                Log::error('Failed to create item category', [
                    'category_name' => $categoryData['name'],
                    'category_code' => $categoryData['code'],
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('Item category creation completed', [
            'organization_id' => $organization->id,
            'categories_created' => $categoriesCreated,
            'categories_skipped' => $categoriesSkipped,
            'total_categories' => count($defaultCategories)
        ]);
    }

    /**
     * Send welcome emails to both organization and branch admins
     */
    protected function sendWelcomeEmail(Organization $organization, Admin $orgAdmin, Admin $branchAdmin = null): void
    {
        // Send welcome email to organization admin
        try {
            Mail::to($orgAdmin->email)->send(new OrganizationWelcomeMail($organization, $orgAdmin));

            Log::info('Welcome email sent to organization admin', [
                'organization_id' => $organization->id,
                'admin_email' => $orgAdmin->email
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to send welcome email to organization admin', [
                'organization_id' => $organization->id,
                'admin_email' => $orgAdmin->email,
                'error' => $e->getMessage()
            ]);
        }

        // Send welcome email to branch admin if provided
        if ($branchAdmin) {
            try {
                Mail::to($branchAdmin->email)->send(new OrganizationWelcomeMail($organization, $branchAdmin));

                Log::info('Welcome email sent to branch admin', [
                    'organization_id' => $organization->id,
                    'branch_admin_email' => $branchAdmin->email,
                    'branch_id' => $branchAdmin->branch_id
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send welcome email to branch admin', [
                    'organization_id' => $organization->id,
                    'branch_admin_email' => $branchAdmin->email,
                    'branch_id' => $branchAdmin->branch_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
