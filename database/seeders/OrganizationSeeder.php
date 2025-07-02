<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Seeding organization data...');

        $this->createOrganizations();
        
        $this->command->info('✅ Organization data seeded successfully');
    }

    /**
     * Create test organizations
     */
    private function createOrganizations()
    {
        $organizations = [
            [
                'name' => 'Main Restaurant Group',
                'email' => 'admin@mainrestaurant.com',
                'phone' => '+1234567890',
                'address' => '123 Business District, Main City, State 12345',
                'contact_person' => 'John Administrator',
                'contact_person_designation' => 'CEO',
                'contact_person_phone' => '+1234567890',
                'password' => bcrypt('password123'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Demo Restaurant Chain',
                'email' => 'admin@demorestaurant.com',
                'phone' => '+1234567891',
                'address' => '456 Commercial Avenue, Demo City, State 12346',
                'contact_person' => 'Sarah Manager',
                'contact_person_designation' => 'General Manager',
                'contact_person_phone' => '+1234567891',
                'password' => bcrypt('demo123'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Family Bistro',
                'email' => 'owner@familybistro.com',
                'phone' => '+1234567892',
                'address' => '789 Local Street, Small Town, State 12347',
                'contact_person' => 'Mike Family',
                'contact_person_designation' => 'Owner',
                'contact_person_phone' => '+1234567892',
                'password' => bcrypt('family123'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($organizations as $orgData) {
            Organization::create($orgData);
        }
    }
}
