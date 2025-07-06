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

            $headOffice = $this->createHeadOfficeBranch($organization);


            $this->createDefaultKitchenStations($headOffice);


            $orgAdmin = $this->createOrganizationAdmin($organization, $headOffice);

            $branchAdmin = $this->createBranchAdmin($organization, $headOffice);

            // 6. Setup organization-specific roles and permissions
            $this->setupOrganizationRoles($organization);

            // 7. Assign permissions based on subscription plan modules
            $this->assignSubscriptionPermissions($organization, $orgAdmin);
            $this->assignBranchPermissions($organization, $branchAdmin, $headOffice);

            // 8. Setup branch-specific resources
            $this->branchAutomationService->setupBranchResources($headOffice);

            // 9. Send welcome email with credentials
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

        // Assign organization admin role
        $orgAdminRole = Role::where('name', 'Organization Administrator')
            ->where('organization_id', $organization->id)
            ->first();
        
        if (!$orgAdminRole) {
            // Create the role if it doesn't exist
            $orgAdminRole = Role::create([
                'name' => 'Organization Administrator',
                'organization_id' => $organization->id,
                'guard_name' => 'admin',
                'scope' => 'organization',
                'description' => 'Full administrative access to organization',
            ]);
        }

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

        // Assign branch admin role
        $branchAdminRole = Role::where('name', 'Branch Administrator')
            ->where('organization_id', $organization->id)
            ->first();
        
        if (!$branchAdminRole) {
            // Create the role if it doesn't exist
            $branchAdminRole = Role::create([
                'name' => 'Branch Administrator',
                'organization_id' => $organization->id,
                'guard_name' => 'admin',
                'scope' => 'branch',
                'description' => 'Full administrative access to branch operations',
            ]);
        }

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
     * Setup default roles for organization
     */
    protected function setupOrganizationRoles(Organization $organization): void
    {
        $systemRoles = Role::getSystemRoles();

        foreach ($systemRoles as $roleKey => $roleData) {
            if (in_array($roleData['scope'], ['organization', 'branch', 'personal'])) {
                Role::firstOrCreate(
                    [
                        'name' => $roleData['name'],
                        'organization_id' => $organization->id,
                        'guard_name' => 'admin'
                    ],
                    [
                        'scope' => $roleData['scope'],
                        'description' => $roleData['description'] ?? '',
                    ]
                );
            }
        }
    }

    /**
     * Assign permissions based on subscription plan
     */
    protected function assignSubscriptionPermissions(Organization $organization, Admin $admin): void
    {
        $subscriptionPlan = $organization->subscriptionPlan;
        
        if (!$subscriptionPlan) {
            Log::warning('No subscription plan found for organization', ['organization_id' => $organization->id]);
            return;
        }

        // Get modules from subscription plan
        $moduleIds = $subscriptionPlan->getModulesArray();
        
        // Get module permissions from config
        $moduleConfig = config('modules', []);
        $permissions = [];

        foreach ($moduleIds as $moduleId) {
            $module = \App\Models\Module::find($moduleId);
            if (!$module) continue;

            $moduleSlug = $module->slug;
            if (isset($moduleConfig[$moduleSlug])) {
                $moduleData = $moduleConfig[$moduleSlug];
                
                // Get permissions for the tier (default to basic if not specified)
                $tier = 'basic'; // You can enhance this to get tier from subscription
                if (isset($moduleData['tiers'][$tier]['permissions'])) {
                    $permissions = array_merge($permissions, $moduleData['tiers'][$tier]['permissions']);
                }
            }
        }

        // Create permissions if they don't exist and assign to admin
        foreach ($permissions as $permissionName) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'admin'
            ]);
            
            $admin->givePermissionTo($permission);
        }

        Log::info('Subscription permissions assigned', [
            'organization_id' => $organization->id,
            'admin_id' => $admin->id,
            'permissions_count' => count($permissions)
        ]);
    }

    /**
     * Assign branch-specific permissions
     */
    protected function assignBranchPermissions(Organization $organization, Admin $branchAdmin, Branch $branch): void
    {
        $subscriptionPlan = $organization->subscriptionPlan;
        
        if (!$subscriptionPlan) {
            Log::warning('No subscription plan found for branch admin permissions', [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id
            ]);
            return;
        }

        // Get branch-specific permissions based on available modules
        $moduleIds = $subscriptionPlan->getModulesArray();
        $moduleConfig = config('modules', []);
        $branchPermissions = [];

        foreach ($moduleIds as $moduleId) {
            $module = \App\Models\Module::find($moduleId);
            if (!$module) continue;

            $moduleSlug = $module->slug;
            if (isset($moduleConfig[$moduleSlug])) {
                $moduleData = $moduleConfig[$moduleSlug];
                
                // Get basic tier permissions for branch admin
                if (isset($moduleData['tiers']['basic']['permissions'])) {
                    $branchPermissions = array_merge($branchPermissions, $moduleData['tiers']['basic']['permissions']);
                }
            }
        }

        // Add general branch management permissions
        $branchPermissions = array_merge($branchPermissions, [
            'branch.view',
            'branch.edit',
            'staff.view',
            'staff.create',
            'staff.edit',
            'orders.view',
            'orders.create',
            'orders.process',
            'kitchen.view',
            'kitchen.manage',
            'inventory.view',
            'reports.view'
        ]);

        // Create permissions if they don't exist and assign to branch admin
        foreach (array_unique($branchPermissions) as $permissionName) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'admin'
            ]);
            
            $branchAdmin->givePermissionTo($permission);
        }

        Log::info('Branch permissions assigned', [
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'admin_id' => $branchAdmin->id,
            'permissions_count' => count(array_unique($branchPermissions))
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
