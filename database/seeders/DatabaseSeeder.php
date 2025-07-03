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
        $runComprehensive = App::environment(['local', 'testing']) || 
                           (isset($this->command) && $this->command->option('comprehensive')) ||
                           config('app.debug', false);

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
            
            // === AUTOMATED COMPREHENSIVE TEST DATA ===
            // Note: ComprehensiveTestSeeder includes all advanced automation seeders
            // This provides comprehensive test data for development and testing
            ComprehensiveTestSeeder::class,
        ]);
        
        $this->command->info('✅ Comprehensive seeding completed');
    }
}
