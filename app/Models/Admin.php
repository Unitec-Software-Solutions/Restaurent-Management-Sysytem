<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

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
        return $this->belongsTo(Organizations::class);
    }
}
