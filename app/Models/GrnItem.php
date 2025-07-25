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
        'free_received_quantity',
        'total_to_stock',
        'rejected_quantity',
        'buying_price',
        'line_total',
        'manufacturing_date',
        'expiry_date',
        'rejection_reason',
        'discount_received',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'accepted_quantity' => 'decimal:2',
        'free_received_quantity' => 'decimal:2',
        'total_to_stock' => 'decimal:2',
        'rejected_quantity' => 'decimal:2',
        'buying_price' => 'decimal:4',
        'line_total' => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'discount_received' => 'decimal:2',
    ];

    protected $appends = [
        'is_complete',
        'is_partial',
        'remaining_quantity',
        'days_until_expiry',
        'total_to_stock',
    ];

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

    public function calculateLineTotal()
    {
        $baseAmount = $this->accepted_quantity * $this->buying_price;
        $discountAmount = $baseAmount * (($this->discount_received ?? 0) / 100);
        $this->line_total = $baseAmount - $discountAmount;
        $this->save();
        return $this;
    }

    public function getLineTotalBeforeDiscountAttribute()
    {
        return $this->accepted_quantity * $this->buying_price;
    }

    public function getLineDiscountAmountAttribute()
    {
        return $this->line_total_before_discount * (($this->discount_received ?? 0) / 100);
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

    public function getTotalToStockAttribute()
    {
        return (float) $this->accepted_quantity + (float) $this->free_received_quantity;
    }
}
