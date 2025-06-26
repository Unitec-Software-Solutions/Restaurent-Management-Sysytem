<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'contact_person_designation',
        'contact_person_phone',
        'is_active',
        'subscription_plan_id',
        'discount_percentage',
        'password',
        'business_type',
        'status',
        'description',
        'website',
        'logo',
        'trading_name',
        'registration_number',
        'alternative_phone',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }
    
    public function activate()
    {
        $this->update(['is_active' => true]);
        $this->branches()->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
        $this->branches()->update(['is_active' => false]);
    }
    
    public function plan()
    {
        return $this->belongsTo(\App\Models\SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(\App\Models\Subscription::class)->where('is_active', true)->latest();
    }

    public function hasFeature(string $feature): bool
    {
        $subscription = $this->currentSubscription;
        return $subscription ? $subscription->hasFeature($feature) : false;
    }

    public function hasModule(string $module): bool
    {
        $subscription = $this->currentSubscription;
        return $subscription ? $subscription->hasModule($module) : false;
    }

    public function getModuleTier(string $module): string
    {
        $subscription = $this->currentSubscription;
        return $subscription ? $subscription->getModuleTier($module) : 'basic';
    }

    public function canAddBranches(): bool
    {
        $plan = $this->plan;
        if (!$plan || !isset($plan->max_branches)) {
            return true; // No limit
        }
        return $this->branches()->count() < $plan->max_branches;
    }

    public function canAddEmployees(): bool
    {
        $plan = $this->plan;
        if (!$plan || !isset($plan->max_employees)) {
            return true; // No limit
        }
        return $this->employees()->count() < $plan->max_employees;
    }

    public function employees()
    {
        return $this->hasMany(\App\Models\Employee::class);
    }
}
