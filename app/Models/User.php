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
        'organization_id',
        'name',
        'email',
        'phone_number',
        'password',
        'branch_id',
        'created_by',
        'is_active',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            // Super admins should not be bound to organizations
            if ($user->is_super_admin) {
                $user->organization_id = null;
                $user->branch_id = null;
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard_name = 'admin';

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
            'is_guest' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
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
        return $this->hasRole('Super Admin');
    }

    public function isOrganizationAdmin()
    {
        return !$this->isSuperAdmin()
            && !is_null($this->organization_id)
            && is_null($this->branch_id);
    }

    public function isBranchAdmin()
    {
        return $this->hasRole('Branch Admin');
    }

    // Removed legacy custom role and permission logic. Use Spatie methods only.

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Removed legacy isAdmin. Use Spatie roles/permissions only.
}
