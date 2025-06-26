<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'permissions', 'is_active'];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_module')
                    ->withPivot('permissions');
    }
}
