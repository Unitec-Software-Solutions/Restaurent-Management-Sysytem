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
        $modules = $this->getModulesArray();
        
        // Handle different module formats
        foreach ($modules as $moduleData) {
            if (is_string($moduleData) && $moduleData === $module) {
                return true;
            } elseif (is_array($moduleData) && isset($moduleData['name']) && $moduleData['name'] === $module) {
                return true;
            }
        }
        
        return false;
    }

    public function getModuleTier(string $module): string
    {
        $modules = $this->getModulesArray();
        
        foreach ($modules as $moduleData) {
            if (is_array($moduleData) && isset($moduleData['name']) && $moduleData['name'] === $module) {
                return $moduleData['tier'] ?? 'basic';
            }
        }
        
        return 'basic';
    }

    /**
     * Get modules as array with safe JSON handling
     */
    public function getModulesArray(): array
    {
        if (is_array($this->modules)) {
            return $this->modules;
        }
        
        if (is_string($this->modules)) {
            $decoded = json_decode($this->modules, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return [];
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
