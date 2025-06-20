<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

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
        'branch_id',
        'organization_id',
        'is_active',
        'joined_date',
        'address',
        'emergency_contact',
        'position',
        'salary',
        'notes'
    ];

    protected $casts = [
        'joined_date' => 'datetime',
        'is_active' => 'boolean',
        'salary' => 'decimal:2'
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

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'steward_id');
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

    public function scopeStewards($query)
    {
        return $query->where('role', self::ROLE_STEWARD);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // Helper methods
    public function isSteward()
    {
        return $this->role === self::ROLE_STEWARD;
    }

    public function isChef()
    {
        return $this->role === self::ROLE_CHEF;
    }

    public function isManager()
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->emp_id . ')';
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
}
