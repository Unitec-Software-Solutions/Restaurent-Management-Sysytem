<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if we should run basic or comprehensive seeding
        // For migrate:fresh --seed, always run comprehensive seeding in development
        $runComprehensive = App::environment(['local', 'testing']) || 
                           (isset($this->command) && $this->command->option('comprehensive')) ||
                           config('app.debug', false) ||
                           true; // Always run comprehensive for now

        if ($runComprehensive) {
            $this->runComprehensiveSeeding();
        } else {
            $this->runBasicSeeding();
        }
    }

    /**
     * Run basic seeding for production-like environments
     */
    private function runBasicSeeding(): void
    {
        $this->command->info('🔧 Running basic database seeding...');
        
        $this->call([
            // === FOUNDATION SEEDERS (Core system setup) ===
            SystemSettingSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            ModuleSeeder::class,
            
            // === ORGANIZATION STRUCTURE ===
            OrganizationSeeder::class,
            BranchSeeder::class,
            
            // === BASIC USER MANAGEMENT ===
            UserSeeder::class,
            AdminSeeder::class,
            CustomRoleSeeder::class,
            
            // === BASIC INVENTORY SETUP ===
            ItemCategorySeeder::class,
            SupplierSeeder::class,
            
            // === BASIC MENU SETUP ===
            MenuCategorySeeder::class,
            
            // === SYSTEM CONFIGURATIONS ===
            RestaurantConfigSeeder::class,
            SubscriptionPlanSeeder::class,
        ]);
        
        // === STANDARDIZE SUPER ADMIN CREDENTIALS ===
        // Ensure consistent super admin login after all seeders run
        $this->standardizeSuperAdmin();
        
        $this->command->info('✅ Basic seeding completed');
    }

    /**
     * Run comprehensive seeding for development/testing environments
     */
    private function runComprehensiveSeeding(): void
    {
        $this->command->info('🚀 Running comprehensive database seeding...');
        
        $this->call([
            // === FOUNDATION SEEDERS (Core system setup) ===
            SystemSettingSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            ModuleSeeder::class,
            
            // === ORGANIZATION STRUCTURE ===
            OrganizationSeeder::class,
            BranchSeeder::class,
            
            // === USER MANAGEMENT ===
            UserSeeder::class,
            AdminSeeder::class,
            EmployeeSeeder::class,
            StaffSeeder::class,
            CustomRoleSeeder::class,
            
            // === INVENTORY & ITEMS ===
            ItemCategorySeeder::class,
            ItemMasterSeeder::class,
            InventoryItemSeeder::class,
            SupplierSeeder::class,
            
            // === MENU MANAGEMENT ===
            MenuCategorySeeder::class,
            MenuSeeder::class,
            MenuItemSeeder::class,
            
            // === KITCHEN & PRODUCTION ===
            KitchenStationSeeder::class,
            RecipeSeeder::class,
            ProductionRecipeSeeder::class,
            ProductionRecipeDetailSeeder::class,
            
            // === CUSTOMER & RESERVATIONS ===
            CustomerSeeder::class,
            TableSeeder::class,
            ReservationSeeder::class,
            
            // === ORDERS & PAYMENTS ===
            OrderSeeder::class,
            OrderItemSeeder::class,
            OrderStatusStateMachineSeeder::class,
            KotSeeder::class,
            KotItemSeeder::class,
            PaymentSeeder::class,
            PaymentAllocationSeeder::class,
            BillSeeder::class,
            
            // === PURCHASING & INVENTORY TRANSACTIONS ===
            PurchaseOrderSeeder::class,
            PurchaseOrderItemSeeder::class,
            GrnMasterSeeder::class,
            GrnItemSeeder::class,
            ItemTransactionSeeder::class,
            StockReservationSeeder::class,
            
            // === PRODUCTION MANAGEMENT ===
            ProductionRequestMasterSeeder::class,
            ProductionRequestItemSeeder::class,
            ProductionOrderSeeder::class,
            ProductionOrderItemSeeder::class,
            ProductionOrderIngredientSeeder::class,
            ProductionSessionSeeder::class,
            
            // === GOODS TRANSFER ===
            GoodsTransferNoteSeeder::class,
            GoodsTransferItemSeeder::class,
            
            // === SUPPLIER PAYMENTS ===
            SupplierPaymentMasterSeeder::class,
            SupplierPaymentDetailSeeder::class,
            
            // === STAFF MANAGEMENT ===
            ShiftSeeder::class,
            ShiftAssignmentSeeder::class,
            
            // === SUBSCRIPTION & BILLING ===
            SubscriptionPlanSeeder::class,
            SubscriptionSeeder::class,
            
            // === SYSTEM CONFIGURATIONS ===
            RestaurantConfigSeeder::class,
            NotificationProviderSeeder::class,
            ReservationCancellationMailSeeder::class,
            
            // === AUDIT & LOGGING ===
            AuditLogSeeder::class,
            
            // === COMPREHENSIVE TEST SCENARIOS ===
            ProductionScenarioSeeder::class,
            ReportingScenarioSeeder::class,
            
            // === SIMULATION SEEDERS (Development & Testing) ===
            // These provide realistic data scenarios for testing
            ReservationLifecycleSeeder::class,
            OrderSimulationSeeder::class,
            GuestActivitySeeder::class,
            
            // === AUTOMATED COMPREHENSIVE TEST DATA ===
            // Note: ComprehensiveTestSeeder includes all advanced automation seeders
            // This provides comprehensive test data for development and testing
            ComprehensiveTestSeeder::class,
        ]);
        
        // === STANDARDIZE SUPER ADMIN CREDENTIALS ===
        // Ensure consistent super admin login after all seeders run
        $this->standardizeSuperAdmin();
        
        $this->command->info('✅ Comprehensive seeding completed');
    }
    
    /**
     * Standardize super admin credentials for consistent login
     */
    private function standardizeSuperAdmin(): void
    {
        $this->command->info('🔧 Standardizing Super Admin credentials...');
        
        // Ensure consistent super admin credentials
        $email = 'superadmin@rms.com';
        $password = 'SuperAdmin123!';
        $name = 'Super Administrator';
        
        // Create or update super admin (NO organization - system level admin)
        $admin = \App\Models\Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'phone' => '+94 11 000 0000',
                'job_title' => 'System Administrator',
                'organization_id' => null, // Super admin belongs to no organization
                'is_super_admin' => true,
                'is_active' => true,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        
        // Ensure Super Admin role exists and assign all permissions
        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'admin'
        ]);
        
        $allPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->get();
        $superAdminRole->syncPermissions($allPermissions);
        
        // Assign role to admin
        $admin->syncRoles([$superAdminRole]);
        
        $this->command->info('✅ Super Admin standardized: ' . $email . ' / ' . $password . ' (System Level)');
    }
}
