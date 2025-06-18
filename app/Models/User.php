<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Organization;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'user_type',
        'is_registered',
        'organization_id', 
        'branch_id', 
        'role_id', 
        'is_admin', 
        'created_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
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
            'is_registered' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function hasBranchPermission($branchId, $permission)
    {
        return $this->roles()->where(function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
        })->whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        })->exists();
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('superadmin'); 
    }

    public function is_org_admin()
    {
        return $this->hasRole('organization_admin');
    }

    public function is_branch_admin()
    {
        return $this->hasRole('branch_admin');
    }

    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function canAssignRoles()
    {
        return $this->is_admin || $this->hasPermission('users.assign_roles');
    }

    public function hasPermission($permission)
    {
        if ($this->is_superadmin) return true;
        if ($this->role && $this->role->permissions) {
            return $this->role->permissions->pluck('name')->contains($permission);
        }
        return false;
    }
}
