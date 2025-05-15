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
        User::firstOrCreate(
            ['email' => 'admin@restaurant.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'phone_number' => '1234567890',
                'user_type' => 'admin',
                'branch_id' => 1,
                'is_registered' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Manager user manager@restaurant.com / manager123
        User::firstOrCreate(
            ['email' => 'manager@restaurant.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('manager123'),
                'phone_number' => '2345678901',
                'user_type' => 'manager',
                'is_registered' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Chef user chef@restaurant.com / chef123
        User::firstOrCreate(
            ['email' => 'chef@restaurant.com'],
            [
                'name' => 'Chef User',
                'password' => Hash::make('chef123'),
                'phone_number' => '3456789012',
                'user_type' => 'chef',
                'is_registered' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Waiter user waiter@restaurant.com / waiter123
        User::firstOrCreate(
            ['email' => 'waiter@restaurant.com'],
            [
                'name' => 'Waiter User',
                'password' => Hash::make('waiter123'),
                'phone_number' => '4567890123',
                'user_type' => 'waiter',
                'is_registered' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Cashier user cashier@restaurant.com / cashier123
        User::firstOrCreate(
            ['email' => 'cashier@restaurant.com'],
            [
                'name' => 'Cashier User',
                'password' => Hash::make('cashier123'),
                'phone_number' => '5678901234',
                'user_type' => 'cashier',
                'is_registered' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Customer user customer@example.com / customer123
        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('customer123'),
                'phone_number' => '6789012345',
                'user_type' => 'customer',
                'is_registered' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}