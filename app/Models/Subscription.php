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
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    public function plan()
    {
        return $this->belongsTo(\App\Models\SubscriptionPlan::class, 'plan_id');
    }

    public function latestSubscription()
    {
        return $this->hasOne(\App\Models\Subscription::class)->latestOfMany();
    }
}
