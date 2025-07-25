<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

class Employee extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

    // Restaurant role constants for the new system
    public const ROLE_HOST_HOSTESS = 'host/hostess';
    public const ROLE_SERVERS = 'servers';
    public const ROLE_BARTENDERS = 'bartenders';
    public const ROLE_CASHIERS = 'cashiers';
    public const ROLE_CHEFS = 'chefs';
    public const ROLE_DISHWASHERS = 'dishwashers';
    public const ROLE_KITCHEN_MANAGERS = 'kitchen-managers';

    // Legacy role constants (for backward compatibility)
    public const ROLE_STEWARD = 'steward';
    public const ROLE_CHEF = 'chef';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'emp_id',
        'name',
        'email',
        'phone',
        'role',
        'role_id',
        'branch_id',
        'organization_id',
        'is_active',
        'joined_date',
        'address',
        'emergency_contact',
        'position',
        'salary',
        'notes',
        // Essential shift and staff fields
        'shift_type',
        'shift_start_time',
        'shift_end_time',
        'hourly_rate',
        'department',
        'availability_status',
        'current_workload'
    ];

    protected $casts = [
        'joined_date' => 'datetime',
        'is_active' => 'boolean',
        'salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'shift_start_time' => 'datetime:H:i',
        'shift_end_time' => 'datetime:H:i',
        'current_workload' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($employee) {
            if (!$employee->emp_id) {
                $employee->emp_id = 'EMP-' . str_pad(
                    Employee::where('organization_id', $employee->organization_id)->count() + 1,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function employeeRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'server_id'); // Changed from steward_id to server_id
    }

    public function createdTransactions()
    {
        return $this->hasMany(ItemTransaction::class, 'created_by_user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeServers($query)
    {
        return $query->whereHas('employeeRole', function($q) {
            $q->where('name', self::ROLE_SERVERS);
        });
    }

    public function scopeByRestaurantRole($query, $roleName)
    {
        return $query->whereHas('employeeRole', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeStewards($query)
    {
        return $query->where('role', self::ROLE_STEWARD);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    
    public function isServer()
    {
        return $this->hasRole(self::ROLE_SERVERS);
    }

    public function isChef()
    {
        return $this->hasRole(self::ROLE_CHEFS);
    }

    public function isHost()
    {
        return $this->hasRole(self::ROLE_HOST_HOSTESS);
    }

    public function isBartender()
    {
        return $this->hasRole(self::ROLE_BARTENDERS);
    }

    public function isCashier()
    {
        return $this->hasRole(self::ROLE_CASHIERS);
    }

    public function isDishwasher()
    {
        return $this->hasRole(self::ROLE_DISHWASHERS);
    }

    public function isKitchenManager()
    {
        return $this->hasRole(self::ROLE_KITCHEN_MANAGERS);
    }

    // Legacy helper methods (for backward compatibility)
    public function isSteward()
    {
        return $this->role === self::ROLE_STEWARD || $this->isServer();
    }

    public function isManager()
    {
        return $this->role === self::ROLE_MANAGER || $this->isKitchenManager();
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->emp_id . ')';
    }

    public function getRoleNameAttribute()
    {
        if ($this->employeeRole) {
            return ucwords(str_replace(['-', '/'], ' ', $this->employeeRole->name));
        }
        return $this->role ? ucfirst($this->role) : 'No Role';
    }

    public static function getAvailableRestaurantRoles()
    {
        return [
            self::ROLE_HOST_HOSTESS => 'Host/Hostess',
            self::ROLE_SERVERS => 'Servers',
            self::ROLE_BARTENDERS => 'Bartenders',
            self::ROLE_CASHIERS => 'Cashiers',
            self::ROLE_CHEFS => 'Chefs',
            self::ROLE_DISHWASHERS => 'Dishwashers',
            self::ROLE_KITCHEN_MANAGERS => 'Kitchen Managers'
        ];
    }

    public static function getAvailableRoles()
    {
        return [
            self::ROLE_STEWARD => 'Steward',
            self::ROLE_CHEF => 'Chef',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_ADMIN => 'Admin'
        ];
    }

    /**
     * Check if employee can perform specific restaurant operations
     */
    public function canTakeOrders()
    {
        return $this->can('take-orders');
    }

    public function canProcessPayments()
    {
        return $this->can('process-payments');
    }

    public function canManageReservations()
    {
        return $this->can('manage-reservations');
    }

    public function canAccessKitchen()
    {
        return $this->can('kitchen-operations') || $this->can('view-kitchen-orders');
    }

    // Essential shift and staff management methods
    public function isAvailable()
    {
        return $this->availability_status === 'available' && $this->is_active;
    }

    public function isOnShift($time = null)
    {
        if (!$this->shift_start_time || !$this->shift_end_time) {
            return true; // Flexible shift
        }

        $time = $time ?: now()->format('H:i');
        return $time >= $this->shift_start_time && $time <= $this->shift_end_time;
    }

    public function canTakeOrder()
    {
        return $this->isAvailable() && $this->isOnShift() && 
               $this->current_workload < 10; // Default max workload
    }

    public function assignOrder()
    {
        $this->increment('current_workload');
        if ($this->current_workload >= 10) {
            $this->update(['availability_status' => 'busy']);
        }
    }

    public function completeOrder()
    {
        if ($this->current_workload > 0) {
            $this->decrement('current_workload');
            if ($this->current_workload < 10 && $this->availability_status === 'busy') {
                $this->update(['availability_status' => 'available']);
            }
        }
    }

    public function setOnBreak()
    {
        $this->update(['availability_status' => 'on_break']);
    }

    public function setOffDuty()
    {
        $this->update(['availability_status' => 'off_duty', 'current_workload' => 0]);
    }

    public function setAvailable()
    {
        $this->update(['availability_status' => 'available']);
    }

    // Scope for shift management
    public function scopeByShift($query, $shiftType)
    {
        return $query->where('shift_type', $shiftType);
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available')
                     ->where('is_active', true);
    }

    public function scopeOnDuty($query)
    {
        return $query->whereIn('availability_status', ['available', 'busy']);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }
}
