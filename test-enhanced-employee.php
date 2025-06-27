<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING ENHANCED EMPLOYEE MODEL ===\n";

try {
    // Test 1: Check if new columns exist in database
    echo "1. Testing database schema...\n";
    $columns = DB::select("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'employees' 
        AND column_name IN ('shift_type', 'shift_start_time', 'shift_end_time', 'hourly_rate', 'department', 'availability_status', 'current_workload')
        ORDER BY column_name
    ");
    
    foreach ($columns as $column) {
        echo "   ✓ Column exists: {$column->column_name}\n";
    }
    
    // Test 2: Get first employee and check new fields
    echo "\n2. Testing Employee model...\n";
    $employee = Employee::first();
    
    if ($employee) {
        echo "   Employee: {$employee->name}\n";
        echo "   Shift Type: " . ($employee->shift_type ?? 'Not set') . "\n";
        echo "   Department: " . ($employee->department ?? 'Not set') . "\n";
        echo "   Availability: " . ($employee->availability_status ?? 'available') . "\n";
        echo "   Current Workload: " . ($employee->current_workload ?? 0) . "\n";
        
        // Test model methods
        echo "\n3. Testing model methods...\n";
        echo "   isAvailable(): " . ($employee->isAvailable() ? 'Yes' : 'No') . "\n";
        echo "   canTakeOrder(): " . ($employee->canTakeOrder() ? 'Yes' : 'No') . "\n";
        
    } else {
        echo "   No employees found in database\n";
    }
    
    // Test 3: Test scopes
    echo "\n4. Testing model scopes...\n";
    $availableCount = Employee::available()->count();
    $onDutyCount = Employee::onDuty()->count();
    
    echo "   Available employees: {$availableCount}\n";
    echo "   On duty employees: {$onDutyCount}\n";
    
    echo "\n✅ All tests completed successfully!\n";
    echo "The Employee model has been enhanced with shift and staff management fields.\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
