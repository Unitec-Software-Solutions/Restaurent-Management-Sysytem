<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\PurchaseOrder;
use App\Models\ItemTransaction; 

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'supplier_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'has_vat_registration',
        'vat_registration_no',
        'is_inactive',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'has_vat_registration' => 'boolean',
        'is_inactive' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the purchase orders for the supplier.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get transactions where this supplier is the source.
     */
    public function transactions()
    {
        return $this->morphMany(ItemTransaction::class, 'source');
    }
} 