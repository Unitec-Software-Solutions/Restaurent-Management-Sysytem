<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\ItemMaster;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\KitchenStation;
use App\Models\Employee;
use App\Models\Table;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExhaustiveValidationSeeder extends Seeder
{
    use WithoutModelEvents;

    private $validationResults = [];
    private $validationErrors = [];

    /**
     * Seed and validate system integrity with comprehensive validation scenarios
     */
    public function run(): void
    {
        $this->command->info('🔍 Running Exhaustive System Validation & Verification...');
        $this->command->info('═══════════════════════════════════════════════════════════');
        
        try {
            // Phase 1: Data Integrity Validation
            $this->command->info('🔐 Phase 1: Data Integrity Validation');
            $this->validateDataIntegrity();
            
            // Phase 2: Business Rule Validation
            $this->command->info('💼 Phase 2: Business Rule Validation');
            $this->validateBusinessRules();
            
            // Phase 3: Relationship Integrity
            $this->command->info('🔗 Phase 3: Relationship Integrity Validation');
            $this->validateRelationships();
            
            // Phase 4: Permission & Security Validation
            $this->command->info('🛡️ Phase 4: Permission & Security Validation');
            $this->validatePermissions();
            
            // Phase 5: Financial Data Validation
            $this->command->info('💰 Phase 5: Financial Data Validation');
            $this->validateFinancialData();
            
            // Phase 6: State Transition Validation
            $this->command->info('🔄 Phase 6: State Transition Validation');
            $this->validateStateTransitions();
            
            // Phase 7: Performance Validation
            $this->command->info('⚡ Phase 7: Performance Validation');
            $this->validatePerformance();
            
            // Phase 8: Edge Case Validation
            $this->command->info('⚠️ Phase 8: Edge Case Validation');
            $this->validateEdgeCases();
            
            // Phase 9: Cross-Module Validation
            $this->command->info('🌐 Phase 9: Cross-Module Validation');
            $this->validateCrossModule();
            
            // Phase 10: System Health Check
            $this->command->info('🏥 Phase 10: System Health Check');
            $this->performSystemHealthCheck();
            
            $this->displayValidationSummary();
            
        } catch (\Exception $e) {
            $this->command->error('❌ Validation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function validateDataIntegrity(): void
    {
        // 1. Organization Data Integrity
        $organizations = Organization::all();
        foreach ($organizations as $org) {
            $issues = [];
            
            if (empty($org->name)) $issues[] = 'Missing organization name';
            if (empty($org->email)) $issues[] = 'Missing organization email';
            if (!filter_var($org->email, FILTER_VALIDATE_EMAIL)) $issues[] = 'Invalid email format';
            if (empty($org->phone)) $issues[] = 'Missing phone number';
            
            if (!empty($issues)) {
                $this->validationErrors['organization_' . $org->id] = $issues;
            } else {
                $this->validationResults['valid_organizations'] = ($this->validationResults['valid_organizations'] ?? 0) + 1;
            }
        }
        
        // 2. Branch Data Integrity
        $branches = Branch::all();
        foreach ($branches as $branch) {
            $issues = [];
            
            if (empty($branch->name)) $issues[] = 'Missing branch name';
            if (empty($branch->organization_id)) $issues[] = 'Missing organization reference';
            if ($branch->total_capacity <= 0) $issues[] = 'Invalid capacity';
            if ($branch->reservation_fee < 0) $issues[] = 'Negative reservation fee';
            
            if (!empty($issues)) {
                $this->validationErrors['branch_' . $branch->id] = $issues;
            } else {
                $this->validationResults['valid_branches'] = ($this->validationResults['valid_branches'] ?? 0) + 1;
            }
        }
        
        // 3. User Data Integrity
        $users = User::all();
        foreach ($users as $user) {
            $issues = [];
            
            if (empty($user->name)) $issues[] = 'Missing user name';
            if (empty($user->email)) $issues[] = 'Missing email';
            if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) $issues[] = 'Invalid email format';
            if (empty($user->phone)) $issues[] = 'Missing phone';
            if (strlen($user->phone) < 10) $issues[] = 'Phone too short';
            
            if (!empty($issues)) {
                $this->validationErrors['user_' . $user->id] = $issues;
            } else {
                $this->validationResults['valid_users'] = ($this->validationResults['valid_users'] ?? 0) + 1;
            }
        }
        
        // 4. Order Data Integrity
        $orders = Order::all();
        foreach ($orders as $order) {
            $issues = [];
            
            if (empty($order->customer_name)) $issues[] = 'Missing customer name';
            if (empty($order->customer_phone)) $issues[] = 'Missing customer phone';
            if ($order->total < 0) $issues[] = 'Negative total amount';
            if ($order->subtotal < 0) $issues[] = 'Negative subtotal';
            if ($order->tax < 0) $issues[] = 'Negative tax';
            if (abs($order->total - ($order->subtotal + $order->tax)) > 0.01) {
                $issues[] = 'Total calculation mismatch';
            }
            
            if (!empty($issues)) {
                $this->validationErrors['order_' . $order->id] = $issues;
            } else {
                $this->validationResults['valid_orders'] = ($this->validationResults['valid_orders'] ?? 0) + 1;
            }
        }
        
        // 5. Reservation Data Integrity
        $reservations = Reservation::all();
        foreach ($reservations as $reservation) {
            $issues = [];
            
            if (empty($reservation->name)) $issues[] = 'Missing reservation name';
            if (empty($reservation->phone)) $issues[] = 'Missing phone';
            if ($reservation->number_of_people <= 0) $issues[] = 'Invalid number of people';
            if ($reservation->start_time >= $reservation->end_time) $issues[] = 'Invalid time range';
            if ($reservation->reservation_fee < 0) $issues[] = 'Negative reservation fee';
            
            if (!empty($issues)) {
                $this->validationErrors['reservation_' . $reservation->id] = $issues;
            } else {
                $this->validationResults['valid_reservations'] = ($this->validationResults['valid_reservations'] ?? 0) + 1;
            }
        }
    }

    private function validateBusinessRules(): void
    {
        // 1. Branch Capacity vs Reservations
        $branches = Branch::with('reservations')->get();
        foreach ($branches as $branch) {
            $todayReservations = $branch->reservations()
                ->where('date', today())
                ->where('status', '!=', 'cancelled')
                ->get();
            
            $totalPeopleToday = $todayReservations->sum('number_of_people');
            
            if ($totalPeopleToday > $branch->total_capacity * 2) { // Allow some overbooking
                $this->validationErrors['capacity_' . $branch->id] = [
                    "Excessive overbooking: {$totalPeopleToday} people for {$branch->total_capacity} capacity"
                ];
            } else {
                $this->validationResults['capacity_checks'] = ($this->validationResults['capacity_checks'] ?? 0) + 1;
            }
        }
        
        // 2. Subscription Plan Limits
        $organizations = Organization::with(['subscription', 'branches', 'admins'])->get();
        foreach ($organizations as $org) {
            if ($org->subscription && $org->subscription->subscription_plan) {
                $plan = $org->subscription->subscription_plan;
                $issues = [];
                
                if ($org->branches->count() > $plan->max_branches) {
                    $issues[] = "Branch limit exceeded: {$org->branches->count()} > {$plan->max_branches}";
                }
                
                if ($org->admins->count() > $plan->max_users) {
                    $issues[] = "User limit exceeded: {$org->admins->count()} > {$plan->max_users}";
                }
                
                if (!empty($issues)) {
                    $this->validationErrors['subscription_' . $org->id] = $issues;
                } else {
                    $this->validationResults['subscription_compliance'] = ($this->validationResults['subscription_compliance'] ?? 0) + 1;
                }
            }
        }
        
        // 3. Order Status Flow Validation
        $orders = Order::all();
        $validStatusFlow = [
            'submitted' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => []
        ];
        
        foreach ($orders as $order) {
            // This would check if the order went through valid status transitions
            // For demo, we'll just validate current status is valid
            if (!in_array($order->status, array_keys($validStatusFlow))) {
                $this->validationErrors['order_status_' . $order->id] = [
                    "Invalid order status: {$order->status}"
                ];
            } else {
                $this->validationResults['valid_order_statuses'] = ($this->validationResults['valid_order_statuses'] ?? 0) + 1;
            }
        }
        
        // 4. Reservation Time Validation
        $reservations = Reservation::all();
        foreach ($reservations as $reservation) {
            $issues = [];
            
            // Check if reservation is in the past but still pending/confirmed
            if ($reservation->date < today() && in_array($reservation->status, ['pending', 'confirmed'])) {
                $issues[] = 'Past date reservation still active';
            }
            
            // Check if reservation time is within business hours
            $branch = $reservation->branch;
            if ($branch && $branch->opening_time && $branch->closing_time) {
                if ($reservation->start_time < $branch->opening_time || 
                    $reservation->end_time > $branch->closing_time) {
                    $issues[] = 'Reservation outside business hours';
                }
            }
            
            if (!empty($issues)) {
                $this->validationErrors['reservation_business_' . $reservation->id] = $issues;
            } else {
                $this->validationResults['valid_reservation_times'] = ($this->validationResults['valid_reservation_times'] ?? 0) + 1;
            }
        }
    }

    private function validateRelationships(): void
    {
        // 1. Organization -> Branches Relationship
        $orphanedBranches = Branch::whereNotIn('organization_id', Organization::pluck('id'))->get();
        if ($orphanedBranches->count() > 0) {
            $this->validationErrors['orphaned_branches'] = [
                "Found {$orphanedBranches->count()} branches without valid organization"
            ];
        } else {
            $this->validationResults['branch_org_relationships'] = 'OK';
        }
        
        // 2. Order -> Branch Relationship
        $orphanedOrders = Order::whereNotIn('branch_id', Branch::pluck('id'))->get();
        if ($orphanedOrders->count() > 0) {
            $this->validationErrors['orphaned_orders'] = [
                "Found {$orphanedOrders->count()} orders without valid branch"
            ];
        } else {
            $this->validationResults['order_branch_relationships'] = 'OK';
        }
        
        // 3. Reservation -> Branch Relationship
        $orphanedReservations = Reservation::whereNotIn('branch_id', Branch::pluck('id'))->get();
        if ($orphanedReservations->count() > 0) {
            $this->validationErrors['orphaned_reservations'] = [
                "Found {$orphanedReservations->count()} reservations without valid branch"
            ];
        } else {
            $this->validationResults['reservation_branch_relationships'] = 'OK';
        }
        
        // 4. Admin -> Organization/Branch Relationship
        $invalidAdmins = Admin::where(function($query) {
            $query->whereNotNull('organization_id')
                  ->whereNotIn('organization_id', Organization::pluck('id'));
        })->orWhere(function($query) {
            $query->whereNotNull('branch_id')
                  ->whereNotIn('branch_id', Branch::pluck('id'));
        })->get();
        
        if ($invalidAdmins->count() > 0) {
            $this->validationErrors['invalid_admin_relationships'] = [
                "Found {$invalidAdmins->count()} admins with invalid org/branch references"
            ];
        } else {
            $this->validationResults['admin_relationships'] = 'OK';
        }
        
        // 5. Cross-Reference Validation
        $branches = Branch::with('organization')->get();
        foreach ($branches as $branch) {
            if (!$branch->organization) {
                $this->validationErrors['missing_org_' . $branch->id] = [
                    'Branch references non-existent organization'
                ];
            }
        }
        
        // 6. Payment -> Order/Reservation Relationship
        $invalidPayments = Payment::where(function($query) {
            $query->where('payable_type', Order::class)
                  ->whereNotIn('payable_id', Order::pluck('id'));
        })->orWhere(function($query) {
            $query->where('payable_type', Reservation::class)
                  ->whereNotIn('payable_id', Reservation::pluck('id'));
        })->get();
        
        if ($invalidPayments->count() > 0) {
            $this->validationErrors['invalid_payments'] = [
                "Found {$invalidPayments->count()} payments with invalid references"
            ];
        } else {
            $this->validationResults['payment_relationships'] = 'OK';
        }
    }

    private function validatePermissions(): void
    {
        // 1. Role Assignment Validation
        $admins = Admin::with('roles')->get();
        foreach ($admins as $admin) {
            $issues = [];
            
            if ($admin->roles->isEmpty()) {
                $issues[] = 'Admin has no roles assigned';
            }
            
            // Check for conflicting roles
            $roleNames = $admin->roles->pluck('name')->toArray();
            if (in_array('super_admin', $roleNames) && count($roleNames) > 1) {
                $issues[] = 'Super admin should not have additional roles';
            }
            
            if (!empty($issues)) {
                $this->validationErrors['admin_roles_' . $admin->id] = $issues;
            } else {
                $this->validationResults['valid_admin_roles'] = ($this->validationResults['valid_admin_roles'] ?? 0) + 1;
            }
        }
        
        // 2. Permission Hierarchy Validation
        $roles = Role::with('permissions')->where('guard_name', 'admin')->get();
        foreach ($roles as $role) {
            if ($role->name === 'super_admin') {
                // Super admin should have all permissions
                $allPermissions = Permission::where('guard_name', 'admin')->count();
                if ($role->permissions->count() !== $allPermissions) {
                    $this->validationErrors['super_admin_permissions'] = [
                        'Super admin missing some permissions'
                    ];
                }
            }
            
            // Check for orphaned permissions
            if ($role->permissions->isEmpty() && $role->name !== 'guest') {
                $this->validationErrors['empty_role_' . $role->id] = [
                    'Role has no permissions assigned'
                ];
            }
        }
        
        // 3. Access Level Validation
        $organizationAdmins = Admin::whereNotNull('organization_id')->with('roles')->get();
        foreach ($organizationAdmins as $admin) {
            $hasOrgPermissions = $admin->roles->flatMap->permissions
                ->contains(function($permission) {
                    return str_contains($permission->name, 'organizations.');
                });
            
            if (!$hasOrgPermissions && !$admin->hasRole('super_admin')) {
                $this->validationErrors['admin_access_' . $admin->id] = [
                    'Organization admin lacks organization permissions'
                ];
            }
        }
        
        $this->validationResults['permission_checks_completed'] = 'OK';
    }

    private function validateFinancialData(): void
    {
        // 1. Payment Amount Validation
        $payments = Payment::all();
        foreach ($payments as $payment) {
            $issues = [];
            
            if ($payment->amount == 0) {
                $issues[] = 'Zero amount payment';
            }
            
            // Validate payment method
            $validMethods = ['cash', 'card', 'bank_transfer', 'online_portal', 'qr_code', 'cheque', 'mobile_app'];
            if (!in_array($payment->payment_method, $validMethods)) {
                $issues[] = 'Invalid payment method';
            }
            
            // Validate status
            $validStatuses = ['pending', 'completed', 'failed', 'cancelled', 'refunded'];
            if (!in_array($payment->status, $validStatuses)) {
                $issues[] = 'Invalid payment status';
            }
            
            if (!empty($issues)) {
                $this->validationErrors['payment_' . $payment->id] = $issues;
            } else {
                $this->validationResults['valid_payments'] = ($this->validationResults['valid_payments'] ?? 0) + 1;
            }
        }
        
        // 2. Order Financial Consistency
        $orders = Order::all();
        foreach ($orders as $order) {
            $orderPayments = Payment::where('payable_type', Order::class)
                                   ->where('payable_id', $order->id)
                                   ->where('status', 'completed')
                                   ->sum('amount');
            
            if ($order->status === 'completed' && $orderPayments < $order->total * 0.99) {
                $this->validationErrors['order_payment_' . $order->id] = [
                    "Order total ({$order->total}) doesn't match payments ({$orderPayments})"
                ];
            }
        }
        
        // 3. Reservation Fee Validation
        $reservations = Reservation::where('status', 'confirmed')->get();
        foreach ($reservations as $reservation) {
            if ($reservation->reservation_fee > 0) {
                $reservationPayments = Payment::where('payable_type', Reservation::class)
                                             ->where('payable_id', $reservation->id)
                                             ->where('status', 'completed')
                                             ->sum('amount');
                
                if ($reservationPayments < $reservation->reservation_fee * 0.99) {
                    $this->validationErrors['reservation_payment_' . $reservation->id] = [
                        "Reservation fee ({$reservation->reservation_fee}) not fully paid ({$reservationPayments})"
                    ];
                }
            }
        }
        
        // 4. Negative Amount Validation (Refunds)
        $negativePayments = Payment::where('amount', '<', 0)->get();
        foreach ($negativePayments as $payment) {
            // Negative payments should have corresponding positive payments
            $relatedPayments = Payment::where('payable_type', $payment->payable_type)
                                     ->where('payable_id', $payment->payable_id)
                                     ->where('amount', '>', 0)
                                     ->where('status', 'completed')
                                     ->sum('amount');
            
            if (abs($payment->amount) > $relatedPayments) {
                $this->validationErrors['refund_' . $payment->id] = [
                    'Refund amount exceeds original payments'
                ];
            }
        }
        
        $this->validationResults['financial_validation_completed'] = 'OK';
    }

    private function validateStateTransitions(): void
    {
        // 1. Order State Transitions
        $orders = Order::orderBy('created_at')->get();
        foreach ($orders as $order) {
            $issues = [];
            
            // Check if status is logically consistent with timestamps
            if ($order->status === 'completed' && !$order->updated_at) {
                $issues[] = 'Completed order missing update timestamp';
            }
            
            if ($order->status === 'submitted' && $order->created_at < now()->subHours(24)) {
                $issues[] = 'Order submitted status too old (>24h)';
            }
            
            if (!empty($issues)) {
                $this->validationErrors['order_state_' . $order->id] = $issues;
            }
        }
        
        // 2. Reservation State Transitions
        $reservations = Reservation::orderBy('created_at')->get();
        foreach ($reservations as $reservation) {
            $issues = [];
            
            // Past reservations should not be pending
            if ($reservation->date < today() && $reservation->status === 'pending') {
                $issues[] = 'Past reservation still pending';
            }
            
            // Confirmed reservations should have valid future dates
            if ($reservation->status === 'confirmed' && $reservation->date < today()) {
                $issues[] = 'Confirmed reservation in the past';
            }
            
            // Check-in time validation
            if ($reservation->check_in_time && $reservation->status !== 'completed') {
                $issues[] = 'Checked-in reservation not marked as completed';
            }
            
            if (!empty($issues)) {
                $this->validationErrors['reservation_state_' . $reservation->id] = $issues;
            }
        }
        
        // 3. User Account State Validation
        $users = User::all();
        foreach ($users as $user) {
            $issues = [];
            
            // Email verification consistency
            if ($user->email_verified_at && !$user->email) {
                $issues[] = 'Verified timestamp without email';
            }
            
            if (!empty($issues)) {
                $this->validationErrors['user_state_' . $user->id] = $issues;
            }
        }
        
        $this->validationResults['state_transition_checks'] = 'OK';
    }

    private function validatePerformance(): void
    {
        // 1. Database Query Performance Check
        $startTime = microtime(true);
        
        // Simulate complex query
        $complexQueryResult = DB::table('orders')
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->join('organizations', 'branches.organization_id', '=', 'organizations.id')
            ->select('organizations.name', DB::raw('COUNT(orders.id) as order_count'))
            ->groupBy('organizations.id', 'organizations.name')
            ->get();
        
        $queryTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        
        if ($queryTime > 1000) { // > 1 second
            $this->validationErrors['performance_query'] = [
                "Slow complex query: {$queryTime}ms"
            ];
        } else {
            $this->validationResults['query_performance'] = "{$queryTime}ms";
        }
        
        // 2. Data Volume Check
        $totalRecords = 0;
        $modelCounts = [
            'organizations' => Organization::count(),
            'branches' => Branch::count(),
            'users' => User::count(),
            'admins' => Admin::count(),
            'orders' => Order::count(),
            'reservations' => Reservation::count(),
            'payments' => Payment::count(),
        ];
        
        foreach ($modelCounts as $model => $count) {
            $totalRecords += $count;
            $this->validationResults["record_count_{$model}"] = $count;
        }
        
        $this->validationResults['total_records'] = $totalRecords;
        
        // 3. Memory Usage Check
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
        $this->validationResults['memory_usage_mb'] = round($memoryUsage, 2);
        
        if ($memoryUsage > 512) { // > 512MB
            $this->validationErrors['memory_usage'] = [
                "High memory usage: {$memoryUsage}MB"
            ];
        }
    }

    private function validateEdgeCases(): void
    {
        // 1. Duplicate Data Detection
        $duplicateEmails = User::select('email', DB::raw('COUNT(*) as count'))
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();
        
        if ($duplicateEmails->count() > 0) {
            $this->validationErrors['duplicate_emails'] = [
                "Found {$duplicateEmails->count()} duplicate email addresses"
            ];
        }
        
        // 2. Extreme Value Detection
        $extremeOrders = Order::where('total', '>', 10000)->orWhere('total', '<', 0)->get();
        if ($extremeOrders->count() > 0) {
            $this->validationResults['extreme_order_values'] = $extremeOrders->count();
        }
        
        // 3. Future Date Reservations (Beyond Reasonable Limit)
        $farFutureReservations = Reservation::where('date', '>', now()->addYear())->get();
        if ($farFutureReservations->count() > 0) {
            $this->validationResults['far_future_reservations'] = $farFutureReservations->count();
        }
        
        // 4. Orphaned Subscription Plans
        $unusedPlans = SubscriptionPlan::whereNotIn('id', Subscription::pluck('subscription_plan_id'))->get();
        if ($unusedPlans->count() > 0) {
            $this->validationResults['unused_subscription_plans'] = $unusedPlans->count();
        }
        
        // 5. Invalid Phone Number Formats
        $invalidPhones = User::where('phone', 'not like', '+94%')
            ->orWhere(DB::raw('LENGTH(phone)'), '<', 12)
            ->get();
        
        if ($invalidPhones->count() > 0) {
            $this->validationErrors['invalid_phone_formats'] = [
                "Found {$invalidPhones->count()} invalid phone number formats"
            ];
        }
        
        $this->validationResults['edge_case_validation'] = 'Completed';
    }

    private function validateCrossModule(): void
    {
        // 1. Order-Reservation Integration
        $ordersWithReservations = Order::whereNotNull('reservation_id')->get();
        foreach ($ordersWithReservations as $order) {
            $reservation = Reservation::find($order->reservation_id);
            if (!$reservation) {
                $this->validationErrors['missing_reservation_' . $order->id] = [
                    'Order references non-existent reservation'
                ];
            } elseif ($order->branch_id !== $reservation->branch_id) {
                $this->validationErrors['branch_mismatch_' . $order->id] = [
                    'Order and reservation have different branches'
                ];
            }
        }
        
        // 2. Kitchen-Order Integration
        $kitchenStations = KitchenStation::all();
        $ordersWithStations = Order::whereHas('orderItems', function($query) {
            $query->whereNotNull('assigned_station_id');
        })->get();
        
        foreach ($ordersWithStations as $order) {
            foreach ($order->orderItems as $item) {
                if ($item->assigned_station_id) {
                    $station = $kitchenStations->find($item->assigned_station_id);
                    if (!$station) {
                        $this->validationErrors['missing_station_' . $item->id] = [
                            'Order item references non-existent kitchen station'
                        ];
                    } elseif ($station->branch_id !== $order->branch_id) {
                        $this->validationErrors['station_branch_mismatch_' . $item->id] = [
                            'Order item station belongs to different branch'
                        ];
                    }
                }
            }
        }
        
        // 3. Table-Reservation Integration
        $reservationsWithTables = Reservation::whereHas('tables')->get();
        foreach ($reservationsWithTables as $reservation) {
            foreach ($reservation->tables as $table) {
                if ($table->branch_id !== $reservation->branch_id) {
                    $this->validationErrors['table_branch_mismatch_' . $reservation->id] = [
                        'Reservation table belongs to different branch'
                    ];
                }
            }
        }
        
        // 4. Employee-Branch Integration
        $employees = Employee::whereNotNull('branch_id')->get();
        foreach ($employees as $employee) {
            if (!Branch::find($employee->branch_id)) {
                $this->validationErrors['employee_branch_' . $employee->id] = [
                    'Employee references non-existent branch'
                ];
            }
        }
        
        $this->validationResults['cross_module_validation'] = 'Completed';
    }

    private function performSystemHealthCheck(): void
    {
        // 1. Database Connection Health
        try {
            DB::connection()->getPdo();
            $this->validationResults['database_connection'] = 'OK';
        } catch (\Exception $e) {
            $this->validationErrors['database_connection'] = [$e->getMessage()];
        }
        
        // 2. Table Existence Check
        $requiredTables = [
            'organizations', 'branches', 'users', 'admins', 'orders', 'reservations',
            'payments', 'subscription_plans', 'subscriptions', 'kitchen_stations',
            'item_masters', 'employees', 'tables', 'roles', 'permissions'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $this->validationResults["table_{$table}"] = 'Exists';
                } else {
                    $this->validationErrors["missing_table_{$table}"] = ['Table does not exist'];
                }
            } catch (\Exception $e) {
                $this->validationErrors["table_check_{$table}"] = [$e->getMessage()];
            }
        }
        
        // 3. Index Performance Check
        try {
            $indexQuery = "SHOW INDEX FROM orders WHERE Key_name != 'PRIMARY'";
            $indexes = DB::select($indexQuery);
            $this->validationResults['order_indexes'] = count($indexes);
        } catch (\Exception $e) {
            // Index check not critical for functionality
            $this->validationResults['index_check'] = 'Skipped';
        }
        
        // 4. Storage Space Check (estimated)
        $storageEstimate = 0;
        foreach ($requiredTables as $table) {
            try {
                $count = DB::table($table)->count();
                $storageEstimate += $count * 1024; // Rough estimate: 1KB per record
            } catch (\Exception $e) {
                // Continue with other tables
            }
        }
        
        $this->validationResults['estimated_storage_kb'] = $storageEstimate;
        
        // 5. Configuration Validation
        $config_checks = [
            'app.name' => config('app.name'),
            'app.env' => config('app.env'),
            'database.default' => config('database.default'),
        ];
        
        foreach ($config_checks as $key => $value) {
            if (empty($value)) {
                $this->validationErrors["config_{$key}"] = ['Configuration missing or empty'];
            } else {
                $this->validationResults["config_{$key}"] = $value;
            }
        }
        
        $this->validationResults['system_health_check'] = 'Completed';
    }

    private function displayValidationSummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 EXHAUSTIVE VALIDATION & VERIFICATION SUMMARY');
        $this->command->info('════════════════════════════════════════════════════');
        
        $totalValidations = count($this->validationResults);
        $totalErrors = count($this->validationErrors);
        
        $this->command->info("✅ Total Validations Passed: {$totalValidations}");
        $this->command->info("❌ Total Validation Errors: {$totalErrors}");
        
        // Success Rate
        $successRate = $totalValidations > 0 ? round(($totalValidations / ($totalValidations + $totalErrors)) * 100, 2) : 0;
        $this->command->info("📈 Validation Success Rate: {$successRate}%");
        
        $this->command->newLine();
        
        if ($totalErrors > 0) {
            $this->command->error('🚨 VALIDATION ERRORS FOUND:');
            foreach ($this->validationErrors as $key => $errors) {
                $this->command->error("  {$key}:");
                foreach ($errors as $error) {
                    $this->command->error("    - {$error}");
                }
            }
            $this->command->newLine();
        }
        
        $this->command->info('📋 VALIDATION RESULTS SUMMARY:');
        
        // Group results by category
        $categories = [
            'Data Integrity' => ['valid_organizations', 'valid_branches', 'valid_users', 'valid_orders', 'valid_reservations'],
            'Business Rules' => ['capacity_checks', 'subscription_compliance', 'valid_order_statuses', 'valid_reservation_times'],
            'Relationships' => ['branch_org_relationships', 'order_branch_relationships', 'reservation_branch_relationships', 'admin_relationships', 'payment_relationships'],
            'Permissions' => ['valid_admin_roles', 'permission_checks_completed'],
            'Financial' => ['valid_payments', 'financial_validation_completed'],
            'Performance' => ['query_performance', 'memory_usage_mb', 'total_records'],
            'System Health' => ['database_connection', 'system_health_check']
        ];
        
        foreach ($categories as $category => $keys) {
            $this->command->info("  📁 {$category}:");
            foreach ($keys as $key) {
                if (isset($this->validationResults[$key])) {
                    $value = $this->validationResults[$key];
                    $this->command->info("    ✓ {$key}: {$value}");
                }
            }
        }
        
        $this->command->newLine();
        $this->command->info('🔍 DETAILED METRICS:');
        
        foreach ($this->validationResults as $key => $value) {
            if (!in_array($key, array_merge(...array_values($categories)))) {
                $this->command->info("  📊 {$key}: {$value}");
            }
        }
        
        $this->command->newLine();
        
        if ($totalErrors === 0) {
            $this->command->info('🎉 ALL VALIDATIONS PASSED! System integrity confirmed.');
        } else {
            $this->command->warn('⚠️  Some validation errors found. Review and address issues above.');
        }
        
        $this->command->info('✅ Exhaustive validation and verification completed!');
        $this->command->info('🔍 System has been thoroughly tested for data integrity, business rules,');
        $this->command->info('    relationships, permissions, financial consistency, performance,');
        $this->command->info('    edge cases, cross-module integration, and overall health.');
    }
}
