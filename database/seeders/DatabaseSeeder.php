<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
        ]);
    }
}
