<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'modules', 'price', 'currency', 'description', 'is_trial', 'trial_period_days'];
    protected $casts = [
        'modules' => 'array',
        'is_trial' => 'boolean',
    ];
}
