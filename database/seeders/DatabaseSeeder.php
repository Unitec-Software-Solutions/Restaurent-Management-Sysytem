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
            AuditLogSeeder::class,
            EmployeeSeeder::class,
            GrnItemSeeder::class,
            InventoryItemSeeder::class,
            ItemMasterSeeder::class,
            KitchenStationSeeder::class,
            KotItemSeeder::class,
            MenuSeeder::class,
            MenuItemSeeder::class,
            NotificationProviderSeeder::class,
            OrderSeeder::class,
            OrderStatusStateMachineSeeder::class,
            PaymentSeeder::class,
            PaymentAllocationSeeder::class,
            ProductionOrderSeeder::class,
            ProductionOrderIngredientSeeder::class,
            ProductionRecipeSeeder::class,
            ProductionRequestItemSeeder::class,
            ProductionSessionSeeder::class,
            RecipeSeeder::class,
            ReservationCancellationMailSeeder::class,
            ShiftSeeder::class,
            StockReservationSeeder::class,
            SubscriptionPlanSeeder::class,
            SupplierPaymentDetailSeeder::class,
            SystemSettingSeeder::class,
            UserSeeder::class,
            TableSeeder::class,
            SupplierPaymentMasterSeeder::class,
            SupplierSeeder::class,
            SubscriptionSeeder::class,
            StaffSeeder::class,
            ShiftAssignmentSeeder::class,
            RoleSeeder::class,
            RestaurantConfigSeeder::class,
            ReservationSeeder::class,
            PurchaseOrderItemSeeder::class,
            PurchaseOrderSeeder::class,
            ProductionRequestMasterSeeder::class,
            ProductionRecipeDetailSeeder::class,
            ProductionOrderItemSeeder::class,
            PermissionSeeder::class,
            OrderItemSeeder::class,
            OrganizationSeeder::class,
            ModuleSeeder::class,
            MenuCategorySeeder::class,
            KotSeeder::class,
            GrnMasterSeeder::class,
            ItemTransactionSeeder::class,
            ItemCategorySeeder::class,
            GoodsTransferNoteSeeder::class,
            GoodsTransferItemSeeder::class,
            CustomRoleSeeder::class,
            CustomerSeeder::class,
            BranchSeeder::class,
            BillSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
