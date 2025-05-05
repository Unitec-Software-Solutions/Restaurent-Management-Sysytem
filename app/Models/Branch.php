<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
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