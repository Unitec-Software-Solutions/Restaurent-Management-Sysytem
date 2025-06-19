<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'superadmin@rms.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Always hash the password!
                'is_super_admin' => true, // or 'is_super_admin' if that's your column name
            ]
        );
    }
}
