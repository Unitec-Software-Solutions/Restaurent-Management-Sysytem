<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'phone',
        'name',
        'email',
        'preferred_contact',
        'date_of_birth',
        'anniversary_date',
        'dietary_preferences',
        'special_notes',
        'is_active',
        'last_visit_date',
        'total_orders',
        'total_spent',
        'loyalty_points'
    ];

    protected $casts = [
        'phone' => 'string',
        'preferred_contact' => 'string',
        'date_of_birth' => 'date',
        'anniversary_date' => 'date',
        'last_visit_date' => 'datetime',
        'is_active' => 'boolean',
        'total_spent' => 'decimal:2',
        'loyalty_points' => 'integer',
        'total_orders' => 'integer'
    ];

    // Define the primary key as phone
    protected $primaryKey = 'phone';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Boot method to handle phone-based primary key
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($customer) {
            // Normalize phone number format
            $customer->phone = self::normalizePhone($customer->phone);
        });
        
        static::updating(function ($customer) {
            // Normalize phone number format
            if ($customer->isDirty('phone')) {
                $customer->phone = self::normalizePhone($customer->phone);
            }
        });
    }

    /**
     * Normalize phone number to consistent format
     */
    public static function normalizePhone($phone)
    {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if missing (assuming Sri Lanka +94)
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            $phone = '94' . substr($phone, 1);
        } elseif (strlen($phone) === 9) {
            $phone = '94' . $phone;
        }
        
        return '+' . $phone;
    }

    /**
     * Relationships
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'phone', 'phone');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_phone', 'phone');
    }

    /**
     * Helper methods
     */
    public function getDisplayNameAttribute()
    {
        return $this->name ?: 'Guest Customer';
    }

    public function getPreferredContactMethodAttribute()
    {
        return $this->preferred_contact ?: 'email';
    }

    public function isPreferredContactSms()
    {
        return $this->preferred_contact === 'sms';
    }

    public function isPreferredContactEmail()
    {
        return $this->preferred_contact === 'email';
    }

    /**
     * Scope for active customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Update customer statistics
     */
    public function updateStats()
    {
        $this->update([
            'total_orders' => $this->orders()->count(),
            'total_spent' => $this->orders()->sum('total_amount'),
            'last_visit_date' => $this->orders()->latest()->first()?->created_at
        ]);
    }

    /**
     * Find or create customer by phone
     */
    public static function findOrCreateByPhone($phone, $additionalData = [])
    {
        $normalizedPhone = self::normalizePhone($phone);
        
        return self::firstOrCreate(
            ['phone' => $normalizedPhone],
            array_merge([
                'is_active' => true,
                'preferred_contact' => 'email',
                'total_orders' => 0,
                'total_spent' => 0,
                'loyalty_points' => 0
            ], $additionalData)
        );
    }
}
