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
            ItemTransactionSeeder::class,
            AdminSeeder::class,
            ReservationSeeder::class,

        ]);
    }
}
