<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = [
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Farm Fresh Produce',
                'contact_person' => 'John Farmer',
                'phone' => '(555) 111-2222',
                'email' => 'john@farmfresh.com',
                'address' => '1234 Rural Road, Farmland, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => true,
                'vat_registration_no' => 'VAT-'. Str::upper(Str::random(9)),
            ],
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Quality Meats & Seafood',
                'contact_person' => 'Mary Butcher',
                'phone' => '(555) 222-3333',
                'email' => 'mary@qualitymeats.com',
                'address' => '567 Harbor Way, Portside, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => true,
                'vat_registration_no' => 'VAT-'. Str::upper(Str::random(9)),
            ],
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Beverage Distributors Inc.',
                'contact_person' => 'Robert Drinks',
                'phone' => '(555) 333-4444',
                'email' => 'robert@beveragedist.com',
                'address' => '890 Brewery Lane, Distillery District, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => false,
                'vat_registration_no' => null,
            ],
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Restaurant Equipment Suppliers',
                'contact_person' => 'Susan Tools',
                'phone' => '(555) 444-5555',
                'email' => 'susan@resequip.com',
                'address' => '246 Industrial Park, Manufacturing Zone, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => true,
                'vat_registration_no' => 'VAT-'. Str::upper(Str::random(9)),
            ],
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Packaging Solutions',
                'contact_person' => 'David Wrapper',
                'phone' => '(555) 555-6666',
                'email' => 'david@packagingsol.com',
                'address' => '135 Box Street, Container City, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => false,
                'vat_registration_no' => null,
            ],
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Cleaning & Sanitation Co.',
                'contact_person' => 'Jennifer Clean',
                'phone' => '(555) 666-7777',
                'email' => 'jennifer@cleanco.com',
                'address' => '579 Sparkle Avenue, Sanitaryville, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => true,
                'vat_registration_no' => 'VAT-'. Str::upper(Str::random(9)),
            ],
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Bakery Ingredients Supply',
                'contact_person' => 'Michael Baker',
                'phone' => '(555) 777-8888',
                'email' => 'michael@bakeryingredients.com',
                'address' => '802 Flour Mill Road, Breadtown, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => false,
                'vat_registration_no' => null,
            ],
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Dairy Delivery LLC',
                'contact_person' => 'Sarah Milker',
                'phone' => '(555) 888-9999',
                'email' => 'sarah@dairydelivery.com',
                'address' => '463 Pasture Lane, Creamery Hills, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => true,
                'vat_registration_no' => 'VAT-'. Str::upper(Str::random(9)),
            ],
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Spice & Seasoning Traders',
                'contact_person' => 'Tom Spicy',
                'phone' => '(555) 999-0000',
                'email' => 'tom@spicetraders.com',
                'address' => '751 Flavor Street, Aromaville, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => false,
                'vat_registration_no' => null,
            ],
            [
                'supplier_id' => 'SUP-' . Str::upper(Str::random(6)),
                'name' => 'Office & Paper Supply Co.',
                'contact_person' => 'Linda Stapler',
                'phone' => '(555) 000-1111',
                'email' => 'linda@officesupply.com',
                'address' => '369 Document Drive, Papertown, State 54321',
                'is_inactive' => false,
                'is_active' => true,
                'has_vat_registration' => true,
                'vat_registration_no' => 'VAT-'. Str::upper(Str::random(9)),
            ],
        ];

        $createdCount = 0;
        
        foreach ($suppliers as $supplier) {
            // Check if a supplier with this name already exists
            $exists = Supplier::where('name', $supplier['name'])->exists();
            
            if (!$exists) {
                Supplier::create($supplier);
                $createdCount++;
            }
        }

        $totalSuppliers = count($suppliers);
        $skippedCount = $totalSuppliers - $createdCount;
        
        $this->command->info("Suppliers seeding completed!");
        $this->command->info("Created {$createdCount} new suppliers.");
        $this->command->info("Skipped {$skippedCount} existing suppliers.");
    }
}