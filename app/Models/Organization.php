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
}
