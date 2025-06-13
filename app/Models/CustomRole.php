<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class CustomRole extends SpatieRole
{
    protected $fillable = ['name', 'organization_id', 'branch_id', 'guard_name'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
