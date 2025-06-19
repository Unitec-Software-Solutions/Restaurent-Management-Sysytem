<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone_number',
        'email',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'profile_picture',
        'is_active',
        'notes',
        'loyalty_points',
        'total_spent',
        'visit_count',
        'last_visit_date',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'loyalty_points' => 'integer',
        'total_spent' => 'decimal:2',
        'visit_count' => 'integer',
        'last_visit_date' => 'datetime',
    ];

    protected $appends = ['full_name'];

    // Relationships
    public function authenticationMethods()
    {
        return $this->hasMany(CustomerAuthenticationMethod::class);
    }

    public function preferences()
    {
        return $this->hasOne(CustomerPreference::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVip($query)
    {
        return $query->where('loyalty_points', '>=', 1000);
    }

    public function scopeRecentlyActive($query, $days = 30)
    {
        return $query->where('last_visit_date', '>=', now()->subDays($days));
    }

    // Methods
    public function addLoyaltyPoints($points)
    {
        $this->increment('loyalty_points', $points);
    }

    public function recordVisit()
    {
        $this->increment('visit_count');
        $this->update(['last_visit_date' => now()]);
    }

    public function addSpending($amount)
    {
        $this->increment('total_spent', $amount);
    }
}
