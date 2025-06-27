<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable following UI/UX guidelines.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_active'
    ];

    /**
     * Get the attributes that should be cast following UI/UX data types.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array'
    ];

    /**
     * Relationships
     */
    public function subscriptionPlans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'subscription_plan_modules');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'module_permissions');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
