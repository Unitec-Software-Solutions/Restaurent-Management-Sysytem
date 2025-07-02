<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding customer data...');

        $this->createCustomerProfiles();
        
        $this->command->info('✅ Customer data seeded successfully');
    }

    /**
     * Create diverse customer profiles for testing
     */
    private function createCustomerProfiles()
    {
        $customers = [
            // Regular customers
            [
                'phone' => '+1234567890',
                'name' => 'John Smith',
                'email' => 'john.smith@email.com',
                'preferred_contact' => 'email',
                'date_of_birth' => '1985-03-15',
                'anniversary_date' => '2010-06-20',
                'dietary_preferences' => json_encode(['vegetarian']),
                'special_notes' => 'Prefers quiet tables, allergic to nuts',
                'is_active' => true,
                'last_visit_date' => now()->subDays(5),
                'total_orders' => 25,
                'total_spent' => 875.50,
                'loyalty_points' => 125,
            ],
            [
                'phone' => '+1234567891',
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@email.com',
                'preferred_contact' => 'sms',
                'date_of_birth' => '1990-07-22',
                'anniversary_date' => '2015-09-10',
                'dietary_preferences' => json_encode(['gluten-free', 'vegan']),
                'special_notes' => 'VIP customer, prefers plant-based menu',
                'is_active' => true,
                'last_visit_date' => now()->subDays(2),
                'total_orders' => 42,
                'total_spent' => 1250.75,
                'loyalty_points' => 250,
            ],
            [
                'phone' => '+1234567892',
                'name' => 'Michael Brown',
                'email' => 'm.brown@email.com',
                'preferred_contact' => 'email',
                'date_of_birth' => '1978-12-05',
                'anniversary_date' => '2005-04-18',
                'dietary_preferences' => json_encode(['none']),
                'special_notes' => 'Business customer, often orders for groups',
                'is_active' => true,
                'last_visit_date' => now()->subDays(1),
                'total_orders' => 18,
                'total_spent' => 1680.00,
                'loyalty_points' => 168,
            ],
            [
                'phone' => '+1234567893',
                'name' => 'Emily Davis',
                'email' => 'emily.davis@email.com',
                'preferred_contact' => 'email',
                'date_of_birth' => '1995-01-30',
                'dietary_preferences' => json_encode(['pescatarian']),
                'special_notes' => 'Young professional, prefers quick service',
                'is_active' => true,
                'last_visit_date' => now()->subDays(7),
                'total_orders' => 12,
                'total_spent' => 420.25,
                'loyalty_points' => 42,
            ],
            [
                'phone' => '+1234567894',
                'name' => 'Robert Wilson',
                'email' => 'robert.wilson@email.com',
                'preferred_contact' => 'sms',
                'date_of_birth' => '1965-08-14',
                'anniversary_date' => '1990-11-25',
                'dietary_preferences' => json_encode(['low-sodium']),
                'special_notes' => 'Senior customer, dietary restrictions for health',
                'is_active' => true,
                'last_visit_date' => now()->subDays(10),
                'total_orders' => 35,
                'total_spent' => 980.40,
                'loyalty_points' => 98,
            ],
            
            // Frequent customers
            [
                'phone' => '+1234567895',
                'name' => 'Lisa Chen',
                'email' => 'lisa.chen@email.com',
                'preferred_contact' => 'email',
                'date_of_birth' => '1988-04-12',
                'dietary_preferences' => json_encode(['halal']),
                'special_notes' => 'Frequent customer, knows staff by name',
                'is_active' => true,
                'last_visit_date' => now()->subHours(6),
                'total_orders' => 78,
                'total_spent' => 2340.50,
                'loyalty_points' => 345,
            ],
            [
                'phone' => '+1234567896',
                'name' => 'David Miller',
                'email' => 'david.miller@email.com',
                'preferred_contact' => 'sms',
                'date_of_birth' => '1982-09-27',
                'anniversary_date' => '2008-02-14',
                'dietary_preferences' => json_encode(['keto']),
                'special_notes' => 'Regular lunch customer, works nearby',
                'is_active' => true,
                'last_visit_date' => now()->subHours(4),
                'total_orders' => 65,
                'total_spent' => 1950.75,
                'loyalty_points' => 295,
            ],
            
            // Occasional customers
            [
                'phone' => '+1234567897',
                'name' => 'Jennifer Taylor',
                'email' => 'jennifer.taylor@email.com',
                'preferred_contact' => 'email',
                'date_of_birth' => '1975-11-08',
                'anniversary_date' => '2000-07-03',
                'dietary_preferences' => json_encode(['vegetarian']),
                'special_notes' => 'Celebrates special occasions here',
                'is_active' => true,
                'last_visit_date' => now()->subDays(45),
                'total_orders' => 8,
                'total_spent' => 560.00,
                'loyalty_points' => 56,
            ],
            [
                'phone' => '+1234567898',
                'name' => 'James Anderson',
                'email' => 'james.anderson@email.com',
                'preferred_contact' => 'sms',
                'date_of_birth' => '1992-06-19',
                'dietary_preferences' => json_encode(['none']),
                'special_notes' => 'Tourist, visited during city trip',
                'is_active' => true,
                'last_visit_date' => now()->subDays(90),
                'total_orders' => 3,
                'total_spent' => 125.50,
                'loyalty_points' => 12,
            ],
            
            // Inactive customer
            [
                'phone' => '+1234567899',
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@email.com',
                'preferred_contact' => 'email',
                'date_of_birth' => '1980-02-28',
                'dietary_preferences' => json_encode(['none']),
                'special_notes' => 'Moved away, account inactive',
                'is_active' => false,
                'last_visit_date' => now()->subDays(180),
                'total_orders' => 15,
                'total_spent' => 450.75,
                'loyalty_points' => 45,
            ],
            
            // High-value customer
            [
                'phone' => '+1234567800',
                'name' => 'William Thompson',
                'email' => 'william.thompson@email.com',
                'preferred_contact' => 'sms',
                'date_of_birth' => '1970-10-16',
                'anniversary_date' => '1995-12-22',
                'dietary_preferences' => json_encode(['wine_pairing']),
                'special_notes' => 'VIP customer, corporate account, wine enthusiast',
                'is_active' => true,
                'last_visit_date' => now()->subDays(3),
                'total_orders' => 95,
                'total_spent' => 4750.25,
                'loyalty_points' => 475,
            ],
            
            // Family customers
            [
                'phone' => '+1234567801',
                'name' => 'The Johnson Family',
                'email' => 'johnson.family@email.com',
                'preferred_contact' => 'email',
                'date_of_birth' => '1985-05-20',
                'anniversary_date' => '2012-08-15',
                'dietary_preferences' => json_encode(['kid_friendly', 'no_spicy']),
                'special_notes' => 'Family with children, needs high chairs',
                'is_active' => true,
                'last_visit_date' => now()->subDays(12),
                'total_orders' => 28,
                'total_spent' => 1120.00,
                'loyalty_points' => 112,
            ],
        ];

        foreach ($customers as $customerData) {
            try {
                DB::table('customers')->updateOrInsert(
                    ['phone' => $customerData['phone']],
                    array_merge($customerData, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            } catch (\Exception $e) {
                $this->command->warn("Could not create customer {$customerData['name']}: " . $e->getMessage());
                continue;
            }
        }
    }
}
