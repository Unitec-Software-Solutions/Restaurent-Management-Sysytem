<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StaffShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates comprehensive staff and shift scenarios including:
     * - Regular shifts with standard hours
     * - Overlapping shifts for shift changes
     * - Split shifts for part-time staff
     * - Holiday and weekend coverage
     * - Emergency shift coverage scenarios
     * - Peak hour double-staffing
     * - Edge cases like midnight shifts and early morning prep
     */
    public function run()
    {
        $organizations = Organization::all();
        $branches = Branch::all();
        
        if ($organizations->isEmpty() || $branches->isEmpty()) {
            $this->command->warn('Organizations and branches must exist before seeding staff shifts');
            return;
        }
        
        // Staff roles and their typical schedules
        $staffRoles = [
            'head_chef' => ['hours_per_week' => 50, 'hourly_rate' => 28.50],
            'sous_chef' => ['hours_per_week' => 45, 'hourly_rate' => 22.00],
            'line_cook' => ['hours_per_week' => 40, 'hourly_rate' => 16.50],
            'prep_cook' => ['hours_per_week' => 35, 'hourly_rate' => 14.75],
            'server' => ['hours_per_week' => 30, 'hourly_rate' => 12.00],
            'host' => ['hours_per_week' => 25, 'hourly_rate' => 13.50],
            'busser' => ['hours_per_week' => 20, 'hourly_rate' => 11.00],
            'dishwasher' => ['hours_per_week' => 30, 'hourly_rate' => 12.50],
            'manager' => ['hours_per_week' => 50, 'hourly_rate' => 35.00],
            'bartender' => ['hours_per_week' => 35, 'hourly_rate' => 15.00],
        ];
        
        $employees = [];
        $shifts = [];
        
        foreach ($branches as $branch) {
            $this->command->info("Creating staff and shifts for branch: {$branch->name}");
            
            // Create employees for each role
            foreach ($staffRoles as $role => $config) {
                $count = $this->getEmployeeCount($role);
                
                for ($i = 1; $i <= $count; $i++) {
                    $employee = Employee::create([
                        'organization_id' => $branch->organization_id,
                        'branch_id' => $branch->id,
                        'employee_code' => strtoupper($role) . '_' . str_pad($branch->id, 2, '0', STR_PAD_LEFT) . '_' . str_pad($i, 3, '0', STR_PAD_LEFT),
                        'first_name' => $this->getFirstName(),
                        'last_name' => $this->getLastName(),
                        'email' => strtolower($role) . $i . '_branch' . $branch->id . '@restaurant.local',
                        'phone' => $this->generatePhoneNumber(),
                        'position' => ucwords(str_replace('_', ' ', $role)),
                        'department' => $this->getDepartment($role),
                        'hire_date' => Carbon::now()->subDays(rand(30, 365)),
                        'hourly_rate' => $config['hourly_rate'],
                        'status' => rand(1, 100) <= 95 ? 'active' : 'inactive', // 95% active
                        'emergency_contact_name' => $this->getFirstName() . ' ' . $this->getLastName(),
                        'emergency_contact_phone' => $this->generatePhoneNumber(),
                        'address' => $this->generateAddress(),
                        'date_of_birth' => Carbon::now()->subYears(rand(18, 65)),
                        'social_security_number' => $this->generateSSN(),
                        'bank_account_number' => 'ACC' . rand(100000, 999999),
                        'tax_id' => 'TAX' . rand(10000, 99999),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $employees[] = $employee;
                    
                    // Generate shifts for this employee
                    $shifts = array_merge($shifts, $this->generateShiftsForEmployee($employee, $role, $config));
                }
            }
        }
        
        // Bulk insert shifts for better performance
        if (!empty($shifts)) {
            DB::table('employee_shifts')->insert($shifts);
            $this->command->info('Created ' . count($shifts) . ' shifts for ' . count($employees) . ' employees');
        }
        
        // Create some edge case scenarios
        $this->createEdgeCaseShifts($employees);
        
        $this->command->info('Staff and shift seeding completed successfully');
    }
    
    /**
     * Get number of employees needed for each role
     */
    private function getEmployeeCount($role)
    {
        $counts = [
            'head_chef' => 1,
            'sous_chef' => 2,
            'line_cook' => 4,
            'prep_cook' => 3,
            'server' => 8,
            'host' => 3,
            'busser' => 4,
            'dishwasher' => 3,
            'manager' => 2,
            'bartender' => 2,
        ];
        
        return $counts[$role] ?? 1;
    }
    
    /**
     * Generate shifts for an employee based on their role
     */
    private function generateShiftsForEmployee($employee, $role, $config)
    {
        $shifts = [];
        $startDate = Carbon::now()->startOfWeek()->subWeeks(2); // Start 2 weeks ago
        $endDate = Carbon::now()->endOfWeek()->addWeeks(4); // Go 4 weeks into future
        
        $shiftPatterns = $this->getShiftPatterns($role);
        
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            foreach ($shiftPatterns as $pattern) {
                if ($this->shouldWorkDay($currentDate, $pattern, $role)) {
                    $shiftData = $this->createShiftData($employee, $currentDate, $pattern, $role);
                    if ($shiftData) {
                        $shifts[] = $shiftData;
                    }
                }
            }
            $currentDate->addDay();
        }
        
        return $shifts;
    }
    
    /**
     * Get shift patterns for different roles
     */
    private function getShiftPatterns($role)
    {
        $patterns = [
            'head_chef' => [
                ['start' => '06:00', 'end' => '15:00', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']],
                ['start' => '08:00', 'end' => '17:00', 'days' => ['Saturday', 'Sunday']],
            ],
            'sous_chef' => [
                ['start' => '07:00', 'end' => '16:00', 'days' => ['Monday', 'Wednesday', 'Friday']],
                ['start' => '14:00', 'end' => '23:00', 'days' => ['Tuesday', 'Thursday', 'Saturday', 'Sunday']],
            ],
            'line_cook' => [
                ['start' => '08:00', 'end' => '17:00', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']],
                ['start' => '15:00', 'end' => '24:00', 'days' => ['Friday', 'Saturday', 'Sunday']],
            ],
            'prep_cook' => [
                ['start' => '05:00', 'end' => '14:00', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']],
                ['start' => '06:00', 'end' => '12:00', 'days' => ['Saturday', 'Sunday']],
            ],
            'server' => [
                ['start' => '11:00', 'end' => '15:00', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']], // Lunch shift
                ['start' => '17:00', 'end' => '22:00', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday']], // Dinner shift
                ['start' => '17:00', 'end' => '23:00', 'days' => ['Friday', 'Saturday']], // Weekend dinner
                ['start' => '10:00', 'end' => '16:00', 'days' => ['Saturday', 'Sunday']], // Weekend lunch
            ],
            'host' => [
                ['start' => '10:30', 'end' => '15:30', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']],
                ['start' => '16:30', 'end' => '22:30', 'days' => ['Friday', 'Saturday', 'Sunday']],
            ],
            'busser' => [
                ['start' => '11:30', 'end' => '15:30', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']],
                ['start' => '17:30', 'end' => '22:30', 'days' => ['Friday', 'Saturday', 'Sunday']],
            ],
            'dishwasher' => [
                ['start' => '12:00', 'end' => '20:00', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday']],
                ['start' => '16:00', 'end' => '24:00', 'days' => ['Friday', 'Saturday', 'Sunday']],
            ],
            'manager' => [
                ['start' => '09:00', 'end' => '18:00', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']],
                ['start' => '12:00', 'end' => '21:00', 'days' => ['Saturday', 'Sunday']],
            ],
            'bartender' => [
                ['start' => '16:00', 'end' => '24:00', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday']],
                ['start' => '15:00', 'end' => '01:00', 'days' => ['Friday', 'Saturday', 'Sunday']],
            ],
        ];
        
        return $patterns[$role] ?? [['start' => '09:00', 'end' => '17:00', 'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']]];
    }
    
    /**
     * Determine if employee should work on given day
     */
    private function shouldWorkDay($date, $pattern, $role)
    {
        $dayName = $date->format('l');
        
        if (!in_array($dayName, $pattern['days'])) {
            return false;
        }
        
        // Add some randomness for sick days, vacations, etc.
        if (rand(1, 100) <= 5) { // 5% chance of not working scheduled day
            return false;
        }
        
        // Higher probability of working on weekends for service roles
        if (in_array($dayName, ['Saturday', 'Sunday']) && in_array($role, ['server', 'bartender', 'host'])) {
            return rand(1, 100) <= 90; // 90% chance
        }
        
        return true;
    }
    
    /**
     * Create shift data array
     */
    private function createShiftData($employee, $date, $pattern, $role)
    {
        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $pattern['start']);
        $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $pattern['end']);
        
        // Handle overnight shifts
        if ($endTime <= $startTime) {
            $endTime->addDay();
        }
        
        // Add some variation to start/end times (±15 minutes)
        $startTime->addMinutes(rand(-15, 15));
        $endTime->addMinutes(rand(-15, 15));
        
        // Calculate break time based on shift length
        $shiftHours = $startTime->diffInHours($endTime);
        $breakMinutes = $shiftHours >= 8 ? 60 : ($shiftHours >= 6 ? 45 : 30);
        
        return [
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'shift_date' => $date->format('Y-m-d'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'scheduled_hours' => round($startTime->diffInMinutes($endTime) / 60, 2),
            'actual_start_time' => $this->getActualTime($startTime),
            'actual_end_time' => $this->getActualTime($endTime),
            'break_minutes' => $breakMinutes,
            'status' => $this->getShiftStatus($date),
            'notes' => $this->getShiftNotes($role, $date),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
    /**
     * Get actual time with some variance from scheduled time
     */
    private function getActualTime($scheduledTime)
    {
        // 80% chance of being on time, 20% chance of being early/late
        if (rand(1, 100) <= 80) {
            return $scheduledTime->format('H:i:s');
        }
        
        // Early/late by 5-30 minutes
        $variance = rand(5, 30);
        $isLate = rand(1, 100) <= 60; // 60% chance of being late vs early
        
        $actualTime = $scheduledTime->copy();
        if ($isLate) {
            $actualTime->addMinutes($variance);
        } else {
            $actualTime->subMinutes($variance);
        }
        
        return $actualTime->format('H:i:s');
    }
    
    /**
     * Get shift status based on date
     */
    private function getShiftStatus($date)
    {
        if ($date->isFuture()) {
            return 'scheduled';
        }
        
        $statuses = ['completed', 'completed', 'completed', 'completed', 'no_show', 'sick_leave', 'early_departure'];
        $weights = [85, 85, 85, 85, 3, 5, 7]; // 85% completed, 3% no-show, 5% sick, 7% early departure
        
        return $this->weightedRandom($statuses, $weights);
    }
    
    /**
     * Generate shift notes
     */
    private function getShiftNotes($role, $date)
    {
        $notes = [
            'Regular shift',
            'Busy day - extra tables',
            'Training new employee',
            'Inventory count day',
            'Special event catering',
            'Deep cleaning scheduled',
            'Equipment maintenance',
            'VIP guest service',
            'Holiday rush preparation',
            'Menu testing session',
        ];
        
        // 30% chance of having notes
        if (rand(1, 100) <= 30) {
            return $notes[array_rand($notes)];
        }
        
        return null;
    }
    
    /**
     * Create edge case shifts for testing scenarios
     */
    private function createEdgeCaseShifts($employees)
    {
        $edgeCases = [];
        
        foreach ($employees as $employee) {
            // Overlapping shifts (shift change scenarios)
            $edgeCases[] = [
                'employee_id' => $employee->id,
                'branch_id' => $employee->branch_id,
                'shift_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
                'start_time' => '14:00:00',
                'end_time' => '16:00:00', // Overlap with next shift
                'scheduled_hours' => 2,
                'actual_start_time' => '14:00:00',
                'actual_end_time' => '16:15:00', // Ran over slightly
                'break_minutes' => 0,
                'status' => 'scheduled',
                'notes' => 'Shift overlap for training',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Split shifts
            $edgeCases[] = [
                'employee_id' => $employee->id,
                'branch_id' => $employee->branch_id,
                'shift_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'start_time' => '06:00:00',
                'end_time' => '10:00:00', // Morning prep
                'scheduled_hours' => 4,
                'actual_start_time' => '05:55:00',
                'actual_end_time' => '10:00:00',
                'break_minutes' => 15,
                'status' => 'scheduled',
                'notes' => 'Split shift - morning prep only',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $edgeCases[] = [
                'employee_id' => $employee->id,
                'branch_id' => $employee->branch_id,
                'shift_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'start_time' => '17:00:00',
                'end_time' => '22:00:00', // Evening service
                'scheduled_hours' => 5,
                'actual_start_time' => '17:00:00',
                'actual_end_time' => '22:30:00',
                'break_minutes' => 30,
                'status' => 'scheduled',
                'notes' => 'Split shift - evening service only',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Only create edge cases for first 5 employees to avoid too much data
            if (count($edgeCases) >= 15) break;
        }
        
        if (!empty($edgeCases)) {
            DB::table('employee_shifts')->insert($edgeCases);
            $this->command->info('Created ' . count($edgeCases) . ' edge case shifts');
        }
    }
    
    /**
     * Helper methods for generating realistic data
     */
    private function getFirstName()
    {
        $names = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Lisa', 'Robert', 'Maria', 'James', 'Jennifer', 
                 'William', 'Linda', 'Richard', 'Patricia', 'Charles', 'Barbara', 'Joseph', 'Elizabeth', 'Thomas', 'Susan'];
        return $names[array_rand($names)];
    }
    
    private function getLastName()
    {
        $names = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
                 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];
        return $names[array_rand($names)];
    }
    
    private function generatePhoneNumber()
    {
        return sprintf('(%03d) %03d-%04d', rand(200, 999), rand(200, 999), rand(1000, 9999));
    }
    
    private function getDepartment($role)
    {
        $departments = [
            'head_chef' => 'Kitchen',
            'sous_chef' => 'Kitchen',
            'line_cook' => 'Kitchen',
            'prep_cook' => 'Kitchen',
            'server' => 'Front of House',
            'host' => 'Front of House',
            'busser' => 'Front of House',
            'dishwasher' => 'Kitchen',
            'manager' => 'Management',
            'bartender' => 'Bar',
        ];
        
        return $departments[$role] ?? 'General';
    }
    
    private function generateAddress()
    {
        $streets = ['Main St', 'Oak Ave', 'Pine Rd', 'Cedar Ln', 'Maple Dr', 'Elm St', 'Park Ave', 'First St'];
        $number = rand(100, 9999);
        $street = $streets[array_rand($streets)];
        return "$number $street";
    }
    
    private function generateSSN()
    {
        return sprintf('%03d-%02d-%04d', rand(100, 999), rand(10, 99), rand(1000, 9999));
    }
    
    private function weightedRandom($values, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        for ($i = 0; $i < count($values); $i++) {
            $random -= $weights[$i];
            if ($random <= 0) {
                return $values[$i];
            }
        }
        
        return $values[0];
    }
}
