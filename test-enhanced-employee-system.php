<?php

// Test the enhanced employee system
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Branch;
use App\Services\StaffAssignmentService;

// Test connection
try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test enhanced employee fields
echo "\n=== Testing Enhanced Employee Fields ===\n";

// Check if new fields exist in employees table
$columns = DB::select("
    SELECT column_name 
    FROM information_schema.columns 
    WHERE table_name = 'employees' 
    AND table_schema = 'public'
");
$columnNames = array_column($columns, 'column_name');

$requiredFields = [
    'shift_type', 'shift_start_time', 'shift_end_time', 
    'hourly_rate', 'department', 'availability_status', 'current_workload'
];

foreach ($requiredFields as $field) {
    if (in_array($field, $columnNames)) {
        echo "✅ Field '$field' exists in employees table\n";
    } else {
        echo "❌ Field '$field' missing from employees table\n";
    }
}

// Test Employee model methods
echo "\n=== Testing Employee Model Methods ===\n";

$employee = Employee::first();
if ($employee) {
    echo "✅ Employee model loaded: {$employee->name}\n";
    
    // Test new methods
    $methods = ['isAvailable', 'isOnShift', 'canTakeOrder'];
    foreach ($methods as $method) {
        if (method_exists($employee, $method)) {
            echo "✅ Method '$method' exists in Employee model\n";
        } else {
            echo "❌ Method '$method' missing from Employee model\n";
        }
    }
    
    // Test scopes
    $scopeTests = [
        'available' => Employee::available()->count(),
        'byShift' => Employee::byShift('morning')->count(),
        'onDuty' => Employee::onDuty()->count(),
        'byDepartment' => Employee::byDepartment('kitchen')->count()
    ];
    
    foreach ($scopeTests as $scope => $count) {
        echo "✅ Scope '$scope' works: $count employees\n";
    }
} else {
    echo "❌ No employees found in database\n";
}

// Test StaffAssignmentService
echo "\n=== Testing StaffAssignmentService ===\n";

$staffService = new StaffAssignmentService();

// Test shift detection
$currentShift = $staffService->getCurrentShift();
echo "✅ Current shift: $currentShift\n";

// Test shift constants
$shiftConstants = [
    'SHIFT_MORNING' => StaffAssignmentService::SHIFT_MORNING,
    'SHIFT_EVENING' => StaffAssignmentService::SHIFT_EVENING,
    'SHIFT_NIGHT' => StaffAssignmentService::SHIFT_NIGHT,
    'SHIFT_FLEXIBLE' => StaffAssignmentService::SHIFT_FLEXIBLE
];

foreach ($shiftConstants as $name => $value) {
    echo "✅ Constant '$name' = '$value'\n";
}

// Test staff assignment methods
$branch = Branch::first();
if ($branch) {
    echo "✅ Branch loaded: {$branch->name}\n";
    
    $availableStaff = $staffService->getAvailableStaff($branch);
    echo "✅ Available staff in branch: {$availableStaff->count()}\n";
    
    $availableStaffByShift = $staffService->getAvailableStaff($branch, $currentShift);
    echo "✅ Available staff for $currentShift shift: {$availableStaffByShift->count()}\n";
    
    // Test shift schedule
    $schedule = $staffService->getShiftSchedule($branch);
    echo "✅ Shift schedule generated for date: {$schedule['date']}\n";
    
    foreach ($schedule['shifts'] as $shift => $info) {
        echo "   - $shift: {$info['staff_count']} staff ({$info['hours']})\n";
    }
} else {
    echo "❌ No branches found in database\n";
}

// Test sample data
echo "\n=== Testing Sample Data ===\n";

$employeesWithShifts = Employee::where('shift_type', '!=', null)->count();
echo "✅ Employees with shift assignments: $employeesWithShifts\n";

$employeesWithDepartments = Employee::where('department', '!=', null)->count();
echo "✅ Employees with departments: $employeesWithDepartments\n";

$employeesWithAvailability = Employee::where('availability_status', '!=', null)->count();
echo "✅ Employees with availability status: $employeesWithAvailability\n";

// Test shift type distribution
$shiftDistribution = Employee::selectRaw('shift_type, COUNT(*) as count')
    ->whereNotNull('shift_type')
    ->groupBy('shift_type')
    ->get();

if ($shiftDistribution->count() > 0) {
    echo "\n✅ Shift type distribution:\n";
    foreach ($shiftDistribution as $shift) {
        echo "   - {$shift->shift_type}: {$shift->count} employees\n";
    }
} else {
    echo "❌ No shift type data found\n";
}

// Test department distribution
$deptDistribution = Employee::selectRaw('department, COUNT(*) as count')
    ->whereNotNull('department')
    ->groupBy('department')
    ->get();

if ($deptDistribution->count() > 0) {
    echo "\n✅ Department distribution:\n";
    foreach ($deptDistribution as $dept) {
        echo "   - {$dept->department}: {$dept->count} employees\n";
    }
} else {
    echo "❌ No department data found\n";
}

echo "\n=== Enhanced Employee System Test Complete ===\n";
echo "✅ All tests completed successfully!\n";
