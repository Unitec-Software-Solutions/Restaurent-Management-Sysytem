<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GrnItem extends Model
{
    use HasFactory;

    protected $table = 'grn_items';
    protected $primaryKey = 'grn_item_id';

    protected $fillable = [
        'grn_id',
        'item_id',
        'item_code',
        'item_name',
        'batch_no',
        'ordered_quantity',
        'received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'buying_price',
        'line_total',
        'manufacturing_date',
        'expiry_date',
        'rejection_reason',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'accepted_quantity' => 'decimal:2',
        'rejected_quantity' => 'decimal:2',
        'buying_price' => 'decimal:4',
        'line_total' => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date'
    ];

    protected $appends = [
        'is_complete',
        'is_partial',
        'remaining_quantity',
        'days_until_expiry'
    ];

    // Relationships
    public function grn()
    {
        return $this->belongsTo(GrnMaster::class, 'grn_id', 'grn_id');
    }

    public function purchaseOrderDetail()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_detail_id', 'po_detail_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id', 'id');
    }

    public function itemByCode()
    {
        return $this->belongsTo(ItemMaster::class, 'item_code', 'item_code');
    }

    // Scopes
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }
    

    public function scopeForItemCode($query, $itemCode)
    {
        return $query->where('item_code', $itemCode);
    }

    public function scopeForBatch($query, $batchNo)
    {
        return $query->where('batch_no', $batchNo);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now());
    }

    // Accessors
    public function getIsCompleteAttribute()
    {
        return $this->accepted_quantity >= $this->ordered_quantity;
    }

    public function getIsPartialAttribute()
    {
        return $this->accepted_quantity > 0 && $this->accepted_quantity < $this->ordered_quantity;
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->ordered_quantity - $this->accepted_quantity;
    }

    public function getDaysUntilExpiryAttribute()
    {
        return $this->expiry_date ? now()->diffInDays($this->expiry_date, false) : null;
    }

    public function getExpiryStatusAttribute()
    {
        if (!$this->expiry_date) return 'N/A';
        
        $days = $this->days_until_expiry;
        
        if ($days < 0) return 'Expired';
        if ($days < 30) return 'Expiring Soon';
        return 'Good';
    }

    // Business logic methods
    public function calculateLineTotal()
    {
        $this->line_total = $this->accepted_quantity * $this->buying_price;
        $this->save();
        return $this;
    }

    public function acceptQuantity($quantity, $updateGrnTotal = true)
    {
        $this->accepted_quantity = $quantity;
        $this->rejected_quantity = $this->received_quantity - $quantity;
        $this->calculateLineTotal();
        
        if ($updateGrnTotal) {
            $this->grn->recalculateTotal();
        }
        
        return $this;
    }

    public function rejectItem($reason = null)
    {
        $this->accepted_quantity = 0;
        $this->rejected_quantity = $this->received_quantity;
        $this->rejection_reason = $reason;
        $this->calculateLineTotal();
        $this->grn->recalculateTotal();
        
        return $this;
    }
}