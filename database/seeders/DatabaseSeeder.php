<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\MinimalSystemSeeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database - Minimal Setup
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@rms.com',
            'password' => Hash::make('SuperAdmin123!'),
            'is_super_admin' => true,
            'is_active' => true,
        ]);
    }
}
