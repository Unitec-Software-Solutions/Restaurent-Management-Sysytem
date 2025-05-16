<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Test User
        /*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */

        $this->call([
            OrganizationSeeder::class,
            AdminSeeder::class,
            BranchSeeder::class,
            LoginSeeder::class,
            SupplierSeeder::class,
            ItemCategorySeeder::class,  // New Item Category Seeder
            ItemMasterSeeder::class,    // New Item Master Seeder
            // InventoryCategorySeeder::class,
            // InventoryItemSeeder::class,
            // InventoryStockSeeder::class,
            // InventoryTransactionSeeder::class,
            // MenuCategorySeeder::class,
            // MenuItemSeeder::class,
            // MenuRecipeSeeder::class,
            // PurchaseOrderSeeder::class,
            // PurchaseOrderItemSeeder::class,
            // GoodReceivedNoteSeeder::class,
            // GoodReceivedNoteItemSeeder::class,
        ]);
    }
}
