<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'organization_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'is_trial'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_trial' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    public function plan()
    {
        return $this->belongsTo(\App\Models\SubscriptionPlan::class, 'plan_id');
    }

    public function hasFeature(string $feature): bool
    {
        return $this->plan->hasFeature($feature);
    }

    public function hasModule(string $module): bool
    {
        return $this->plan->hasModule($module);
    }

    public function getModuleTier(string $module): string
    {
        return $this->plan->getModuleTier($module);
    }

    public function isActive(): bool
    {
        return $this->is_active && 
               $this->status === 'active' && 
               $this->end_date->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    public function latestSubscription()
    {
        return $this->hasOne(\App\Models\Subscription::class)->latestOfMany();
    }
}
