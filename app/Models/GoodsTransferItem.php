<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsTransferItem extends Model
{
    use HasFactory;

    protected $table = 'gtn_items';
    protected $primaryKey = 'gtn_item_id';

    protected $fillable = [
        'gtn_id',
        'item_id',
        'item_code',
        'item_name',
        'batch_no',
        'expiry_date',
        'transfer_quantity',
        'received_quantity',
        'damaged_quantity',
        'quantity_accepted',
        'quantity_rejected',
        'transfer_price',
        'line_total',
        'notes',
        'item_rejection_reason',
        'item_status',
        'quality_notes',
        'inspected_by',
        'inspected_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'inspected_at' => 'datetime',
        'transfer_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'quantity_accepted' => 'decimal:2',
        'quantity_rejected' => 'decimal:2',
        'transfer_price' => 'decimal:4',
        'line_total' => 'decimal:4',
        'quality_notes' => 'array',
    ];

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PARTIALLY_ACCEPTED = 'partially_accepted';

    // Relationships

    public function goodsTransferNote()
    {
        return $this->belongsTo(GoodsTransferNote::class, 'gtn_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }

    public function inspectedBy()
    {
        return $this->belongsTo(Employee::class, 'inspected_by');
    }

    // Scopes
    public function scopeAccepted($query)
    {
        return $query->where('item_status', self::STATUS_ACCEPTED);
    }

    public function scopeRejected($query)
    {
        return $query->where('item_status', self::STATUS_REJECTED);
    }

    public function scopePartiallyAccepted($query)
    {
        return $query->where('item_status', self::STATUS_PARTIALLY_ACCEPTED);
    }

    public function scopePending($query)
    {
        return $query->where('item_status', self::STATUS_PENDING);
    }

    // Status Check Methods
    public function isPending()
    {
        return $this->item_status === self::STATUS_PENDING;
    }

    public function isAccepted()
    {
        return $this->item_status === self::STATUS_ACCEPTED;
    }

    public function isRejected()
    {
        return $this->item_status === self::STATUS_REJECTED;
    }

    public function isPartiallyAccepted()
    {
        return $this->item_status === self::STATUS_PARTIALLY_ACCEPTED;
    }

    // Calculated Properties
    public function getAcceptanceRateAttribute()
    {
        return $this->transfer_quantity > 0 ?
               (($this->quantity_accepted ?? 0) / $this->transfer_quantity) * 100 : 0;
    }

    public function getRejectionRateAttribute()
    {
        return $this->transfer_quantity > 0 ?
               (($this->quantity_rejected ?? 0) / $this->transfer_quantity) * 100 : 0;
    }

    public function getAcceptedValueAttribute()
    {
        return ($this->quantity_accepted ?? 0) * $this->transfer_price;
    }

    public function getRejectedValueAttribute()
    {
        return ($this->quantity_rejected ?? 0) * $this->transfer_price;
    }

    // Workflow Methods
    public function accept($quantity = null, $userId = null, $notes = null)
    {
        $acceptedQty = $quantity ?? $this->transfer_quantity;
        $rejectedQty = $this->transfer_quantity - $acceptedQty;

        $this->update([
            'quantity_accepted' => $acceptedQty,
            'quantity_rejected' => $rejectedQty,
            'item_status' => $acceptedQty == $this->transfer_quantity ? self::STATUS_ACCEPTED :
                           ($acceptedQty == 0 ? self::STATUS_REJECTED : self::STATUS_PARTIALLY_ACCEPTED),
            'inspected_by' => $userId,
            'inspected_at' => now(),
            'quality_notes' => $notes ? array_merge($this->quality_notes ?? [], [$notes]) : $this->quality_notes,
        ]);

        return $this;
    }

    public function reject($reason, $userId = null)
    {
        $this->update([
            'quantity_accepted' => 0,
            'quantity_rejected' => $this->transfer_quantity,
            'item_rejection_reason' => $reason,
            'item_status' => self::STATUS_REJECTED,
            'inspected_by' => $userId,
            'inspected_at' => now(),
        ]);

        return $this;
    }

    public function addQualityNote($note, $userId = null)
    {
        $qualityNotes = $this->quality_notes ?? [];
        $qualityNotes[] = [
            'note' => $note,
            'user_id' => $userId,
            'timestamp' => now()->toISOString(),
        ];

        $this->update([
            'quality_notes' => $qualityNotes,
        ]);

        return $this;
    }

    // Validation Methods
    public function validateQuantities()
    {
        $accepted = $this->quantity_accepted ?? 0;
        $rejected = $this->quantity_rejected ?? 0;
        $total = $accepted + $rejected;

        if ($total != $this->transfer_quantity) {
            throw new \Exception(
                "Total accepted ({$accepted}) + rejected ({$rejected}) quantities must equal transfer quantity ({$this->transfer_quantity})"
            );
        }

        return true;
    }
}
