<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'modules', 
        'price', 
        'currency', 
        'description', 
        'is_trial', 
        'trial_period_days',
        'max_branches',
        'max_employees',
        'features'
    ];
    
    protected $casts = [
        'modules' => 'array',
        'features' => 'array',
        'is_trial' => 'boolean',
    ];

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->modules ?? []);
    }

    public function getModuleTier(string $module): string
    {
        $modules = $this->modules ?? [];
        foreach ($modules as $moduleConfig) {
            if (is_array($moduleConfig) && isset($moduleConfig['name']) && $moduleConfig['name'] === $module) {
                return $moduleConfig['tier'] ?? 'basic';
            }
        }
        return 'basic';
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
