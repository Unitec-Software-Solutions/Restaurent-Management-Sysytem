<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Organization;
use App\Models\Branch;


class Admin extends Authenticatable
{
    use Notifiable, HasRoles, HasFactory;

    protected $guard_name = 'admin'; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
        'organization_id',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    // Check if the admin is a super admin
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /**
     * Determine if the admin is an organization admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        // If you use Spatie roles:
        return $this->hasRole('Organization Admin');
    }

    // Check if the admin is a branch admin
    public function isBranchAdmin(): bool
    {
        return $this->hasRole('Branch Admin');
    }

    /**
     * Get the branch that the admin belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the organization that the admin belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the roles assigned to the admin (Spatie roles).
     */
    public function getRoleNamesList(): array
    {
        return $this->getRoleNames()->toArray();
    }

    /**
     * Check if the admin is active (if you have an 'active' column).
     */
    public function isActive(): bool
    {
        return property_exists($this, 'active') ? (bool) $this->active : true;
    }
}
