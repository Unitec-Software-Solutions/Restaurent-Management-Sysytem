<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Employee;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CREATING SAMPLE EMPLOYEE DATA ===\n";

try {
    DB::beginTransaction();
    
    // Get first organization and branch
    $organization = Organization::first();
    $branch = Branch::first();
    
    if (!$organization || !$branch) {
        echo "❌ No organization or branch found. Please create them first.\n";
        exit(1);
    }
    
    echo "Using Organization: {$organization->name}\n";
    echo "Using Branch: {$branch->name}\n\n";
    
    // Create sample employees with shift data
    $sampleEmployees = [
        [
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'phone' => '0412345678',
            'role' => 'waiter',
            'shift_type' => 'morning',
            'shift_start_time' => '06:00',
            'shift_end_time' => '15:00',
            'department' => 'Service',
            'hourly_rate' => 25.50,
            'availability_status' => 'available',
            'current_workload' => 0,
        ],
        [
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'phone' => '0423456789',
            'role' => 'chef',
            'shift_type' => 'evening',
            'shift_start_time' => '15:00',
            'shift_end_time' => '23:00',
            'department' => 'Kitchen',
            'hourly_rate' => 32.00,
            'availability_status' => 'available',
            'current_workload' => 0,
        ],
        [
            'name' => 'Carol Davis',
            'email' => 'carol@example.com',
            'phone' => '0434567890',
            'role' => 'cashier',
            'shift_type' => 'flexible',
            'department' => 'Service',
            'hourly_rate' => 28.75,
            'availability_status' => 'available',
            'current_workload' => 0,
        ]
    ];
    
    foreach ($sampleEmployees as $index => $empData) {
        // Check if employee already exists
        $existing = Employee::where('email', $empData['email'])->first();
        if ($existing) {
            echo "Employee {$empData['name']} already exists, updating...\n";
            $existing->update(array_merge($empData, [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
            ]));
            $employee = $existing;
        } else {
            echo "Creating employee: {$empData['name']}\n";
            $employee = Employee::create(array_merge($empData, [
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'joined_date' => now(),
                'is_active' => true,
            ]));
        }
        
        echo "   - {$employee->name}: {$employee->shift_type} shift, {$employee->department} department\n";
    }
    
    DB::commit();
    
    echo "\n=== TESTING ENHANCED FUNCTIONALITY ===\n";
    
    // Test 1: Get available employees
    $available = Employee::available()->get();
    echo "Available employees: {$available->count()}\n";
    foreach ($available as $emp) {
        echo "   - {$emp->name} ({$emp->shift_type} shift, {$emp->availability_status})\n";
    }
    
    // Test 2: Test shift filtering
    echo "\nMorning shift employees:\n";
    $morningShift = Employee::byShift('morning')->get();
    foreach ($morningShift as $emp) {
        echo "   - {$emp->name} ({$emp->shift_start_time} - {$emp->shift_end_time})\n";
    }
    
    // Test 3: Test department filtering
    echo "\nKitchen department employees:\n";
    $kitchen = Employee::byDepartment('Kitchen')->get();
    foreach ($kitchen as $emp) {
        echo "   - {$emp->name} ({$emp->role})\n";
    }
    
    // Test 4: Test workload management
    echo "\nTesting workload management...\n";
    $employee = Employee::first();
    if ($employee) {
        echo "Before: {$employee->name} - Workload: {$employee->current_workload}, Status: {$employee->availability_status}\n";
        
        // Assign some orders
        $employee->assignOrder();
        $employee->assignOrder();
        $employee->refresh();
        
        echo "After assigning 2 orders: Workload: {$employee->current_workload}, Status: {$employee->availability_status}\n";
        
        // Complete an order
        $employee->completeOrder();
        $employee->refresh();
        
        echo "After completing 1 order: Workload: {$employee->current_workload}, Status: {$employee->availability_status}\n";
    }
    
    echo "\n✅ Sample data created and functionality tested successfully!\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
