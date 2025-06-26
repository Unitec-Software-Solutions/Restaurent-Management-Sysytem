<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomRole extends SpatieRole
{
    use HasFactory;

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
