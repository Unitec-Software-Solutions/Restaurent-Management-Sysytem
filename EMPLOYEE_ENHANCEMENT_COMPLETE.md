# Employee Table Enhancement - Shift and Staff Management

## Overview
Enhanced the `employees` table with essential shift and staff management fields, keeping the implementation focused and practical for restaurant operations.

## Database Changes

### New Fields Added to `employees` Table:

#### Shift Management
- `shift_type` - ENUM: morning, evening, night, flexible (default: flexible)
- `shift_start_time` - TIME: Start time for the employee's shift
- `shift_end_time` - TIME: End time for the employee's shift

#### Staff Management
- `hourly_rate` - DECIMAL(8,2): Employee's hourly wage rate
- `department` - VARCHAR: Department (Kitchen, Service, Management, etc.)
- `availability_status` - ENUM: available, busy, on_break, off_duty (default: available)
- `current_workload` - INTEGER: Number of active orders/tasks assigned (default: 0)

#### Database Indexes
- `idx_employees_shift_availability` - For efficient shift and availability queries
- `idx_employees_branch_active` - For branch-based active employee queries

## Model Enhancements

### Employee Model (`app/Models/Employee.php`)

#### New Fillable Fields
```php
'shift_type', 'shift_start_time', 'shift_end_time', 'hourly_rate', 
'department', 'availability_status', 'current_workload'
```

#### New Casts
```php
'hourly_rate' => 'decimal:2',
'shift_start_time' => 'datetime:H:i',
'shift_end_time' => 'datetime:H:i',
'current_workload' => 'integer'
```

#### New Methods
- `isAvailable()` - Check if employee is available and active
- `isOnShift($time)` - Check if employee is currently on their shift
- `canTakeOrder()` - Check if employee can take new orders
- `assignOrder()` - Increment workload and update status if needed
- `completeOrder()` - Decrement workload and update status
- `setOnBreak()`, `setOffDuty()`, `setAvailable()` - Status management methods

#### New Scopes
- `byShift($shiftType)` - Filter employees by shift type
- `available()` - Get available and active employees
- `onDuty()` - Get employees who are available or busy
- `byDepartment($department)` - Filter by department

## Controller Updates

### EmployeeController (`app/Http/Controllers/Admin/EmployeeController.php`)

#### Enhanced Validation Rules
Added validation for new fields in both `store()` and `update()` methods:
```php
'shift_type' => 'nullable|in:morning,evening,night,flexible',
'shift_start_time' => 'nullable|date_format:H:i',
'shift_end_time' => 'nullable|date_format:H:i',
'hourly_rate' => 'nullable|numeric|min:0',
'department' => 'nullable|string|max:255',
'availability_status' => 'nullable|in:available,busy,on_break,off_duty',
'current_workload' => 'nullable|integer|min:0',
```

#### New API Methods
- `getAvailableForShift()` - Get employees available for specific shift
- `updateAvailability()` - Update employee availability status
- `getShiftSchedule()` - Get comprehensive shift schedule for branch

## Service Updates

### StaffAssignmentService (`app/Services/StaffAssignmentService.php`)

#### Updated Constants
```php
const SHIFT_MORNING = 'morning';
const SHIFT_EVENING = 'evening';
const SHIFT_NIGHT = 'night';
const SHIFT_FLEXIBLE = 'flexible';
```

#### Enhanced Methods
- Updated `getAvailableStaff()` to use new availability scope
- Updated `selectBestStaffMember()` to use model's current_workload field
- Updated `calculatePriorityScore()` to work with new shift_type field

## Usage Examples

### Creating Employees with Shift Data
```php
Employee::create([
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
    // ... other required fields
]);
```

### Querying by Shift and Availability
```php
// Get available morning shift employees
$morningStaff = Employee::available()
    ->byShift('morning')
    ->get();

// Get kitchen department employees who can take orders
$kitchenStaff = Employee::byDepartment('Kitchen')
    ->where('availability_status', 'available')
    ->get();

// Check if employee can take a new order
if ($employee->canTakeOrder()) {
    $employee->assignOrder();
}
```

### API Endpoints for Shift Management
```php
// Get shift schedule
GET /api/employees/shift-schedule?branch_id=1

// Update availability
POST /api/employees/{id}/availability
{
    "availability_status": "on_break"
}

// Get available staff for shift
GET /api/employees/available-for-shift?shift_type=evening&branch_id=1
```

## Migration File
- **File**: `2025_06_26_110757_enhance_employees_table_with_shift_and_staff_fields.php`
- **Status**: ✅ Applied successfully
- **Rollback**: Supported - includes proper down() method

## Testing
- ✅ Database schema validated
- ✅ Model methods tested
- ✅ Sample data creation verified
- ✅ Workload management functionality confirmed
- ✅ Scope queries working correctly

## Benefits

1. **Efficient Shift Management**: Track employee shifts and availability in real-time
2. **Workload Balancing**: Automatic workload tracking prevents overloading staff
3. **Department Organization**: Better staff organization by department
4. **API Ready**: RESTful endpoints for frontend integration
5. **Performance Optimized**: Database indexes for fast queries
6. **Minimal Impact**: Only essential fields added, no bloat

## Files Modified

### Database
- `database/migrations/2025_06_26_110757_enhance_employees_table_with_shift_and_staff_fields.php` (new)

### Models
- `app/Models/Employee.php` (enhanced)

### Controllers
- `app/Http/Controllers/Admin/EmployeeController.php` (enhanced)

### Services
- `app/Services/StaffAssignmentService.php` (updated)

### Test Files
- `test-enhanced-employee.php` (verification script)
- `create-sample-employees.php` (sample data script)

---

**Status**: ✅ **COMPLETE** - Employee table successfully enhanced with essential shift and staff management functionality.
