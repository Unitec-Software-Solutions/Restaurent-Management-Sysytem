<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseOrder;
use App\Models\ItemTransaction;
use App\Models\GrnMaster;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $with = ['organization']; // Always load organization with supplier

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
        'created_at' => 'datetime:Y-m-d H:i:s', // Consistent datetime format
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = ['status']; // If you want to add computed attributes

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class)
            ->with(['branch', 'user', 'grns']) // Include GRNs if needed
            ->orderBy('order_date', 'desc');
    }

    public function transactions()
    {
        return $this->morphMany(ItemTransaction::class, 'source')
            ->whereRaw('source_id::text = ?', [(string) $this->getKey()]) // Use PostgreSQL's "::text" for casting
            ->orderBy('created_at', 'desc');
    }


    // Add if you need GRN relationship
    public function grns()
    {
        return $this->hasManyThrough(
            GrnMaster::class,
            PurchaseOrder::class,
            'supplier_id',  // Foreign key on PurchaseOrder table
            'po_id',        // Foreign key on GrnMaster table
            'id',           // Local key on Supplier table
            'id'            // Local key on PurchaseOrder table
        );
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithPendingPayments($query)
    {
        return $query->whereHas('purchaseOrders', function($q) {
            $q->whereRaw('total_amount > paid_amount');
        });
    }

    // Accessors
    public function getStatusAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getVatStatusAttribute()
    {
        return $this->has_vat_registration
            ? 'VAT Registered ('.$this->vat_registration_no.')'
            : 'Not VAT Registered';
    }

    // Helper methods
    public function totalPurchaseAmount()
    {
        return $this->purchaseOrders()->sum('total_amount');
    }

    public function totalPaidAmount()
    {
        return $this->purchaseOrders()->sum('paid_amount');
    }

    public function pendingPaymentAmount()
    {
        return $this->totalPurchaseAmount() - $this->totalPaidAmount();
    }
}
