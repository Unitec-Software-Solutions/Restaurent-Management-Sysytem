<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'key',
        'value',
        'type',
        'description',
        'is_active'
    ];

    protected $casts = [
        'value' => 'json',
        'is_active' => 'boolean'
    ];

    /**
     * Relationships
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get configuration value by key
     */
    public static function get($key, $default = null, $branchId = null, $organizationId = null)
    {
        $query = self::where('key', $key)->where('is_active', true);

        // Priority: Branch-specific > Organization-specific > Global
        if ($branchId) {
            $config = $query->where('branch_id', $branchId)->first();
            if ($config) return $config->value;
        }

        if ($organizationId) {
            $config = $query->where('organization_id', $organizationId)->whereNull('branch_id')->first();
            if ($config) return $config->value;
        }

        $config = $query->whereNull('organization_id')->whereNull('branch_id')->first();
        return $config ? $config->value : $default;
    }

    /**
     * Set configuration value
     */
    public static function set($key, $value, $type = 'string', $description = null, $branchId = null, $organizationId = null)
    {
        return self::updateOrCreate(
            [
                'key' => $key,
                'branch_id' => $branchId,
                'organization_id' => $organizationId
            ],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_active' => true
            ]
        );
    }

    /**
     * Get reservation fee configuration
     */
    public static function getReservationFee($reservationType = 'online', $branchId = null, $organizationId = null)
    {
        $key = "reservation_fee_{$reservationType}";
        return (float) self::get($key, 0, $branchId, $organizationId);
    }

    /**
     * Get cancellation fee rules
     */
    public static function getCancellationFeeRules($branchId = null, $organizationId = null)
    {
        $rules = self::get('cancellation_fee_rules', [], $branchId, $organizationId);
        
        if (empty($rules)) {
            return [
                'fee_amount' => 0,
                'hours_before' => 24,
                'percentage_of_reservation_fee' => 100,
                'flat_fee' => 0
            ];
        }

        return $rules;
    }

    /**
     * Calculate cancellation fee for a reservation
     */
    public static function calculateCancellationFee($reservation, $cancelTime = null)
    {
        $cancelTime = $cancelTime ?: now();
        $rules = self::getCancellationFeeRules($reservation->branch_id, $reservation->organization_id);
        
        $hoursUntilReservation = $cancelTime->diffInHours($reservation->reservation_time, false);
        
        if ($hoursUntilReservation >= $rules['hours_before']) {
            return 0; // No fee if cancelled early enough
        }
        
        $fee = 0;
        
        // Calculate percentage of reservation fee
        if ($rules['percentage_of_reservation_fee'] > 0) {
            $fee += ($reservation->reservation_fee * $rules['percentage_of_reservation_fee'] / 100);
        }
        
        // Add flat fee
        if ($rules['flat_fee'] > 0) {
            $fee += $rules['flat_fee'];
        }
        
        return max(0, $fee);
    }

    /**
     * Scope for active configurations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for branch-specific configurations
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope for organization-specific configurations
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
