<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('employee')->insert([
            ['emp_id' => 'EMP001', 'name' => 'John Doe'],
            ['emp_id' => 'EMP002', 'name' => 'Jane Smith'],
            ['emp_id' => 'EMP003', 'name' => 'Alice Johnson'],
            ['emp_id' => 'EMP004', 'name' => 'Bob Williams'],
        ]);
    }
}
