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

    /**
     * Complete organization setup with automation
     */
    public function setupNewOrganization(array $organizationData): Organization
    {
        return DB::transaction(function () use ($organizationData) {
            // 1. Create organization
            $organization = Organization::create($organizationData);

            // 2. Create head office branch
            $headOffice = $this->createHeadOfficeBranch($organization);

            // 3. Create organization admin
            $orgAdmin = $this->createOrganizationAdmin($organization);

            // 4. Setup default roles for organization
            // TODO: Fix Role::getSystemRoles() method to return correct field names
            // $this->setupOrganizationRoles($organization);

            // 5. Create default kitchen stations (disabled for now to debug other issues)
            // $this->createDefaultKitchenStations($headOffice);

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
    protected function createOrganizationAdmin(Organization $organization): Admin
    {
        $password = Str::random(12);

        $adminData = [
            'organization_id' => $organization->id,
            'name' => $organization->contact_person ?? 'Administrator',
            'email' => $organization->email,
            'password' => Hash::make($password),
            'phone' => $organization->contact_person_phone ?? $organization->phone,
            'job_title' => 'Organization Administrator',

            'is_active' => true,
        ];

        $admin = Admin::create($adminData);

        // Assign organization admin role
        $orgAdminRole = Role::where('name', 'Organization Administrator')->first();
        if ($orgAdminRole) {
            $admin->assignRole($orgAdminRole);
        }

        // Store password for welcome email
        $admin->temporary_password = $password;

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
     * Send welcome email to organization
     */
    protected function sendWelcomeEmail(Organization $organization, Admin $admin): void
    {
        try {
            Mail::to($admin->email)->send(new OrganizationWelcomeMail($organization, $admin));
        } catch (\Exception $e) {
            Log::warning('Failed to send welcome email to organization', [
                'organization_id' => $organization->id,
                'admin_email' => $admin->email,
                'error' => $e->getMessage()
            ]);
        }
    }
}
