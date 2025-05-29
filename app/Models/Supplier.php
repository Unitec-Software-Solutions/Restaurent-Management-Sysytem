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

    protected $fillable = [
        'organization_id',
        'supplier_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'has_vat_registration',
        'vat_registration_no',
        'is_active',
    ];

    protected $casts = [
        'has_vat_registration' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function transactions()
    {
        return $this->morphMany(ItemTransaction::class, 'source');
    }

    public function organization()
    {
        return $this->belongsTo(Organizations::class);
    }

    // Scope to filter by organization
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
    

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}