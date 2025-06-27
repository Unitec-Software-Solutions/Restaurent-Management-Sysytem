<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
use App\Models\User;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class ExhaustiveRoleSeeder extends Seeder
{
    use WithoutModelEvents;

    private $permissionHierarchy = [];
    private $roleData = [];

    /**
     * Seed exhaustive role and permission scenarios for restaurant management
     */
    public function run(): void
    {
        $this->command->info('👑 Seeding Exhaustive Role & Permission Scenarios...');
        $this->command->info('═══════════════════════════════════════════════════════');
        
        try {
            // Phase 1: Core System Permissions
            $this->command->info('🔐 Phase 1: Core System Permissions');
            $this->seedCorePermissions();
            
            // Phase 2: Hierarchical Role Structure
            $this->command->info('🏗️ Phase 2: Hierarchical Role Structure');
            $this->seedHierarchicalRoles();
            
            // Phase 3: Department-Specific Roles
            $this->command->info('🏢 Phase 3: Department-Specific Roles');
            $this->seedDepartmentRoles();
            
            // Phase 4: Branch-Level Role Variations
            $this->command->info('🏪 Phase 4: Branch-Level Role Variations');
            $this->seedBranchRoles();
            
            // Phase 5: Temporary & Special Roles
            $this->command->info('⏱️ Phase 5: Temporary & Special Assignment Roles');
            $this->seedTemporaryRoles();
            
            // Phase 6: Permission Boundary Testing
            $this->command->info('🔍 Phase 6: Permission Boundary Testing');
            $this->seedPermissionBoundaries();
            
            // Phase 7: Multi-Role User Scenarios
            $this->command->info('👥 Phase 7: Multi-Role User Scenarios');
            $this->seedMultiRoleUsers();
            
            // Phase 8: Role Inheritance & Delegation
            $this->command->info('⬇️ Phase 8: Role Inheritance & Delegation');
            $this->seedRoleInheritance();
            
            // Phase 9: Cross-Organization Role Management
            $this->command->info('🌐 Phase 9: Cross-Organization Role Management');
            $this->seedCrossOrgRoles();
            
            // Phase 10: Emergency & Override Roles
            $this->command->info('🚨 Phase 10: Emergency & Override Scenarios');
            $this->seedEmergencyRoles();
            
            $this->displayRoleSummary();
            
        } catch (\Exception $e) {
            $this->command->error('❌ Role seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedCorePermissions(): void
    {
        $corePermissions = [
            // System Administration
            'system' => [
                'system.view_logs', 'system.manage_settings', 'system.backup_database',
                'system.restore_database', 'system.update_system', 'system.manage_modules',
                'system.view_analytics', 'system.export_data', 'system.import_data'
            ],
            
            // User Management
            'users' => [
                'users.create', 'users.read', 'users.update', 'users.delete',
                'users.assign_roles', 'users.manage_permissions', 'users.view_activity',
                'users.reset_passwords', 'users.suspend_accounts', 'users.activate_accounts'
            ],
            
            // Organization Management
            'organizations' => [
                'organizations.create', 'organizations.read', 'organizations.update', 'organizations.delete',
                'organizations.manage_branches', 'organizations.view_analytics', 'organizations.manage_subscriptions',
                'organizations.configure_settings', 'organizations.export_data'
            ],
            
            // Branch Management
            'branches' => [
                'branches.create', 'branches.read', 'branches.update', 'branches.delete',
                'branches.manage_staff', 'branches.view_reports', 'branches.configure_settings',
                'branches.manage_inventory', 'branches.process_orders', 'branches.manage_tables'
            ],
            
            // Menu Management
            'menus' => [
                'menus.create', 'menus.read', 'menus.update', 'menus.delete',
                'menus.manage_categories', 'menus.set_prices', 'menus.manage_availability',
                'menus.create_specials', 'menus.manage_variants', 'menus.upload_images'
            ],
            
            // Order Management
            'orders' => [
                'orders.create', 'orders.read', 'orders.update', 'orders.delete',
                'orders.process_payment', 'orders.modify_items', 'orders.cancel',
                'orders.refund', 'orders.view_kitchen_display', 'orders.manage_delivery'
            ],
            
            // Reservation Management
            'reservations' => [
                'reservations.create', 'reservations.read', 'reservations.update', 'reservations.delete',
                'reservations.assign_tables', 'reservations.process_payment', 'reservations.check_in',
                'reservations.check_out', 'reservations.modify_time', 'reservations.cancel'
            ],
            
            // Inventory Management
            'inventory' => [
                'inventory.create', 'inventory.read', 'inventory.update', 'inventory.delete',
                'inventory.adjust_stock', 'inventory.transfer_items', 'inventory.receive_goods',
                'inventory.manage_suppliers', 'inventory.generate_reports', 'inventory.set_alerts'
            ],
            
            // Kitchen Operations
            'kitchen' => [
                'kitchen.view_orders', 'kitchen.update_status', 'kitchen.manage_stations',
                'kitchen.manage_recipes', 'kitchen.view_inventory', 'kitchen.manage_prep',
                'kitchen.quality_control', 'kitchen.manage_timers'
            ],
            
            // Reports & Analytics
            'reports' => [
                'reports.view_sales', 'reports.view_inventory', 'reports.view_staff',
                'reports.view_customer', 'reports.export_data', 'reports.schedule_reports',
                'reports.view_financial', 'reports.create_custom'
            ],
            
            // Financial Management
            'finance' => [
                'finance.view_transactions', 'finance.process_refunds', 'finance.manage_pricing',
                'finance.view_revenue', 'finance.manage_taxes', 'finance.reconcile_payments',
                'finance.generate_invoices', 'finance.manage_expenses'
            ]
        ];

        foreach ($corePermissions as $module => $permissions) {
            $this->command->info("  🔑 Creating {$module} permissions");
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
                $this->permissionHierarchy[$module][] = $permission;
            }
        }
    }

    private function seedHierarchicalRoles(): void
    {
        // Super Admin - Full System Access
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'admin')->get());
        $this->roleData['super_admin'] = Permission::where('guard_name', 'admin')->count();
        
        // System Administrator
        $systemAdmin = Role::firstOrCreate(['name' => 'system_administrator', 'guard_name' => 'admin']);
        $systemAdminPerms = collect($this->permissionHierarchy['system'] ?? [])
            ->merge($this->permissionHierarchy['users'] ?? [])
            ->merge($this->permissionHierarchy['reports'] ?? []);
        $systemAdmin->syncPermissions($systemAdminPerms->toArray());
        $this->roleData['system_administrator'] = $systemAdminPerms->count();
        
        // Organization Owner
        $orgOwner = Role::firstOrCreate(['name' => 'organization_owner', 'guard_name' => 'admin']);
        $orgOwnerPerms = collect($this->permissionHierarchy['organizations'] ?? [])
            ->merge($this->permissionHierarchy['branches'] ?? [])
            ->merge($this->permissionHierarchy['users'] ?? [])
            ->merge($this->permissionHierarchy['finance'] ?? [])
            ->merge($this->permissionHierarchy['reports'] ?? []);
        $orgOwner->syncPermissions($orgOwnerPerms->toArray());
        $this->roleData['organization_owner'] = $orgOwnerPerms->count();
        
        // Organization Administrator
        $orgAdmin = Role::firstOrCreate(['name' => 'organization_administrator', 'guard_name' => 'admin']);
        $orgAdminPerms = collect([
            'organizations.read', 'organizations.update', 'organizations.view_analytics',
            'branches.read', 'branches.update', 'branches.manage_staff', 'branches.view_reports',
            'users.create', 'users.read', 'users.update', 'users.assign_roles',
            'reports.view_sales', 'reports.view_inventory', 'reports.view_staff'
        ]);
        $orgAdmin->syncPermissions($orgAdminPerms->toArray());
        $this->roleData['organization_administrator'] = $orgAdminPerms->count();
        
        // Branch Manager
        $branchManager = Role::firstOrCreate(['name' => 'branch_manager', 'guard_name' => 'admin']);
        $branchManagerPerms = collect([
            'branches.read', 'branches.update', 'branches.manage_staff', 'branches.view_reports',
            'menus.create', 'menus.read', 'menus.update', 'menus.manage_categories',
            'orders.read', 'orders.update', 'orders.process_payment',
            'reservations.create', 'reservations.read', 'reservations.update',
            'inventory.read', 'inventory.update', 'inventory.adjust_stock',
            'reports.view_sales', 'reports.view_inventory'
        ]);
        $branchManager->syncPermissions($branchManagerPerms->toArray());
        $this->roleData['branch_manager'] = $branchManagerPerms->count();
        
        // Branch Assistant Manager
        $assistantManager = Role::firstOrCreate(['name' => 'assistant_manager', 'guard_name' => 'admin']);
        $assistantManagerPerms = collect([
            'branches.read', 'menus.read', 'menus.update',
            'orders.create', 'orders.read', 'orders.update',
            'reservations.create', 'reservations.read', 'reservations.update',
            'inventory.read', 'inventory.update'
        ]);
        $assistantManager->syncPermissions($assistantManagerPerms->toArray());
        $this->roleData['assistant_manager'] = $assistantManagerPerms->count();
    }

    private function seedDepartmentRoles(): void
    {
        // Kitchen Manager
        $kitchenManager = Role::firstOrCreate(['name' => 'kitchen_manager', 'guard_name' => 'admin']);
        $kitchenManagerPerms = collect($this->permissionHierarchy['kitchen'] ?? [])
            ->merge(['inventory.read', 'inventory.update', 'menus.read', 'orders.read']);
        $kitchenManager->syncPermissions($kitchenManagerPerms->toArray());
        $this->roleData['kitchen_manager'] = $kitchenManagerPerms->count();
        
        // Head Chef
        $headChef = Role::firstOrCreate(['name' => 'head_chef', 'guard_name' => 'admin']);
        $headChefPerms = collect([
            'kitchen.view_orders', 'kitchen.update_status', 'kitchen.manage_stations',
            'kitchen.manage_recipes', 'kitchen.quality_control',
            'menus.read', 'menus.create_specials', 'inventory.read'
        ]);
        $headChef->syncPermissions($headChefPerms->toArray());
        $this->roleData['head_chef'] = $headChefPerms->count();
        
        // Sous Chef
        $sousChef = Role::firstOrCreate(['name' => 'sous_chef', 'guard_name' => 'admin']);
        $sousChefPerms = collect([
            'kitchen.view_orders', 'kitchen.update_status', 'kitchen.manage_prep',
            'kitchen.quality_control', 'inventory.read'
        ]);
        $sousChef->syncPermissions($sousChefPerms->toArray());
        $this->roleData['sous_chef'] = $sousChefPerms->count();
        
        // Line Cook
        $lineCook = Role::firstOrCreate(['name' => 'line_cook', 'guard_name' => 'admin']);
        $lineCookPerms = collect([
            'kitchen.view_orders', 'kitchen.update_status', 'inventory.read'
        ]);
        $lineCook->syncPermissions($lineCookPerms->toArray());
        $this->roleData['line_cook'] = $lineCookPerms->count();
        
        // Front of House Manager
        $fohManager = Role::firstOrCreate(['name' => 'front_house_manager', 'guard_name' => 'admin']);
        $fohManagerPerms = collect([
            'reservations.create', 'reservations.read', 'reservations.update',
            'reservations.assign_tables', 'reservations.check_in', 'reservations.check_out',
            'orders.create', 'orders.read', 'orders.update',
            'branches.read', 'reports.view_sales'
        ]);
        $fohManager->syncPermissions($fohManagerPerms->toArray());
        $this->roleData['front_house_manager'] = $fohManagerPerms->count();
        
        // Waiter/Server
        $waiter = Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'admin']);
        $waiterPerms = collect([
            'orders.create', 'orders.read', 'orders.update',
            'reservations.read', 'reservations.check_in', 'reservations.check_out',
            'menus.read'
        ]);
        $waiter->syncPermissions($waiterPerms->toArray());
        $this->roleData['waiter'] = $waiterPerms->count();
        
        // Host/Hostess
        $host = Role::firstOrCreate(['name' => 'host', 'guard_name' => 'admin']);
        $hostPerms = collect([
            'reservations.create', 'reservations.read', 'reservations.update',
            'reservations.assign_tables', 'reservations.check_in'
        ]);
        $host->syncPermissions($hostPerms->toArray());
        $this->roleData['host'] = $hostPerms->count();
        
        // Inventory Manager
        $inventoryManager = Role::firstOrCreate(['name' => 'inventory_manager', 'guard_name' => 'admin']);
        $inventoryManagerPerms = collect($this->permissionHierarchy['inventory'] ?? []);
        $inventoryManager->syncPermissions($inventoryManagerPerms->toArray());
        $this->roleData['inventory_manager'] = $inventoryManagerPerms->count();
        
        // Financial Controller
        $financialController = Role::firstOrCreate(['name' => 'financial_controller', 'guard_name' => 'admin']);
        $financialControllerPerms = collect($this->permissionHierarchy['finance'] ?? [])
            ->merge($this->permissionHierarchy['reports'] ?? []);
        $financialController->syncPermissions($financialControllerPerms->toArray());
        $this->roleData['financial_controller'] = $financialControllerPerms->count();
    }

    private function seedBranchRoles(): void
    {
        // Franchise Owner
        $franchiseOwner = Role::firstOrCreate(['name' => 'franchise_owner', 'guard_name' => 'admin']);
        $franchiseOwnerPerms = collect([
            'branches.read', 'branches.update', 'branches.manage_staff', 'branches.view_reports',
            'menus.read', 'menus.update', 'menus.set_prices',
            'orders.read', 'reservations.read', 'inventory.read',
            'reports.view_sales', 'reports.view_financial', 'finance.view_revenue'
        ]);
        $franchiseOwner->syncPermissions($franchiseOwnerPerms->toArray());
        $this->roleData['franchise_owner'] = $franchiseOwnerPerms->count();
        
        // Regional Manager
        $regionalManager = Role::firstOrCreate(['name' => 'regional_manager', 'guard_name' => 'admin']);
        $regionalManagerPerms = collect([
            'branches.read', 'branches.view_reports', 'branches.manage_staff',
            'reports.view_sales', 'reports.view_inventory', 'reports.view_staff',
            'users.read', 'users.assign_roles'
        ]);
        $regionalManager->syncPermissions($regionalManagerPerms->toArray());
        $this->roleData['regional_manager'] = $regionalManagerPerms->count();
        
        // Multi-Branch Supervisor
        $multiBranchSupervisor = Role::firstOrCreate(['name' => 'multi_branch_supervisor', 'guard_name' => 'admin']);
        $multiBranchSupervisorPerms = collect([
            'branches.read', 'branches.view_reports',
            'orders.read', 'reservations.read', 'inventory.read',
            'reports.view_sales', 'reports.view_inventory'
        ]);
        $multiBranchSupervisor->syncPermissions($multiBranchSupervisorPerms->toArray());
        $this->roleData['multi_branch_supervisor'] = $multiBranchSupervisorPerms->count();
        
        // Branch Trainee
        $branchTrainee = Role::firstOrCreate(['name' => 'branch_trainee', 'guard_name' => 'admin']);
        $branchTraineePerms = collect([
            'orders.read', 'reservations.read', 'menus.read',
            'kitchen.view_orders', 'inventory.read'
        ]);
        $branchTrainee->syncPermissions($branchTraineePerms->toArray());
        $this->roleData['branch_trainee'] = $branchTraineePerms->count();
    }

    private function seedTemporaryRoles(): void
    {
        // Event Manager (Temporary)
        $eventManager = Role::firstOrCreate(['name' => 'event_manager_temp', 'guard_name' => 'admin']);
        $eventManagerPerms = collect([
            'reservations.create', 'reservations.read', 'reservations.update',
            'reservations.assign_tables', 'orders.create', 'orders.read',
            'menus.read', 'branches.read'
        ]);
        $eventManager->syncPermissions($eventManagerPerms->toArray());
        $this->roleData['event_manager_temp'] = $eventManagerPerms->count();
        
        // Consultant Auditor (Temporary)
        $consultant = Role::firstOrCreate(['name' => 'consultant_auditor', 'guard_name' => 'admin']);
        $consultantPerms = collect([
            'reports.view_sales', 'reports.view_inventory', 'reports.view_staff',
            'reports.view_financial', 'branches.read', 'organizations.read',
            'inventory.read', 'finance.view_transactions'
        ]);
        $consultant->syncPermissions($consultantPerms->toArray());
        $this->roleData['consultant_auditor'] = $consultantPerms->count();
        
        // Seasonal Staff Supervisor
        $seasonalSupervisor = Role::firstOrCreate(['name' => 'seasonal_supervisor', 'guard_name' => 'admin']);
        $seasonalSupervisorPerms = collect([
            'orders.create', 'orders.read', 'orders.update',
            'reservations.read', 'reservations.check_in', 'reservations.check_out',
            'kitchen.view_orders', 'inventory.read'
        ]);
        $seasonalSupervisor->syncPermissions($seasonalSupervisorPerms->toArray());
        $this->roleData['seasonal_supervisor'] = $seasonalSupervisorPerms->count();
        
        // Weekend Manager
        $weekendManager = Role::firstOrCreate(['name' => 'weekend_manager', 'guard_name' => 'admin']);
        $weekendManagerPerms = collect([
            'branches.read', 'orders.read', 'orders.update',
            'reservations.read', 'reservations.update',
            'kitchen.view_orders', 'reports.view_sales'
        ]);
        $weekendManager->syncPermissions($weekendManagerPerms->toArray());
        $this->roleData['weekend_manager'] = $weekendManagerPerms->count();
    }

    private function seedPermissionBoundaries(): void
    {
        // Read-Only Analyst
        $readOnlyAnalyst = Role::firstOrCreate(['name' => 'read_only_analyst', 'guard_name' => 'admin']);
        $readOnlyPerms = Permission::where('guard_name', 'admin')
            ->where('name', 'like', '%.read')
            ->orWhere('name', 'like', '%.view_%')
            ->get();
        $readOnlyAnalyst->syncPermissions($readOnlyPerms);
        $this->roleData['read_only_analyst'] = $readOnlyPerms->count();
        
        // Limited Branch Access
        $limitedBranchUser = Role::firstOrCreate(['name' => 'limited_branch_user', 'guard_name' => 'admin']);
        $limitedBranchPerms = collect([
            'orders.create', 'orders.read',
            'reservations.read', 'menus.read',
            'inventory.read'
        ]);
        $limitedBranchUser->syncPermissions($limitedBranchPerms->toArray());
        $this->roleData['limited_branch_user'] = $limitedBranchPerms->count();
        
        // Financial View Only
        $financialViewer = Role::firstOrCreate(['name' => 'financial_viewer', 'guard_name' => 'admin']);
        $financialViewerPerms = collect([
            'finance.view_transactions', 'finance.view_revenue',
            'reports.view_sales', 'reports.view_financial'
        ]);
        $financialViewer->syncPermissions($financialViewerPerms->toArray());
        $this->roleData['financial_viewer'] = $financialViewerPerms->count();
        
        // Kitchen Display Only
        $kitchenDisplay = Role::firstOrCreate(['name' => 'kitchen_display_only', 'guard_name' => 'admin']);
        $kitchenDisplayPerms = collect([
            'kitchen.view_orders', 'orders.read'
        ]);
        $kitchenDisplay->syncPermissions($kitchenDisplayPerms->toArray());
        $this->roleData['kitchen_display_only'] = $kitchenDisplayPerms->count();
    }

    private function seedMultiRoleUsers(): void
    {
        // Create test users with multiple roles
        $organizations = Organization::limit(3)->get();
        $branches = Branch::limit(5)->get();
        
        foreach ($organizations as $org) {
            // Multi-role organization admin
            $multiRoleAdmin = Admin::create([
                'name' => "Multi-Role Admin {$org->name}",
                'email' => "multirole.{$org->id}@restaurant.com",
                'password' => bcrypt('password'),
                'organization_id' => $org->id,
                'is_active' => true,
            ]);
            
            $multiRoleAdmin->assignRole(['organization_administrator', 'financial_controller']);
            $this->roleData['multi_role_users'] = ($this->roleData['multi_role_users'] ?? 0) + 1;
        }
        
        foreach ($branches->take(3) as $branch) {
            // Multi-department supervisor
            $multiDeptUser = Admin::create([
                'name' => "Multi-Dept Supervisor {$branch->name}",
                'email' => "multidept.{$branch->id}@restaurant.com",
                'password' => bcrypt('password'),
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'is_active' => true,
            ]);
            
            $multiDeptUser->assignRole(['front_house_manager', 'inventory_manager']);
            $this->roleData['multi_role_users'] = ($this->roleData['multi_role_users'] ?? 0) + 1;
        }
    }

    private function seedRoleInheritance(): void
    {
        // Create role inheritance patterns
        
        // Senior roles that inherit from junior roles
        $seniorWaiter = Role::firstOrCreate(['name' => 'senior_waiter', 'guard_name' => 'admin']);
        $waiterRole = Role::where('name', 'waiter')->first();
        if ($waiterRole) {
            $waiterPerms = $waiterRole->permissions->pluck('name');
            $additionalPerms = collect(['orders.modify_items', 'reservations.modify_time']);
            $seniorWaiter->syncPermissions($waiterPerms->merge($additionalPerms)->toArray());
            $this->roleData['senior_waiter'] = $waiterPerms->merge($additionalPerms)->count();
        }
        
        // Lead positions that inherit and extend
        $leadCook = Role::firstOrCreate(['name' => 'lead_cook', 'guard_name' => 'admin']);
        $lineCookRole = Role::where('name', 'line_cook')->first();
        if ($lineCookRole) {
            $lineCookPerms = $lineCookRole->permissions->pluck('name');
            $additionalPerms = collect(['kitchen.manage_prep', 'kitchen.manage_timers']);
            $leadCook->syncPermissions($lineCookPerms->merge($additionalPerms)->toArray());
            $this->roleData['lead_cook'] = $lineCookPerms->merge($additionalPerms)->count();
        }
        
        // Shift supervisor that inherits from multiple roles
        $shiftSupervisor = Role::firstOrCreate(['name' => 'shift_supervisor', 'guard_name' => 'admin']);
        $hostRole = Role::where('name', 'host')->first();
        $waiterRole = Role::where('name', 'waiter')->first();
        
        if ($hostRole && $waiterRole) {
            $combinedPerms = $hostRole->permissions->pluck('name')
                ->merge($waiterRole->permissions->pluck('name'))
                ->merge(['reports.view_sales', 'inventory.read'])
                ->unique();
            $shiftSupervisor->syncPermissions($combinedPerms->toArray());
            $this->roleData['shift_supervisor'] = $combinedPerms->count();
        }
    }

    private function seedCrossOrgRoles(): void
    {
        // Corporate Consultant (Multi-Organization Access)
        $corporateConsultant = Role::firstOrCreate(['name' => 'corporate_consultant', 'guard_name' => 'admin']);
        $corporateConsultantPerms = collect([
            'organizations.read', 'branches.read', 'branches.view_reports',
            'reports.view_sales', 'reports.view_inventory', 'reports.view_financial',
            'finance.view_revenue', 'users.read'
        ]);
        $corporateConsultant->syncPermissions($corporateConsultantPerms->toArray());
        $this->roleData['corporate_consultant'] = $corporateConsultantPerms->count();
        
        // Quality Assurance Inspector
        $qaInspector = Role::firstOrCreate(['name' => 'qa_inspector', 'guard_name' => 'admin']);
        $qaInspectorPerms = collect([
            'branches.read', 'kitchen.view_orders', 'kitchen.quality_control',
            'inventory.read', 'menus.read', 'reports.view_inventory'
        ]);
        $qaInspector->syncPermissions($qaInspectorPerms->toArray());
        $this->roleData['qa_inspector'] = $qaInspectorPerms->count();
        
        // Training Coordinator
        $trainingCoordinator = Role::firstOrCreate(['name' => 'training_coordinator', 'guard_name' => 'admin']);
        $trainingCoordinatorPerms = collect([
            'branches.read', 'users.read', 'users.assign_roles',
            'reports.view_staff', 'organizations.read'
        ]);
        $trainingCoordinator->syncPermissions($trainingCoordinatorPerms->toArray());
        $this->roleData['training_coordinator'] = $trainingCoordinatorPerms->count();
    }

    private function seedEmergencyRoles(): void
    {
        // Emergency Manager (Full Access During Crisis)
        $emergencyManager = Role::firstOrCreate(['name' => 'emergency_manager', 'guard_name' => 'admin']);
        $emergencyManagerPerms = collect([
            'branches.read', 'branches.update', 'orders.read', 'orders.update', 'orders.cancel',
            'reservations.read', 'reservations.update', 'reservations.cancel',
            'kitchen.view_orders', 'kitchen.update_status', 'inventory.read',
            'users.read', 'system.view_logs'
        ]);
        $emergencyManager->syncPermissions($emergencyManagerPerms->toArray());
        $this->roleData['emergency_manager'] = $emergencyManagerPerms->count();
        
        // System Recovery Specialist
        $recoverySpecialist = Role::firstOrCreate(['name' => 'system_recovery', 'guard_name' => 'admin']);
        $recoverySpecialistPerms = collect([
            'system.view_logs', 'system.backup_database', 'system.restore_database',
            'system.manage_settings', 'users.read', 'organizations.read', 'branches.read'
        ]);
        $recoverySpecialist->syncPermissions($recoverySpecialistPerms->toArray());
        $this->roleData['system_recovery'] = $recoverySpecialistPerms->count();
        
        // Override Administrator
        $overrideAdmin = Role::firstOrCreate(['name' => 'override_administrator', 'guard_name' => 'admin']);
        $overrideAdminPerms = collect([
            'orders.cancel', 'orders.refund', 'reservations.cancel',
            'inventory.adjust_stock', 'finance.process_refunds',
            'users.suspend_accounts', 'users.activate_accounts'
        ]);
        $overrideAdmin->syncPermissions($overrideAdminPerms->toArray());
        $this->roleData['override_administrator'] = $overrideAdminPerms->count();
        
        // Maintenance Mode Administrator
        $maintenanceAdmin = Role::firstOrCreate(['name' => 'maintenance_admin', 'guard_name' => 'admin']);
        $maintenanceAdminPerms = collect([
            'system.manage_settings', 'system.update_system', 'system.view_logs',
            'system.backup_database', 'users.read'
        ]);
        $maintenanceAdmin->syncPermissions($maintenanceAdminPerms->toArray());
        $this->roleData['maintenance_admin'] = $maintenanceAdminPerms->count();
    }

    private function displayRoleSummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 EXHAUSTIVE ROLE & PERMISSION SEEDING SUMMARY');
        $this->command->info('═══════════════════════════════════════════════════════');
        
        $totalRoles = Role::where('guard_name', 'admin')->count();
        $totalPermissions = Permission::where('guard_name', 'admin')->count();
        $totalAdmins = Admin::count();
        
        $this->command->info("👑 Total Roles Created: {$totalRoles}");
        $this->command->info("🔑 Total Permissions Created: {$totalPermissions}");
        $this->command->info("👤 Total Admin Users: {$totalAdmins}");
        
        $this->command->newLine();
        $this->command->info('🎯 ROLE BREAKDOWN:');
        
        foreach ($this->roleData as $role => $permissionCount) {
            $roleName = ucwords(str_replace('_', ' ', $role));
            $this->command->info(sprintf('  %-30s: %d permissions', $roleName, $permissionCount));
        }
        
        $this->command->newLine();
        $this->command->info('📈 PERMISSION CATEGORIES:');
        
        foreach ($this->permissionHierarchy as $category => $permissions) {
            $categoryName = ucwords($category);
            $count = count($permissions);
            $this->command->info(sprintf('  %-20s: %d permissions', $categoryName, $count));
        }
        
        $this->command->newLine();
        $this->command->info('🔐 ROLE ASSIGNMENTS:');
        
        $roleAssignments = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->selectRaw('roles.name as role_name, COUNT(*) as user_count')
            ->groupBy('roles.name')
            ->get();
            
        foreach ($roleAssignments as $assignment) {
            $this->command->info(sprintf('  %-30s: %d users', ucwords(str_replace('_', ' ', $assignment->role_name)), $assignment->user_count));
        }
        
        $this->command->newLine();
        $this->command->info('✅ All role and permission scenarios have been comprehensively seeded!');
        $this->command->info('🔍 Scenarios include: hierarchical roles, department-specific permissions,');
        $this->command->info('    temporary assignments, boundary testing, multi-role users,');
        $this->command->info('    inheritance patterns, cross-organization access, and emergency roles.');
    }
}
