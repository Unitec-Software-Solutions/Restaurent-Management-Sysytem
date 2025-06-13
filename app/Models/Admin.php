<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Organization;

class Admin extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
        'organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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
     * Determine if the admin is a super admin.
     * Adjust the logic as per your application's super admin identification.
     */
    public function isSuperAdmin()
    {
        return (bool) $this->is_superadmin; 
    }
}
