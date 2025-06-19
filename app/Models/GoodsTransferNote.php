<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GoodsTransferNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gtn_master';
    protected $primaryKey = 'gtn_id';

    protected $fillable = [
        'gtn_number',
        'from_branch_id',
        'to_branch_id',
        'created_by',
        'approved_by',
        'organization_id',
        'transfer_date',
        'status',
        'origin_status',
        'receiver_status',
        'confirmed_at',
        'delivered_at',
        'received_at',
        'verified_at',
        'accepted_at',
        'rejection_reason',
        'rejected_by',
        'rejected_at',
        'verified_by',
        'received_by',
        'notes',
        'total_value',
        'is_active',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
        'verified_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'total_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Status Constants
    const ORIGIN_STATUS_DRAFT = 'draft';
    const ORIGIN_STATUS_CONFIRMED = 'confirmed';
    const ORIGIN_STATUS_IN_DELIVERY = 'in_delivery';
    const ORIGIN_STATUS_DELIVERED = 'delivered';

    const RECEIVER_STATUS_PENDING = 'pending';
    const RECEIVER_STATUS_RECEIVED = 'received';
    const RECEIVER_STATUS_VERIFIED = 'verified';
    const RECEIVER_STATUS_ACCEPTED = 'accepted';
    const RECEIVER_STATUS_REJECTED = 'rejected';
    const RECEIVER_STATUS_PARTIALLY_ACCEPTED = 'partially_accepted';

    // Transaction Type Constants for ItemTransaction
    const TRANSACTION_TYPE_GTN_OUTGOING = 'gtn_outgoing';
    const TRANSACTION_TYPE_GTN_INCOMING = 'gtn_incoming';
    const TRANSACTION_TYPE_GTN_REJECTION = 'gtn_rejection';

    // Relationships

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organizations::class, 'organization_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(GoodsTransferItem::class, 'gtn_id');
    }

    // Additional Relationships for workflow tracking
    public function rejectedBy()
    {
        return $this->belongsTo(Employee::class, 'rejected_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(ItemTransaction::class, 'gtn_id', 'gtn_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByOriginStatus($query, $status)
    {
        return $query->where('origin_status', $status);
    }

    public function scopeByReceiverStatus($query, $status)
    {
        return $query->where('receiver_status', $status);
    }

    // Workflow State Checks
    public function isDraft()
    {
        return $this->origin_status === self::ORIGIN_STATUS_DRAFT;
    }

    public function isConfirmed()
    {
        return $this->origin_status === self::ORIGIN_STATUS_CONFIRMED;
    }

    public function isDelivered()
    {
        return $this->origin_status === self::ORIGIN_STATUS_DELIVERED;
    }

    public function isPending()
    {
        return $this->receiver_status === self::RECEIVER_STATUS_PENDING;
    }

    public function isReceived()
    {
        return $this->receiver_status === self::RECEIVER_STATUS_RECEIVED;
    }

    public function isVerified()
    {
        return $this->receiver_status === self::RECEIVER_STATUS_VERIFIED;
    }

    public function isAccepted()
    {
        return $this->receiver_status === self::RECEIVER_STATUS_ACCEPTED;
    }

    public function isRejected()
    {
        return $this->receiver_status === self::RECEIVER_STATUS_REJECTED;
    }

    public function isPartiallyAccepted()
    {
        return $this->receiver_status === self::RECEIVER_STATUS_PARTIALLY_ACCEPTED;
    }

    // Workflow Transition Methods
    public function confirmTransfer($userId)
    {
        if (!$this->isDraft()) {
            throw new Exception('GTN can only be confirmed from draft status');
        }

        return DB::transaction(function () use ($userId) {
            // Update GTN status
            $this->update([
                'origin_status' => self::ORIGIN_STATUS_CONFIRMED,
                'receiver_status' => self::RECEIVER_STATUS_PENDING,
                'confirmed_at' => now(),
                'approved_by' => $userId,
            ]);

            // Create outgoing inventory transactions (deduct stock from sender)
            $this->createOutgoingInventoryTransactions($userId);

            Log::info('GTN confirmed successfully', [
                'gtn_id' => $this->gtn_id,
                'gtn_number' => $this->gtn_number,
                'user_id' => $userId
            ]);

            return $this;
        });
    }

    public function receiveTransfer($userId, $notes = null)
    {
        if (!$this->isPending()) {
            throw new Exception('GTN can only be received from pending status');
        }

        $this->update([
            'receiver_status' => self::RECEIVER_STATUS_RECEIVED,
            'received_at' => now(),
            'received_by' => $userId,
            'notes' => $notes ? ($this->notes . "\n" . $notes) : $this->notes,
        ]);

        Log::info('GTN received', [
            'gtn_id' => $this->gtn_id,
            'user_id' => $userId
        ]);

        return $this;
    }

    public function verifyTransfer($userId, $notes = null)
    {
        if (!$this->isReceived()) {
            throw new Exception('GTN can only be verified from received status');
        }

        $this->update([
            'receiver_status' => self::RECEIVER_STATUS_VERIFIED,
            'verified_at' => now(),
            'verified_by' => $userId,
            'notes' => $notes ? ($this->notes . "\n" . $notes) : $this->notes,
        ]);

        Log::info('GTN verified', [
            'gtn_id' => $this->gtn_id,
            'user_id' => $userId
        ]);

        return $this;
    }

    public function acceptTransfer($userId, $acceptanceData = [])
    {
        if (!$this->isVerified()) {
            throw new Exception('GTN can only be accepted from verified status');
        }

        return DB::transaction(function () use ($userId, $acceptanceData) {
            $totalAccepted = 0;
            $totalRejected = 0;
            $allAccepted = true;
            $anyAccepted = false;

            // Process each item's acceptance/rejection
            foreach ($this->items as $item) {
                $itemData = $acceptanceData[$item->gtn_item_id] ?? [];
                $acceptedQty = $itemData['quantity_accepted'] ?? $item->transfer_quantity;
                $rejectedQty = $item->transfer_quantity - $acceptedQty;

                if ($acceptedQty < $item->transfer_quantity) {
                    $allAccepted = false;
                }
                if ($acceptedQty > 0) {
                    $anyAccepted = true;
                }

                $item->update([
                    'quantity_accepted' => $acceptedQty,
                    'quantity_rejected' => $rejectedQty,
                    'item_rejection_reason' => $itemData['rejection_reason'] ?? null,
                    'item_status' => $acceptedQty == $item->transfer_quantity ? 'accepted' :
                                   ($acceptedQty == 0 ? 'rejected' : 'partially_accepted'),
                    'inspected_by' => $userId,
                    'inspected_at' => now(),
                ]);

                $totalAccepted += $acceptedQty;
                $totalRejected += $rejectedQty;
            }

            // Determine final status
            $finalStatus = $allAccepted ? self::RECEIVER_STATUS_ACCEPTED :
                          ($anyAccepted ? self::RECEIVER_STATUS_PARTIALLY_ACCEPTED : self::RECEIVER_STATUS_REJECTED);

            // Update GTN status
            $this->update([
                'receiver_status' => $finalStatus,
                'origin_status' => self::ORIGIN_STATUS_DELIVERED,
                'accepted_at' => now(),
                'verified_by' => $userId,
            ]);

            // Create incoming inventory transactions (add accepted stock to receiver)
            $this->createIncomingInventoryTransactions($userId);

            // Create rejection transactions (return rejected stock to sender)
            if ($totalRejected > 0) {
                $this->createRejectionInventoryTransactions($userId);
            }

            Log::info('GTN acceptance processed', [
                'gtn_id' => $this->gtn_id,
                'final_status' => $finalStatus,
                'total_accepted' => $totalAccepted,
                'total_rejected' => $totalRejected,
                'user_id' => $userId
            ]);

            return $this;
        });
    }

    public function rejectTransfer($userId, $rejectionReason)
    {
        if (!in_array($this->receiver_status, [self::RECEIVER_STATUS_RECEIVED, self::RECEIVER_STATUS_VERIFIED])) {
            throw new Exception('GTN can only be rejected from received or verified status');
        }

        return DB::transaction(function () use ($userId, $rejectionReason) {
            // Mark all items as rejected
            $this->items()->update([
                'quantity_accepted' => 0,
                'quantity_rejected' => DB::raw('transfer_quantity'),
                'item_rejection_reason' => $rejectionReason,
                'item_status' => 'rejected',
                'inspected_by' => $userId,
                'inspected_at' => now(),
            ]);

            // Update GTN status
            $this->update([
                'receiver_status' => self::RECEIVER_STATUS_REJECTED,
                'origin_status' => self::ORIGIN_STATUS_DELIVERED,
                'rejection_reason' => $rejectionReason,
                'rejected_by' => $userId,
                'rejected_at' => now(),
            ]);

            // Return all stock to sender
            $this->createRejectionInventoryTransactions($userId);

            Log::info('GTN fully rejected', [
                'gtn_id' => $this->gtn_id,
                'reason' => $rejectionReason,
                'user_id' => $userId
            ]);

            return $this;
        });
    }

    // Inventory Transaction Creation Methods
    protected function createOutgoingInventoryTransactions($userId)
    {
        foreach ($this->items as $item) {
            ItemTransaction::create([
                'organization_id' => $this->organization_id,
                'branch_id' => $this->from_branch_id,
                'inventory_item_id' => $item->item_id,
                'transaction_type' => self::TRANSACTION_TYPE_GTN_OUTGOING,
                'quantity' => -abs($item->transfer_quantity), // Negative for stock deduction
                'cost_price' => $item->transfer_price,
                'source_id' => $this->gtn_id,
                'source_type' => 'GTN',
                'gtn_id' => $this->gtn_id,
                'created_by_user_id' => $userId,
                'notes' => "Stock deducted for GTN: {$this->gtn_number}",
                'is_active' => true,
            ]);
        }
    }

    protected function createIncomingInventoryTransactions($userId)
    {
        foreach ($this->items as $item) {
            if ($item->quantity_accepted > 0) {
                ItemTransaction::create([
                    'organization_id' => $this->organization_id,
                    'branch_id' => $this->to_branch_id,
                    'inventory_item_id' => $item->item_id,
                    'transaction_type' => self::TRANSACTION_TYPE_GTN_INCOMING,
                    'quantity' => $item->quantity_accepted, // Positive for stock addition
                    'received_quantity' => $item->quantity_accepted,
                    'cost_price' => $item->transfer_price,
                    'source_id' => $this->gtn_id,
                    'source_type' => 'GTN',
                    'gtn_id' => $this->gtn_id,
                    'created_by_user_id' => $userId,
                    'verified_by' => $userId,
                    'notes' => "Stock received from GTN: {$this->gtn_number}",
                    'is_active' => true,
                ]);
            }
        }
    }

    protected function createRejectionInventoryTransactions($userId)
    {
        foreach ($this->items as $item) {
            if ($item->quantity_rejected > 0) {
                ItemTransaction::create([
                    'organization_id' => $this->organization_id,
                    'branch_id' => $this->from_branch_id,
                    'inventory_item_id' => $item->item_id,
                    'transaction_type' => self::TRANSACTION_TYPE_GTN_REJECTION,
                    'quantity' => $item->quantity_rejected, // Positive for stock return
                    'damaged_quantity' => 0,
                    'cost_price' => $item->transfer_price,
                    'source_id' => $this->gtn_id,
                    'source_type' => 'GTN',
                    'gtn_id' => $this->gtn_id,
                    'created_by_user_id' => $userId,
                    'notes' => "Stock returned due to rejection - GTN: {$this->gtn_number}. Reason: {$item->item_rejection_reason}",
                    'is_active' => true,
                ]);
            }
        }
    }

    // Utility Methods
    public function getTotalTransferValue()
    {
        return $this->total_value ?? $this->items->sum('line_total');
    }

    public function getTotalAcceptedValue()
    {
        return $this->items->sum(function ($item) {
            return ($item->quantity_accepted ?? 0) * $item->transfer_price;
        });
    }

    public function getTotalRejectedValue()
    {
        return $this->items->sum(function ($item) {
            return ($item->quantity_rejected ?? 0) * $item->transfer_price;
        });
    }

    public function getAcceptanceRate()
    {
        $totalTransfer = $this->items->sum('transfer_quantity');
        $totalAccepted = $this->items->sum('quantity_accepted');

        return $totalTransfer > 0 ? ($totalAccepted / $totalTransfer) * 100 : 0;
    }

    public function recalculateTotalValue()
    {
        $totalValue = $this->items->sum('line_total');
        $this->update(['total_value' => $totalValue]);
        return $totalValue;
    }
}
