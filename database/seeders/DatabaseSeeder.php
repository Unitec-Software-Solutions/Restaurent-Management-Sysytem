<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('TRUNCATE tables RESTART IDENTITY CASCADE;');

        $this->call([
            OrganizationSeeder::class,
            BranchSeeder::class,
            TableSeeder::class,
            LoginSeeder::class,
            SupplierSeeder::class,
            ItemCategorySeeder::class,
            ItemMasterSeeder::class,
            AdminSeeder::class,
            ReservationSeeder::class,
            // PurchaseOrderSeeder::class,
            // GRNSeeder::class,
            // SupplierPaymentSeeder::class,
            ItemTransactionSeeder::class,
            //EmployeeSeeder::class,
        ]);
    }
}
