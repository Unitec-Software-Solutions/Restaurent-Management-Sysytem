<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kot extends Model
{
    use HasFactory;

    protected $fillable = [
        'kot_number',
        'order_id',
        'kitchen_station_id',
        'status',
        'priority',
        'prepared_at',
        'served_at',
        'notes',
        'prepared_by',
        'created_by',
        'updated_by',
        'organization_id',
        'branch_id'
    ];

    protected $casts = [
        'prepared_at' => 'timestamp',
        'served_at' => 'timestamp'
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function kitchenStation(): BelongsTo
    {
        return $this->belongsTo(KitchenStation::class);
    }

    public function kotItems(): HasMany
    {
        return $this->hasMany(KotItem::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    // Boot method for auto-generating KOT numbers
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($kot) {
            if (empty($kot->kot_number)) {
                $kot->kot_number = 'KOT-' . date('Ymd') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
