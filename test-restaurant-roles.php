<?php
use Illuminate\Support\Facades\Schema;
// Restaurant Role System Test Script

echo "Restaurant Role System Test Results\n";
echo "=====================================\n\n";

// Test 1: Check if all restaurant roles exist
echo "1. Checking Restaurant Roles:\n";
$requiredRoles = ['host/hostess', 'servers', 'bartenders', 'cashiers', 'chefs', 'dishwashers', 'kitchen-managers'];
$existingRoles = \Spatie\Permission\Models\Role::whereIn('name', $requiredRoles)->pluck('name')->toArray();

foreach ($requiredRoles as $role) {
    if (in_array($role, $existingRoles)) {
        echo "   ✓ {$role} - EXISTS\n";
    } else {
        echo "   ✗ {$role} - MISSING\n";
    }
}

// Test 2: Check role permissions
echo "\n2. Checking Role Permissions:\n";
$rolePermissions = [
    'host/hostess' => ['manage-reservations', 'view-restaurant-layout', 'view-table-status', 'customer-service', 'view-waitlist'],
    'servers' => ['take-orders', 'modify-orders', 'view-menu', 'process-payments', 'customer-service', 'view-table-assignments'],
    'bartenders' => ['manage-bar-inventory', 'prepare-beverages', 'view-bar-orders', 'cash-handling', 'view-menu'],
    'cashiers' => ['process-payments', 'handle-refunds', 'cash-handling', 'print-receipts', 'view-sales-reports'],
    'chefs' => ['view-kitchen-orders', 'update-order-status', 'manage-kitchen-inventory', 'view-recipes', 'kitchen-operations'],
    'dishwashers' => ['kitchen-support', 'equipment-maintenance', 'view-cleaning-schedule'],
    'kitchen-managers' => ['manage-kitchen-staff', 'kitchen-operations', 'manage-kitchen-inventory', 'view-kitchen-reports', 'approve-menu-changes', 'schedule-management']
];

foreach ($rolePermissions as $roleName => $permissions) {
    $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
    if ($role) {
        $rolePerms = $role->permissions->pluck('name')->toArray();
        $missing = array_diff($permissions, $rolePerms);
        
        if (empty($missing)) {
            echo "   ✓ {$roleName} - All permissions assigned\n";
        } else {
            echo "   ✗ {$roleName} - Missing: " . implode(', ', $missing) . "\n";
        }
    } else {
        echo "   ✗ {$roleName} - Role not found\n";
    }
}

// Test 3: Check database structure
echo "\n3. Database Structure:\n";
if (Schema::hasTable('employees')) {
    if (Schema::hasColumn('employees', 'role_id')) {
        echo "   ✓ employees.role_id column exists\n";
    } else {
        echo "   ✗ employees.role_id column missing\n";
    }
} else {
    echo "   ✗ employees table not found\n";
}

// Test 4: Check employee role assignments
echo "\n4. Employee Role Assignments:\n";
$employeesWithRoles = \App\Models\Employee::whereNotNull('role_id')->count();
$totalEmployees = \App\Models\Employee::count();

echo "   Employees with restaurant roles: {$employeesWithRoles} of {$totalEmployees}\n";

if ($employeesWithRoles > 0) {
    $roleDistribution = \App\Models\Employee::join('roles', 'employees.role_id', '=', 'roles.id')
        ->selectRaw('roles.name, COUNT(*) as count')
        ->groupBy('roles.name')
        ->get();
        
    echo "   Role distribution:\n";
    foreach ($roleDistribution as $dist) {
        echo "     - {$dist->name}: {$dist->count} employees\n";
    }
}

echo "\n5. System Summary:\n";
echo "   Restaurant roles created: " . count($existingRoles) . " of " . count($requiredRoles) . "\n";
echo "   Restaurant permissions: " . \Spatie\Permission\Models\Permission::where('guard_name', 'web')->count() . "\n";
echo "   Total employees: {$totalEmployees}\n";

echo "\nImplementation Status: ";
if (count($existingRoles) === count($requiredRoles)) {
    echo "✓ COMPLETE\n";
} else {
    echo "✗ INCOMPLETE\n";
}

echo "\nNext Steps:\n";
echo "1. Assign restaurant roles to existing employees via admin panel\n";
echo "2. Test permission-based access controls\n";
echo "3. Review role assignments in employee management\n";
echo "4. Update any custom routes with new middleware if needed\n";
