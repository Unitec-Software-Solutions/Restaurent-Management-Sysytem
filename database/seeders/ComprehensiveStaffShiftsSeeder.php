<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ComprehensiveStaffShiftsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🏢 Creating comprehensive staff and shifts data...');

        // Get organizations and branches
        $organizations = Organization::with('branches')->get();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Please run OrganizationSeeder first.');
            return;
        }

        foreach ($organizations as $organization) {
            $this->createOrganizationStaff($organization);
        }

        $this->command->info('✅ Staff and shifts seeding completed!');
    }

    private function createOrganizationStaff($organization)
    {
        $this->command->info("Creating staff for: {$organization->name}");

        // Create organization-level roles if they don't exist
        $roles = $this->createRoles($organization);
        
        foreach ($organization->branches as $branch) {
            $this->createBranchStaff($branch, $organization, $roles);
            $this->createShiftsAndAssignments($branch);
        }
    }

    private function createRoles($organization)
    {
        $roleDefinitions = [
            'Manager' => ['permissions' => ['manage_staff', 'view_reports', 'manage_inventory']],
            'Assistant Manager' => ['permissions' => ['view_reports', 'manage_orders']],
            'Head Chef' => ['permissions' => ['manage_kitchen', 'manage_menu']],
            'Sous Chef' => ['permissions' => ['manage_kitchen']],
            'Line Cook' => ['permissions' => ['kitchen_operations']],
            'Server' => ['permissions' => ['take_orders', 'serve_customers']],
            'Host/Hostess' => ['permissions' => ['manage_reservations', 'seat_customers']],
            'Bartender' => ['permissions' => ['prepare_drinks', 'manage_bar']],
            'Cashier' => ['permissions' => ['process_payments', 'handle_cash']],
            'Dishwasher' => ['permissions' => ['kitchen_support']],
            'Cleaner' => ['permissions' => ['maintain_cleanliness']],
        ];

        $roles = [];
        foreach ($roleDefinitions as $roleName => $config) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'organization_id' => $organization->id,
            ], [
                'description' => "Organization {$roleName}",
                'permissions' => json_encode($config['permissions']),
                'is_active' => true,
            ]);
            $roles[$roleName] = $role;
        }

        return $roles;
    }

    private function createBranchStaff($branch, $organization, $roles)
    {
        $staffData = [
            // Management
            [
                'name' => 'Sarah Johnson',
                'email' => "manager.{$branch->id}@{$organization->name}.com",
                'role' => 'Manager',
                'phone_number' => '+94771234567',
                'employee_id' => "MGR{$branch->id}001",
                'hire_date' => now()->subMonths(24),
                'hourly_rate' => 2500.00,
                'shift_preference' => 'morning',
            ],
            [
                'name' => 'Mike Chen',
                'email' => "assistant.manager.{$branch->id}@{$organization->name}.com",
                'role' => 'Assistant Manager',
                'phone_number' => '+94771234568',
                'employee_id' => "AMG{$branch->id}001",
                'hire_date' => now()->subMonths(18),
                'hourly_rate' => 2000.00,
                'shift_preference' => 'evening',
            ],

            // Kitchen Staff
            [
                'name' => 'Chef Roberto Italiano',
                'email' => "head.chef.{$branch->id}@{$organization->name}.com",
                'role' => 'Head Chef',
                'phone_number' => '+94771234569',
                'employee_id' => "CHF{$branch->id}001",
                'hire_date' => now()->subMonths(36),
                'hourly_rate' => 2200.00,
                'shift_preference' => 'split',
            ],
            [
                'name' => 'Lisa Wong',
                'email' => "sous.chef.{$branch->id}@{$organization->name}.com",
                'role' => 'Sous Chef',
                'phone_number' => '+94771234570',
                'employee_id' => "SCF{$branch->id}001",
                'hire_date' => now()->subMonths(20),
                'hourly_rate' => 1800.00,
                'shift_preference' => 'morning',
            ],
            [
                'name' => 'Tony Rodriguez',
                'email' => "line.cook1.{$branch->id}@{$organization->name}.com",
                'role' => 'Line Cook',
                'phone_number' => '+94771234571',
                'employee_id' => "LCK{$branch->id}001",
                'hire_date' => now()->subMonths(12),
                'hourly_rate' => 1500.00,
                'shift_preference' => 'morning',
            ],
            [
                'name' => 'Maria Santos',
                'email' => "line.cook2.{$branch->id}@{$organization->name}.com",
                'role' => 'Line Cook',
                'phone_number' => '+94771234572',
                'employee_id' => "LCK{$branch->id}002",
                'hire_date' => now()->subMonths(8),
                'hourly_rate' => 1500.00,
                'shift_preference' => 'evening',
            ],

            // Front of House
            [
                'name' => 'Emma Thompson',
                'email' => "host.{$branch->id}@{$organization->name}.com",
                'role' => 'Host/Hostess',
                'phone_number' => '+94771234573',
                'employee_id' => "HST{$branch->id}001",
                'hire_date' => now()->subMonths(10),
                'hourly_rate' => 1200.00,
                'shift_preference' => 'evening',
            ],
            [
                'name' => 'James Wilson',
                'email' => "server1.{$branch->id}@{$organization->name}.com",
                'role' => 'Server',
                'phone_number' => '+94771234574',
                'employee_id' => "SRV{$branch->id}001",
                'hire_date' => now()->subMonths(15),
                'hourly_rate' => 1300.00,
                'shift_preference' => 'morning',
            ],
            [
                'name' => 'Sophie Davis',
                'email' => "server2.{$branch->id}@{$organization->name}.com",
                'role' => 'Server',
                'phone_number' => '+94771234575',
                'employee_id' => "SRV{$branch->id}002",
                'hire_date' => now()->subMonths(6),
                'hourly_rate' => 1300.00,
                'shift_preference' => 'evening',
            ],
            [
                'name' => 'Alex Kumar',
                'email' => "server3.{$branch->id}@{$organization->name}.com",
                'role' => 'Server',
                'phone_number' => '+94771234576',
                'employee_id' => "SRV{$branch->id}003",
                'hire_date' => now()->subMonths(3),
                'hourly_rate' => 1300.00,
                'shift_preference' => 'split',
            ],

            // Specialized Roles
            [
                'name' => 'Carlos Martinez',
                'email' => "bartender.{$branch->id}@{$organization->name}.com",
                'role' => 'Bartender',
                'phone_number' => '+94771234577',
                'employee_id' => "BTD{$branch->id}001",
                'hire_date' => now()->subMonths(14),
                'hourly_rate' => 1600.00,
                'shift_preference' => 'evening',
            ],
            [
                'name' => 'Jennifer Lee',
                'email' => "cashier.{$branch->id}@{$organization->name}.com",
                'role' => 'Cashier',
                'phone_number' => '+94771234578',
                'employee_id' => "CSH{$branch->id}001",
                'hire_date' => now()->subMonths(9),
                'hourly_rate' => 1400.00,
                'shift_preference' => 'morning',
            ],

            // Support Staff
            [
                'name' => 'David Park',
                'email' => "dishwasher.{$branch->id}@{$organization->name}.com",
                'role' => 'Dishwasher',
                'phone_number' => '+94771234579',
                'employee_id' => "DSH{$branch->id}001",
                'hire_date' => now()->subMonths(5),
                'hourly_rate' => 1000.00,
                'shift_preference' => 'evening',
            ],
            [
                'name' => 'Ana Perez',
                'email' => "cleaner.{$branch->id}@{$organization->name}.com",
                'role' => 'Cleaner',
                'phone_number' => '+94771234580',
                'employee_id' => "CLN{$branch->id}001",
                'hire_date' => now()->subMonths(7),
                'hourly_rate' => 900.00,
                'shift_preference' => 'night',
            ],
        ];

        foreach ($staffData as $staff) {
            $role = $roles[$staff['role']];
            
            $user = User::firstOrCreate([
                'email' => $staff['email'],
            ], [
                'name' => $staff['name'],
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'role_id' => $role->id,
                'phone_number' => $staff['phone_number'],
                'password' => Hash::make('password123'),
                'employee_id' => $staff['employee_id'],
                'hire_date' => $staff['hire_date'],
                'hourly_rate' => $staff['hourly_rate'],
                'shift_preference' => $staff['shift_preference'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->info("  Created staff: {$user->name} ({$staff['role']})");
        }
    }

    private function createShiftsAndAssignments($branch)
    {
        // Define shift templates
        $shiftTemplates = [
            [
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'min_staff' => 4,
                'max_staff' => 6,
                'is_peak' => false,
            ],
            [
                'name' => 'Afternoon Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'min_staff' => 6,
                'max_staff' => 10,
                'is_peak' => true,
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'min_staff' => 2,
                'max_staff' => 3,
                'is_peak' => false,
            ],
            [
                'name' => 'Lunch Rush',
                'start_time' => '11:00:00',
                'end_time' => '15:00:00',
                'min_staff' => 8,
                'max_staff' => 12,
                'is_peak' => true,
            ],
            [
                'name' => 'Dinner Rush',
                'start_time' => '17:00:00',
                'end_time' => '21:00:00',
                'min_staff' => 10,
                'max_staff' => 15,
                'is_peak' => true,
            ],
        ];

        $shifts = [];
        foreach ($shiftTemplates as $template) {
            $shift = Shift::firstOrCreate([
                'branch_id' => $branch->id,
                'name' => $template['name'],
            ], [
                'start_time' => $template['start_time'],
                'end_time' => $template['end_time'],
                'min_staff_required' => $template['min_staff'],
                'max_staff_allowed' => $template['max_staff'],
                'is_peak_hours' => $template['is_peak'],
                'hourly_multiplier' => $template['is_peak'] ? 1.5 : 1.0,
                'is_active' => true,
            ]);
            $shifts[$template['name']] = $shift;
        }

        // Create shift assignments for the next 30 days
        $branchStaff = User::where('branch_id', $branch->id)->get();
        $startDate = now();
        
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $this->assignDayShifts($branch, $shifts, $branchStaff, $date);
        }

        // Create some edge cases and overlapping shifts
        $this->createEdgeCaseShifts($branch, $shifts, $branchStaff);

        $this->command->info("  Created shifts and assignments for: {$branch->name}");
    }

    private function assignDayShifts($branch, $shifts, $staff, $date)
    {
        $isWeekend = $date->isWeekend();
        $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 6 = Saturday

        // Different staffing patterns for different days
        $shiftPatterns = [
            'Monday' => ['Morning Shift', 'Lunch Rush', 'Dinner Rush'],
            'Tuesday' => ['Morning Shift', 'Lunch Rush', 'Afternoon Shift'],
            'Wednesday' => ['Morning Shift', 'Lunch Rush', 'Dinner Rush'],
            'Thursday' => ['Morning Shift', 'Lunch Rush', 'Dinner Rush'],
            'Friday' => ['Morning Shift', 'Lunch Rush', 'Dinner Rush', 'Night Shift'],
            'Saturday' => ['Morning Shift', 'Lunch Rush', 'Dinner Rush', 'Night Shift'],
            'Sunday' => ['Morning Shift', 'Afternoon Shift', 'Night Shift'],
        ];

        $dayName = $date->format('l');
        $dayShifts = $shiftPatterns[$dayName] ?? ['Morning Shift', 'Afternoon Shift'];

        foreach ($dayShifts as $shiftName) {
            if (!isset($shifts[$shiftName])) continue;
            
            $shift = $shifts[$shiftName];
            $requiredStaff = $isWeekend ? $shift->max_staff_allowed : $shift->min_staff_required;
            
            // Select staff based on their preferences and availability
            $selectedStaff = $this->selectStaffForShift($staff, $shift, $requiredStaff, $date);
            
            foreach ($selectedStaff as $staffMember) {
                ShiftAssignment::create([
                    'shift_id' => $shift->id,
                    'user_id' => $staffMember->id,
                    'date' => $date->format('Y-m-d'),
                    'status' => $this->getRandomStatus(),
                    'actual_start_time' => $this->getActualTime($shift->start_time, 'start'),
                    'actual_end_time' => $this->getActualTime($shift->end_time, 'end'),
                    'break_duration' => rand(30, 60), // minutes
                    'overtime_hours' => rand(0, 100) > 80 ? rand(1, 3) : 0, // 20% chance of overtime
                    'notes' => $this->getRandomNotes(),
                ]);
            }
        }
    }

    private function selectStaffForShift($staff, $shift, $requiredCount, $date)
    {
        $shiftTime = strtolower($shift->name);
        $selected = collect();

        // First, add staff with matching preferences
        $preferredStaff = $staff->filter(function($s) use ($shiftTime) {
            return str_contains($shiftTime, $s->shift_preference) || $s->shift_preference === 'split';
        })->shuffle();

        $selected = $selected->merge($preferredStaff->take($requiredCount));

        // If we need more staff, add from remaining pool
        if ($selected->count() < $requiredCount) {
            $remaining = $staff->diff($selected)->shuffle();
            $needed = $requiredCount - $selected->count();
            $selected = $selected->merge($remaining->take($needed));
        }

        return $selected->take($requiredCount);
    }

    private function createEdgeCaseShifts($branch, $shifts, $staff)
    {
        $today = now();

        // Scenario 1: Overlapping shifts during busy period
        $busyDate = $today->copy()->addDays(5);
        $lunchShift = $shifts['Lunch Rush'];
        $dinnerShift = $shifts['Dinner Rush'];
        
        // Some staff work both lunch and dinner (double shift)
        $doubleShiftStaff = $staff->where('role.name', 'Server')->take(2);
        foreach ($doubleShiftStaff as $staffMember) {
            ShiftAssignment::create([
                'shift_id' => $lunchShift->id,
                'user_id' => $staffMember->id,
                'date' => $busyDate->format('Y-m-d'),
                'status' => 'completed',
                'actual_start_time' => '10:45:00',
                'actual_end_time' => '15:30:00',
                'break_duration' => 45,
                'overtime_hours' => 2,
                'notes' => 'Double shift - lunch and dinner coverage',
            ]);

            ShiftAssignment::create([
                'shift_id' => $dinnerShift->id,
                'user_id' => $staffMember->id,
                'date' => $busyDate->format('Y-m-d'),
                'status' => 'completed',
                'actual_start_time' => '16:45:00',
                'actual_end_time' => '21:45:00',
                'break_duration' => 60,
                'overtime_hours' => 1,
                'notes' => 'Double shift - continued from lunch',
            ]);
        }

        // Scenario 2: Staff shortages and call-ins
        $shortageDate = $today->copy()->addDays(8);
        $dinnerShift = $shifts['Dinner Rush'];
        
        // Create assignments with some no-shows
        $allStaff = $staff->shuffle();
        foreach ($allStaff->take(6) as $i => $staffMember) {
            $status = $i < 2 ? 'no_show' : 'completed';
            ShiftAssignment::create([
                'shift_id' => $dinnerShift->id,
                'user_id' => $staffMember->id,
                'date' => $shortageDate->format('Y-m-d'),
                'status' => $status,
                'actual_start_time' => $status === 'no_show' ? null : '16:50:00',
                'actual_end_time' => $status === 'no_show' ? null : '21:20:00',
                'break_duration' => $status === 'no_show' ? null : 45,
                'notes' => $status === 'no_show' ? 'Called in sick last minute' : 'Covered extra tables due to shortage',
            ]);
        }

        // Scenario 3: Split shifts for management
        $splitDate = $today->copy()->addDays(12);
        $manager = $staff->where('role.name', 'Manager')->first();
        if ($manager) {
            // Morning check-in
            ShiftAssignment::create([
                'shift_id' => $shifts['Morning Shift']->id,
                'user_id' => $manager->id,
                'date' => $splitDate->format('Y-m-d'),
                'status' => 'completed',
                'actual_start_time' => '05:45:00',
                'actual_end_time' => '10:00:00',
                'break_duration' => 30,
                'notes' => 'Opening duties and morning setup',
            ]);

            // Evening closing
            ShiftAssignment::create([
                'shift_id' => $shifts['Night Shift']->id,
                'user_id' => $manager->id,
                'date' => $splitDate->format('Y-m-d'),
                'status' => 'completed',
                'actual_start_time' => '20:00:00',
                'actual_end_time' => '23:30:00',
                'break_duration' => 15,
                'overtime_hours' => 1,
                'notes' => 'Split shift - closing duties and end-of-day reporting',
            ]);
        }
    }

    private function getRandomStatus()
    {
        $statuses = ['scheduled', 'in_progress', 'completed', 'late', 'no_show'];
        $weights = [10, 5, 70, 10, 5]; // Weighted random selection
        
        $random = rand(1, 100);
        $cumulative = 0;
        
        foreach ($weights as $i => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $statuses[$i];
            }
        }
        
        return 'completed';
    }

    private function getActualTime($scheduledTime, $type)
    {
        $time = Carbon::createFromTimeString($scheduledTime);
        
        // Add realistic variations
        if ($type === 'start') {
            // Staff might arrive 5-15 minutes early or 5-10 minutes late
            $variation = rand(-15, 10);
        } else {
            // End times might vary by -10 to +30 minutes
            $variation = rand(-10, 30);
        }
        
        return $time->addMinutes($variation)->format('H:i:s');
    }

    private function getRandomNotes()
    {
        $notes = [
            'Smooth shift, no issues',
            'Busy period, handled well',
            'Customer complaint resolved',
            'Training new team member',
            'Equipment issue reported',
            'Exceptional customer service',
            'Rush period extended',
            'Covered additional section',
            'Perfect attendance',
            'Assisted with catering order',
            null // Some shifts have no notes
        ];

        return $notes[array_rand($notes)];
    }
}
