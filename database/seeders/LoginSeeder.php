<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user  admin@restaurant.com / admin123
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('admin123'),
            'phone_number' => '1234567890',
            'user_type' => 'admin',
            'is_registered' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Manager user manager@restaurant.com / manager123
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@restaurant.com',
            'password' => Hash::make('manager123'),
            'phone_number' => '2345678901',
            'user_type' => 'manager',
            'is_registered' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Chef user chef@restaurant.com / chef123
        User::create([
            'name' => 'Chef User',
            'email' => 'chef@restaurant.com',
            'password' => Hash::make('chef123'),
            'phone_number' => '3456789012',
            'user_type' => 'chef',
            'is_registered' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Waiter user waiter@restaurant.com / waiter123
        User::create([
            'name' => 'Waiter User',
            'email' => 'waiter@restaurant.com',
            'password' => Hash::make('waiter123'),
            'phone_number' => '4567890123',
            'user_type' => 'waiter',
            'is_registered' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Cashier user cashier@restaurant.com / cashier123
        User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@restaurant.com',
            'password' => Hash::make('cashier123'),
            'phone_number' => '5678901234',
            'user_type' => 'cashier',
            'is_registered' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Customer user customer@example.com / customer123
        User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('customer123'),
            'phone_number' => '6789012345',
            'user_type' => 'customer',
            'is_registered' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
} 