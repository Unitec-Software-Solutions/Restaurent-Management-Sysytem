<?php

namespace App\Services;

use App\Models\GoodsTransferNote;
use App\Models\GoodsTransferItem;
use App\Models\ItemTransaction;
use App\Models\ItemMaster;
use App\Models\Branch;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class GTNService
{
    protected $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Validate cumulative item stock availability (enhanced version)
     */
    public function validateCumulativeItemStock($items, $branchId)
    {
        $itemQuantities = [];

        // Calculate cumulative quantities for each item
        foreach ($items as $item) {
            $itemId = $item['item_id'];
            $quantity = $item['transfer_quantity'];

            if (isset($itemQuantities[$itemId])) {
                $itemQuantities[$itemId] += $quantity;
            } else {
                $itemQuantities[$itemId] = $quantity;
            }
        }

        // Validate each unique item's cumulative quantity against available stock
        foreach ($itemQuantities as $itemId => $totalQuantity) {
            $currentStock = ItemTransaction::stockOnHand($itemId, $branchId);

            if ($currentStock < $totalQuantity) {
                $item = ItemMaster::find($itemId);
                throw new Exception(
                    "Insufficient stock for item '{$item->name}'. Available: {$currentStock}, Total Required: {$totalQuantity}"
                );
            }
        }

        return true;
    }

    /**
     * Create a new GTN with items
     */
    public function createGTN(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            if (!$user) {
                throw new Exception('Unauthorized access - user not authenticated');
            }

            // Handle super admin organization ID
            if ($user->is_super_admin) {
                // For super admin, organization_id should be provided in data
                if (!isset($data['organization_id']) || !$data['organization_id']) {
                    throw new Exception('Organization ID is required for super admin GTN creation');
                }
                $organizationId = $data['organization_id'];
            } else {
                // For regular admin, use their organization_id
                if (!$user->organization_id) {
                    throw new Exception('Unauthorized access - organization not set');
                }
                $organizationId = $user->organization_id;
            }

            // Validate cumulative stock for all items
            $this->validateCumulativeItemStock($data['items'], $data['from_branch_id']);

            // Get or create employee record
            $employee = $this->employeeRepository->findOrCreateForUser($user, $data['from_branch_id'], $organizationId);

            // Create the GTN record
            $gtn = GoodsTransferNote::create([
                'gtn_number' => $data['gtn_number'] ?? $this->generateGTNNumber($organizationId),
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'created_by' => $employee->id,
                'organization_id' => $organizationId,
                'transfer_date' => $data['transfer_date'] ?? now(),
                'origin_status' => GoodsTransferNote::ORIGIN_STATUS_DRAFT,
                'receiver_status' => GoodsTransferNote::RECEIVER_STATUS_PENDING,
                'status' => 'Pending', // Keep for backward compatibility
                'notes' => $data['notes'] ?? null,
                'is_active' => true,
            ]);

            $totalValue = 0;

            // Add items to the GTN
            foreach ($data['items'] as $itemData) {
                $this->validateItemStock($itemData['item_id'], $data['from_branch_id'], $itemData['transfer_quantity']);

                $item = ItemMaster::find($itemData['item_id']);

                // Always use item's buying_price as transfer_price, ignore any provided transfer_price
                $transferPrice = $item->buying_price ?? 0;
                $lineTotal = $itemData['transfer_quantity'] * $transferPrice;
                $totalValue += $lineTotal;

                GoodsTransferItem::create([
                    'gtn_id' => $gtn->gtn_id,
                    'item_id' => $itemData['item_id'],
                    'item_code' => $item->item_code,
                    'item_name' => $item->name,
                    'batch_no' => $itemData['batch_no'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'transfer_quantity' => $itemData['transfer_quantity'],
                    'transfer_price' => $transferPrice,
                    'line_total' => $lineTotal,
                    'notes' => $itemData['notes'] ?? null,
                    'item_status' => GoodsTransferItem::STATUS_PENDING,
                ]);
            }

            // Update GTN with total value
            $gtn->update(['total_value' => $totalValue]);

            Log::info('GTN created successfully', [
                'gtn_id' => $gtn->gtn_id,
                'gtn_number' => $gtn->gtn_number,
                'from_branch' => $data['from_branch_id'],
                'to_branch' => $data['to_branch_id'],
                'items_count' => count($data['items']),
                'total_value' => $totalValue
            ]);

            return $gtn;
        });
    }

    /**
     * Update an existing GTN (only in draft status)
     */
    public function updateGTN($gtnId, array $data)
    {
        return DB::transaction(function () use ($gtnId, $data) {
            $admin = Auth::user();

            if (!$admin) {
                throw new Exception('Unauthorized access - user not authenticated');
            }

            // Handle super admin organization ID
            if ($admin->is_super_admin) {
                // For super admin, they can update any GTN
                $gtn = GoodsTransferNote::findOrFail($gtnId);
            } else {
                // For regular admin, use their organization_id
                if (!$admin->organization_id) {
                    throw new Exception('Unauthorized access - organization not set');
                }
                $gtn = GoodsTransferNote::where('organization_id', $admin->organization_id)
                    ->findOrFail($gtnId);
            }

            if (!$gtn->isDraft()) {
                throw new Exception('GTN can only be updated in draft status');
            }

            // Update GTN details
            $gtn->update([
                'from_branch_id' => $data['from_branch_id'] ?? $gtn->from_branch_id,
                'to_branch_id' => $data['to_branch_id'] ?? $gtn->to_branch_id,
                'transfer_date' => $data['transfer_date'] ?? $gtn->transfer_date,
                'notes' => $data['notes'] ?? $gtn->notes,
            ]);

            $totalValue = 0;

            // Update items if provided
            if (isset($data['items'])) {
                // Validate cumulative stock for all items
                $this->validateCumulativeItemStock($data['items'], $gtn->from_branch_id);

                // Delete existing items
                $gtn->items()->delete();

                // Add new items
                foreach ($data['items'] as $itemData) {
                    $this->validateItemStock($itemData['item_id'], $gtn->from_branch_id, $itemData['transfer_quantity']);

                    $item = ItemMaster::find($itemData['item_id']);

                    // Always use item's buying_price as transfer_price, ignore any provided transfer_price
                    $transferPrice = $item->buying_price ?? 0;
                    $lineTotal = $itemData['transfer_quantity'] * $transferPrice;
                    $totalValue += $lineTotal;

                    GoodsTransferItem::create([
                        'gtn_id' => $gtn->gtn_id,
                        'item_id' => $itemData['item_id'],
                        'item_code' => $item->item_code,
                        'item_name' => $item->name,
                        'batch_no' => $itemData['batch_no'] ?? null,
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'transfer_quantity' => $itemData['transfer_quantity'],
                        'transfer_price' => $transferPrice,
                        'line_total' => $lineTotal,
                        'notes' => $itemData['notes'] ?? null,
                        'item_status' => GoodsTransferItem::STATUS_PENDING,
                    ]);
                }

                // Update GTN with total value
                $gtn->update(['total_value' => $totalValue]);
            }

            Log::info('GTN updated successfully', [
                'gtn_id' => $gtn->gtn_id,
                'gtn_number' => $gtn->gtn_number,
                'total_value' => $totalValue
            ]);

            return $gtn;
        });
    }

    /**
     * Confirm GTN and deduct stock from sender
     */
    public function confirmGTN($gtnId, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $gtn = GoodsTransferNote::findOrFail($gtnId);

        if (!$gtn->isDraft()) {
            throw new Exception('GTN can only be confirmed from draft status');
        }

        // Validate stock availability before confirmation
        foreach ($gtn->items as $item) {
            $this->validateItemStock($item->item_id, $gtn->from_branch_id, $item->transfer_quantity);
        }

        return $gtn->confirmTransfer($userId);
    }

    /**
     * Mark GTN as received
     */
    public function receiveGTN($gtnId, $userId = null, $notes = null)
    {
        $userId = $userId ?? Auth::id();
        $gtn = GoodsTransferNote::findOrFail($gtnId);

        return $gtn->receiveTransfer($userId, $notes);
    }

    /**
     * Verify GTN items
     */
    public function verifyGTN($gtnId, $userId = null, $notes = null)
    {
        $userId = $userId ?? Auth::id();
        $gtn = GoodsTransferNote::findOrFail($gtnId);

        return $gtn->verifyTransfer($userId, $notes);
    }

    /**
     * Accept/Reject GTN items
     */
    public function processGTNAcceptance($gtnId, array $acceptanceData, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $gtn = GoodsTransferNote::findOrFail($gtnId);

        return $gtn->acceptTransfer($userId, $acceptanceData);
    }

    /**
     * Fully reject GTN
     */
    public function rejectGTN($gtnId, $rejectionReason, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $gtn = GoodsTransferNote::findOrFail($gtnId);

        return $gtn->rejectTransfer($userId, $rejectionReason);
    }

    /**
     * Get items with current stock for a branch
     */
    public function getItemsWithStock($branchId, $organizationId)
    {
        $items = ItemMaster::where('organization_id', $organizationId)
                          ->where('is_active', true)
                          ->with('category')
                          ->get();

        return $items->map(function ($item) use ($branchId) {
            $currentStock = ItemTransaction::stockOnHand($item->id, $branchId);

            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_code' => $item->item_code,
                'category' => $item->category->name ?? 'Uncategorized',
                'unit_of_measurement' => $item->unit_of_measurement,
                'current_stock' => $currentStock,
                'stock_on_hand' => $currentStock, // For backward compatibility
                'buying_price' => $item->buying_price,
                'selling_price' => $item->selling_price,
                'reorder_level' => $item->reorder_level,
                'is_low_stock' => $currentStock <= $item->reorder_level,
                'can_transfer' => $currentStock > 0,
                'max_transfer' => $currentStock,
            ];
        })->filter(function ($item) {
            return $item['can_transfer']; // Only return items with stock
        })->values();
    }

    /**
     * Search items with stock
     */
    public function searchItemsWithStock($branchId, $organizationId, $search = '')
    {
        $query = ItemMaster::where('organization_id', $organizationId)
                          ->where('is_active', true)
                          ->with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $items = $query->limit(20)->get();

        return $items->map(function ($item) use ($branchId) {
            $currentStock = ItemTransaction::stockOnHand($item->id, $branchId);

            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_code' => $item->item_code,
                'category' => $item->category->name ?? 'Uncategorized',
                'unit_of_measurement' => $item->unit_of_measurement,
                'current_stock' => $currentStock,
                'stock_on_hand' => $currentStock, // For backward compatibility
                'buying_price' => $item->buying_price,
                'display_text' => "{$item->name} ({$item->item_code}) - Stock: {$currentStock}",
                'max_transfer' => $currentStock,
            ];
        })->filter(function ($item) {
            return $item['current_stock'] > 0; // Only return items with stock
        })->values();
    }

    /**
     * Get stock for a specific item and branch
     */
    public function getItemStock($itemId, $branchId, $organizationId)
    {
        // Verify item belongs to organization
        $item = ItemMaster::where('id', $itemId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        // Verify branch belongs to organization
        $branch = Branch::where('id', $branchId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        return ItemTransaction::stockOnHand($itemId, $branchId);
    }

    /**
     * Get GTN status summary
     */
    public function getGTNStatusSummary($organizationId, $dateRange = null)
    {
        $query = GoodsTransferNote::where('organization_id', $organizationId);

        if ($dateRange) {
            $query->whereBetween('transfer_date', [$dateRange['start'], $dateRange['end']]);
        }

        $gtns = $query->get();

        return [
            'total' => $gtns->count(),
            'draft' => $gtns->where('origin_status', GoodsTransferNote::ORIGIN_STATUS_DRAFT)->count(),
            'confirmed' => $gtns->where('origin_status', GoodsTransferNote::ORIGIN_STATUS_CONFIRMED)->count(),
            'in_delivery' => $gtns->where('origin_status', GoodsTransferNote::ORIGIN_STATUS_IN_DELIVERY)->count(),
            'delivered' => $gtns->where('origin_status', GoodsTransferNote::ORIGIN_STATUS_DELIVERED)->count(),
            'pending_receipt' => $gtns->where('receiver_status', GoodsTransferNote::RECEIVER_STATUS_PENDING)->count(),
            'received' => $gtns->where('receiver_status', GoodsTransferNote::RECEIVER_STATUS_RECEIVED)->count(),
            'verified' => $gtns->where('receiver_status', GoodsTransferNote::RECEIVER_STATUS_VERIFIED)->count(),
            'accepted' => $gtns->where('receiver_status', GoodsTransferNote::RECEIVER_STATUS_ACCEPTED)->count(),
            'rejected' => $gtns->where('receiver_status', GoodsTransferNote::RECEIVER_STATUS_REJECTED)->count(),
            'partially_accepted' => $gtns->where('receiver_status', GoodsTransferNote::RECEIVER_STATUS_PARTIALLY_ACCEPTED)->count(),
        ];
    }

    /**
     * Validate item stock availability (enhanced version)
     */
    public function validateItemStock($itemId, $branchId, $requiredQuantity)
    {
        $currentStock = ItemTransaction::stockOnHand($itemId, $branchId);

        if ($currentStock < $requiredQuantity) {
            $item = ItemMaster::find($itemId);
            throw new Exception(
                "Insufficient stock for item '{$item->name}'. Available: {$currentStock}, Required: {$requiredQuantity}"
            );
        }

        return true;
    }

    /**
     * Get GTN audit trail
     */
    public function getGTNAuditTrail($gtnId)
    {
        $gtn = GoodsTransferNote::with([
            'createdBy', 'approvedBy', 'receivedBy', 'verifiedBy', 'rejectedBy'
        ])->findOrFail($gtnId);

        $inventoryTransactions = ItemTransaction::getGTNStockMovements($gtnId);

        return [
            'gtn' => $gtn,
            'inventory_transactions' => $inventoryTransactions,
            'timeline' => $this->buildGTNTimeline($gtn),
        ];
    }

    /**
     * Build GTN timeline for audit
     */
    protected function buildGTNTimeline($gtn)
    {
        $timeline = [];

        $timeline[] = [
            'action' => 'Created',
            'timestamp' => $gtn->created_at,
            'user' => $gtn->createdBy,
            'status' => 'Draft',
        ];

        if ($gtn->confirmed_at) {
            $timeline[] = [
                'action' => 'Confirmed',
                'timestamp' => $gtn->confirmed_at,
                'user' => $gtn->approvedBy,
                'status' => 'Confirmed',
                'note' => 'Stock deducted from sender branch',
            ];
        }

        if ($gtn->received_at) {
            $timeline[] = [
                'action' => 'Received',
                'timestamp' => $gtn->received_at,
                'user' => $gtn->receivedBy,
                'status' => 'Received',
            ];
        }

        if ($gtn->verified_at) {
            $timeline[] = [
                'action' => 'Verified',
                'timestamp' => $gtn->verified_at,
                'user' => $gtn->verifiedBy,
                'status' => 'Verified',
            ];
        }

        if ($gtn->accepted_at) {
            $timeline[] = [
                'action' => 'Accepted',
                'timestamp' => $gtn->accepted_at,
                'user' => $gtn->verifiedBy,
                'status' => ucfirst($gtn->receiver_status),
                'note' => 'Stock added to receiver branch',
            ];
        }

        if ($gtn->rejected_at) {
            $timeline[] = [
                'action' => 'Rejected',
                'timestamp' => $gtn->rejected_at,
                'user' => $gtn->rejectedBy,
                'status' => 'Rejected',
                'note' => $gtn->rejection_reason,
            ];
        }

        return collect($timeline)->sortBy('timestamp');
    }

    /**
     * Generate unique GTN number
     */
    protected function generateGTNNumber($orgId = null)
    {
        if (!$orgId) {
            $user = Auth::user();
            $orgId = $user ? $user->organization_id : null;
        }

        if (!$orgId) {
            throw new Exception('Organization ID not found for GTN number generation');
        }

        $datePrefix = now()->format('Ymd');

        $lastGtn = GoodsTransferNote::where('organization_id', $orgId)
                                   ->where('gtn_number', 'like', "GTN-{$datePrefix}-%")
                                   ->orderByDesc('gtn_id')
                                   ->first();

        $nextSeq = 1;
        if ($lastGtn && preg_match('/GTN-\d{8}-(\d{3})/', $lastGtn->gtn_number, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        }

        return 'GTN-' . $datePrefix . '-' . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Backward compatibility methods for existing functionality
     */
    public function canChangeStatus(GoodsTransferNote $gtn, string $newStatus): bool
    {
        // Only pending GTNs can have their status changed
        if ($gtn->status !== 'Pending') {
            return false;
        }

        // Valid status transitions from Pending
        $validStatuses = ['Confirmed', 'Approved', 'Verified'];
        return in_array($newStatus, $validStatuses);
    }

    public function processStockTransfer(GoodsTransferNote $gtn): void
    {
        // Use the new workflow methods
        if ($gtn->isDraft()) {
            $this->confirmGTN($gtn->gtn_id);
        }
    }
}
