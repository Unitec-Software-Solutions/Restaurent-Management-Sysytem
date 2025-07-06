<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\Role;
use App\Models\KitchenStation;
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

            // 8. Send welcome email with credentials (skip branch automation as it's already done above)
            $this->sendWelcomeEmail($organization, $orgAdmin, $branchAdmin);

            // 10. Log organization creation
            Log::info('Organization created successfully with complete automation', [
                'organization_id' => $organization->id,
                'name' => $organization->name,
                'subscription_plan_id' => $organization->subscription_plan_id,
                'head_office_id' => $headOffice->id,
                'org_admin_id' => $orgAdmin->id,
                'branch_admin_id' => $branchAdmin->id,
                'kitchen_stations_count' => $headOffice->kitchenStations()->count()
            ]);

            return $organization->load(['branches.kitchenStations', 'admins.roles', 'subscriptionPlan']);
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
        // Define comprehensive organization admin permissions
        $orgAdminPermissions = [
            // Organization Management
            'organizations.view',
            'organizations.edit',
            'organizations.settings',
            
            // Branch Management
            'branches.view',
            'branches.create',
            'branches.edit',
            'branches.delete',
            'branches.activate',
            
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.activate',
            
            // Role Management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.assign',
            
            // Subscription Management
            'subscription.view',
            'subscription.manage',
            
            // Reports and Analytics
            'reports.view',
            'reports.export',
            'reports.analytics',
            
            // System Settings
            'settings.view',
            'settings.edit'
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

        Log::info('Organization admin permissions assigned', [
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
        // Define comprehensive branch admin permissions
        $branchAdminPermissions = [
            // Branch Operations
            'branches.view',
            'branches.edit',
            
            // Staff Management
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.schedule',
            
            // Order Management
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.process',
            'orders.cancel',
            
            // Kitchen Management
            'kitchen.view',
            'kitchen.manage',
            'kitchen.stations',
            
            // Inventory Management
            'inventory.view',
            'inventory.adjust',
            'inventory.count',
            
            // Menu Management
            'menus.view',
            'menus.edit',
            'menus.activate',
            
            // Reservation Management
            'reservations.view',
            'reservations.create',
            'reservations.edit',
            'reservations.cancel',
            
            // Reports
            'reports.view',
            'reports.branch',
            
            // Basic Settings
            'settings.view'
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

        Log::info('Branch admin permissions assigned', [
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
