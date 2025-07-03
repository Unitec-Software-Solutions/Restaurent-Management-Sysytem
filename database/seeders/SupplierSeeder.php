<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'id' => 1,
                'organization_id' => 1,
                'supplier_id' => 'SUP-001',
                'name' => 'Supplier 1',
                'contact_person' => 'John Doe',
                'phone' => '123-456-7890',
                'email' => 'supplier1@example.com',
                'address' => '123 Main St',
                'has_vat_registration' => true,
                'vat_registration_no' => 'VAT-12345',
                'is_active' => true,
                'is_inactive' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'supplier_type' => 'regular'
            ],
            [
                'id' => 2,
                'organization_id' => 1,
                'supplier_id' => 'SUP-002',
                'name' => 'Supplier 2',
                'contact_person' => 'Jane Smith',
                'phone' => '098-765-4321',
                'email' => 'supplier2@example.com',
                'address' => '456 Elm St',
                'has_vat_registration' => false,
                'vat_registration_no' => null,
                'is_active' => true,
                'is_inactive' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'supplier_type' => 'wholesale'
            ]
        ]);
    }
}
