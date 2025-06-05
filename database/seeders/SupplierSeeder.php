<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Create suppliers for each organization
        for ($orgId = 1; $orgId <= 5; $orgId++) {
            // Create suppliers by category
            $this->createSuppliersByCategory($faker, $orgId, ' ', 3);
            $this->createSuppliersByCategory($faker, $orgId, 'Beverage Supplier', 2);
            $this->createSuppliersByCategory($faker, $orgId, 'Equipment Supplier', 2);
            $this->createSuppliersByCategory($faker, $orgId, 'Packaging Supplier', 1);
        }

        $this->command->info('  ✅ Suppliers seeded successfully!');
    }

    private function createSuppliersByCategory($faker, $orgId, $category, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $name = $category . ' ' . $faker->company;
            $hasVat = $faker->boolean(70);
            
            Supplier::create([
                'organization_id' => $orgId,
                'supplier_id' => 'SUP-' . strtoupper(Str::random(6)),
                'name' => $name,
                'contact_person' => $faker->name,
                'phone' => '+94 ' . $faker->randomElement(['71','72','75','76','77','78']) . ' ' . $faker->numberBetween(1000000, 9999999),
                'email' => $faker->companyEmail,
                'address' => $faker->address,
                'has_vat_registration' => $hasVat,
                'vat_registration_no' => $hasVat ? 'VAT' . $faker->numerify('#########') : null,
                'is_active' => true,
                'is_inactive' => false
            ]);
        }
    }
}