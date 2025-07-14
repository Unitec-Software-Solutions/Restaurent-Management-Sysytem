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
        'is_trial',
        'trial_period_days',
        'is_active',
    ];
    
    protected $casts = [
        'modules' => 'array',
        'features' => 'array',
        'is_trial' => 'boolean',
        'is_active' => 'boolean',
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
        return is_array($this->modules) ? $this->modules : [];
    }

    /**
     * Get modules with names for display purposes
     */
    public function getModulesWithNames(): array
    {
        $modules = $this->getModulesArray();
        $result = [];
        
        // Get all module names for lookup
        $moduleNames = Module::pluck('name', 'id')->toArray();
        
        foreach ($modules as $moduleData) {
            if (is_numeric($moduleData)) {
                // If it's just an ID, look up the name
                $result[] = [
                    'id' => $moduleData,
                    'name' => $moduleNames[$moduleData] ?? 'Unknown Module',
                    'tier' => 'basic'
                ];
            } elseif (is_array($moduleData)) {
                // If it's already an array with name/tier
                if (isset($moduleData['name'])) {
                    $result[] = $moduleData;
                } elseif (isset($moduleData['id'])) {
                    $result[] = [
                        'id' => $moduleData['id'],
                        'name' => $moduleNames[$moduleData['id']] ?? 'Unknown Module',
                        'tier' => $moduleData['tier'] ?? 'basic'
                    ];
                }
            } elseif (is_string($moduleData)) {
                // If it's a string (slug/name), find the module
                $module = Module::where('slug', $moduleData)->orWhere('name', $moduleData)->first();
                $result[] = [
                    'name' => $module ? $module->name : ucfirst($moduleData),
                    'tier' => 'basic'
                ];
            }
        }
        
        return $result;
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class, 'subscription_plan_id');
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id')->where('status', 'active');
    }

    /**
     * Get formatted price with currency symbol
     */
    public function getFormattedPriceAttribute(): string
    {
        // Display price as stored (no division by 100)
        return \App\Helpers\CurrencyHelper::format($this->price, $this->currency ?? 'LKR');
    }

    /**
     * Get currency symbol
     */
    public function getCurrencySymbolAttribute(): string
    {
        return \App\Helpers\CurrencyHelper::getSymbol($this->currency ?? 'LKR');
    }

    /**
     * Get currency name
     */
    public function getCurrencyNameAttribute(): string
    {
        return \App\Helpers\CurrencyHelper::getName($this->currency ?? 'LKR');
    }
}
