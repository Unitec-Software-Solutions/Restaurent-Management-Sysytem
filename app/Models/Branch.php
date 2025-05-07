<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'address',
        'phone_number',
        'email',
        'is_head_office',
        'is_active',
        'opening_time',
        'closing_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_head_office' => 'boolean',
    ];

    /**
     * Get the inventory stock for the branch.
     */
    public function inventoryStock()
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * Get the inventory transactions for the branch.
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
} 