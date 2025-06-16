<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'modules', 'price', 'currency', 'description'];
    protected $casts = [
        'modules' => 'array',
    ];
}
