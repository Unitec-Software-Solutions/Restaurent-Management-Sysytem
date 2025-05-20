<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::first();
        Admin::firstOrCreate(
            ['email' => 'admin@rms.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'branch_id' => '1',
            ]
        );
    }
}
