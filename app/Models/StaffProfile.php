<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'date_of_birth',
        'hire_date',
        'position',
        'department',
        'salary',
        'hourly_rate',
        'employment_type',
        'branch_id',
        'organization_id',
        'manager_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'city',
        'state',
        'postal_code',
        'is_active',
        'termination_date',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['full_name'];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function manager()
    {
        return $this->belongsTo(StaffProfile::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(StaffProfile::class, 'manager_id');
    }

    public function shifts()
    {
        return $this->hasMany(StaffShift::class);
    }

    public function attendance()
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function trainingRecords()
    {
        return $this->hasMany(StaffTrainingRecord::class);
    }

    public function trainingSessions()
    {
        return $this->hasMany(StaffTrainingRecord::class, 'trainer_id');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    public function scopeManagers($query)
    {
        return $query->whereNotNull('manager_id');
    }

    // Methods
    public function isManager()
    {
        return $this->subordinates()->exists();
    }

    public function isFullTime()
    {
        return $this->employment_type === 'full_time';
    }

    public function isPartTime()
    {
        return $this->employment_type === 'part_time';
    }

    public function terminate($date = null, $reason = null)
    {
        $this->update([
            'is_active' => false,
            'termination_date' => $date ?: now(),
            'notes' => $this->notes . "\nTermination reason: " . $reason,
        ]);
    }
}
