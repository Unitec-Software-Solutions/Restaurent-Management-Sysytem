<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'trading_name',
        'registration_number',
        'description',
        'email',
        'password', 
        'phone',
        'alternative_phone',
        'address',
        'website',
        'logo',
        'business_hours',
        'business_type',
        'status',
        'is_active',
        'activation_key',
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
        return $this->hasMany(Subscription::class);
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
}
