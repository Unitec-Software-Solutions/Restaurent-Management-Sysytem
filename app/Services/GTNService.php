<?php

namespace App\Services;

use App\Models\GoodsTransferNote;
use App\Models\GoodsTransferItem;
use App\Models\ItemTransaction;
use App\Models\GrnMaster;
use App\Models\GrnItem;
use App\Models\ItemMaster;
use App\Models\Employee;
use App\Models\Branch;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GTNService
{
    protected $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Process stock transfer when GTN status changes to confirmed/approved/verified
     */
    public function processStockTransfer(GoodsTransferNote $gtn): void
    {
        DB::transaction(function () use ($gtn) {
            // Create stock reduction transactions for origin branch
            $this->createOutgoingTransactions($gtn);

            // Create GRN for receiving branch
            $this->createReceivingGRN($gtn);

            Log::info('Stock transfer processed', [
                'gtn_id' => $gtn->gtn_id,
                'from_branch' => $gtn->from_branch_id,
                'to_branch' => $gtn->to_branch_id
            ]);
        });
    }

    /**
     * Create outgoing stock transactions for origin branch
     */
    private function createOutgoingTransactions(GoodsTransferNote $gtn): void
    {
        foreach ($gtn->items as $item) {
            ItemTransaction::create([
                'organization_id' => $gtn->organization_id,
                'branch_id' => $gtn->from_branch_id,
                'inventory_item_id' => $item->item_id,
                'transaction_type' => 'gtn_stock_out',
                'quantity' => -$item->transfer_quantity, // Negative for outgoing
                'cost_price' => 0, // Internal transfer
                'unit_price' => 0, // Internal transfer
                'source_id' => (string) $gtn->gtn_id,
                'source_type' => GoodsTransferNote::class,
                'created_by_user_id' => Auth::id(),
                'notes' => "Stock transferred out via GTN #{$gtn->gtn_number} to {$gtn->toBranch->name}",
                'is_active' => true,
            ]);
        }
    }

    /**
     * Create a GRN for the receiving branch
     */
    private function createReceivingGRN(GoodsTransferNote $gtn): GrnMaster
    {
        $grn = GrnMaster::create([
            'grn_number' => $this->generateInternalGRNNumber($gtn->organization_id),
            'branch_id' => $gtn->to_branch_id,
            'organization_id' => $gtn->organization_id,
            'supplier_id' => null, // Internal transfer
            'received_by_user_id' => Auth::id(),
            'received_date' => now()->toDateString(),
            'delivery_note_number' => $gtn->gtn_number,
            'invoice_number' => null,
            'notes' => "Internal transfer from GTN #{$gtn->gtn_number} from {$gtn->fromBranch->name}",
            'status' => 'Pending', // Receiving branch needs to confirm
            'is_active' => true,
            'created_by' => Auth::id(),
            'total_amount' => 0 // Internal transfer
        ]);

        // Create GRN items
        foreach ($gtn->items as $gtnItem) {
            GrnItem::create([
                'grn_id' => $grn->grn_id,
                'item_id' => $gtnItem->item_id,
                'item_code' => $gtnItem->item_code,
                'item_name' => $gtnItem->item_name,
                'batch_no' => $gtnItem->batch_no,
                'ordered_quantity' => $gtnItem->transfer_quantity,
                'received_quantity' => $gtnItem->transfer_quantity,
                'accepted_quantity' => $gtnItem->transfer_quantity, // Set accepted_quantity to match transfer_quantity
                'rejected_quantity' => 0,
                'buying_price' => 0, // Internal transfer
                'line_total' => 0, // Internal transfer
                'manufacturing_date' => null,
                'expiry_date' => $gtnItem->expiry_date,
                'rejection_reason' => null,
                'discount_received' => 0,
                'free_received_quantity' => 0
            ]);
        }

        Log::info('Created receiving GRN for GTN', [
            'gtn_id' => $gtn->gtn_id,
            'grn_id' => $grn->grn_id,
            'to_branch_id' => $gtn->to_branch_id
        ]);

        return $grn;
    }

    /**
     * Generate GRN number for internal transfers
     */
    private function generateInternalGRNNumber(int $organizationId): string
    {
        $latest = GrnMaster::where('organization_id', $organizationId)
            ->where('grn_number', 'LIKE', 'GRN-INT-%')
            ->latest('grn_id')
            ->first();

        $nextId = $latest ? (int)substr($latest->grn_number, -4) + 1 : 1;
        return 'GRN-INT-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get items with available stock for a specific branch
     */
    public function getItemsWithStock(int $branchId, int $organizationId): array
    {
        $items = ItemMaster::where('organization_id', $organizationId)
            ->active()
            ->get()
            ->map(function ($item) use ($branchId) {
                $stock = ItemTransaction::stockOnHand($item->id, $branchId);
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit_of_measurement' => $item->unit_of_measurement,
                    'stock_on_hand' => $stock,
                    'max_transfer' => $stock * 1.1 // Include 10% margin
                ];
            })
            ->filter(function ($item) {
                return $item['stock_on_hand'] > 0; // Only show items with stock
            })
            ->values()
            ->toArray();

        return $items;
    }

    /**
     * Validate GTN status change
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

    /**
     * Check if items have sufficient stock for transfer
     */
    public function validateItemStock(array $items, int $fromBranchId): array
    {
        $errors = [];

        foreach ($items as $index => $item) {
            if (!isset($item['item_id']) || !isset($item['transfer_quantity'])) {
                continue;
            }

            $availableStock = ItemTransaction::stockOnHand($item['item_id'], $fromBranchId);
            $requestedQuantity = (float) $item['transfer_quantity'];
            $maxAllowed = $availableStock * 1; // ONLY 100% of available stock change if margin of error is needed

            // if ($requestedQuantity > $maxAllowed) {
            //     $errors["items.{$index}.transfer_quantity"] =
            //         "Transfer quantity ({$requestedQuantity}) exceeds available stock ({$availableStock}) plus 10% margin ({$maxAllowed}).";
            // }

            if ($requestedQuantity > $maxAllowed) {
                $errors["items.{$index}.transfer_quantity"] =
                    "Transfer quantity ({$requestedQuantity}) exceeds available stock ({$availableStock}) .";
            }
        }

        return $errors;
    }

    /**
     * Create a new GTN
     */
    public function createGTN(array $data): GoodsTransferNote
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            if (!$user || !$user->organization_id) {
                throw new \Exception('Unauthorized access - organization not set');
            }

            $organizationId = $user->organization_id;

            // Get or create employee record
            $employee = $this->employeeRepository->findOrCreateForUser($user, $data['from_branch_id']);

            $gtn = GoodsTransferNote::create([
                'gtn_number' => $data['gtn_number'],
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'created_by' => $employee->id,
                'organization_id' => $organizationId,
                'transfer_date' => $data['transfer_date'],
                'status' => 'Pending',
                'notes' => $data['notes'] ?? null,
            ]);

            $this->createGTNItems($gtn, $data['items']);

            Log::info('GTN created', ['gtn_id' => $gtn->gtn_id, 'user_id' => $user->id]);

            return $gtn;
        });
    }

    /**
     * Update an existing GTN
     */
    public function updateGTN(int $gtnId, array $data): GoodsTransferNote
    {
        return DB::transaction(function () use ($gtnId, $data) {
            $admin = Auth::user();

            if (!$admin || !$admin->organization_id) {
                throw new \Exception('Unauthorized access - organization not set');
            }

            $gtn = GoodsTransferNote::where('organization_id', $admin->organization_id)
                ->findOrFail($gtnId);

            if ($gtn->status !== 'Pending') {
                throw new \Exception('Only pending GTNs can be updated.');
            }

            $gtn->update([
                'gtn_number' => $data['gtn_number'],
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'transfer_date' => $data['transfer_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $gtn->items()->delete();
            $this->createGTNItems($gtn, $data['items']);

            Log::info('GTN updated', ['gtn_id' => $gtn->gtn_id, 'user_id' => $admin->id]);

            return $gtn;
        });
    }

    /**
     * Create GTN items
     */
    private function createGTNItems(GoodsTransferNote $gtn, array $items): void
    {
        foreach ($items as $item) {
            $itemModel = ItemMaster::findOrFail($item['item_id']);

            GoodsTransferItem::create([
                'gtn_id' => $gtn->gtn_id,
                'item_id' => $itemModel->id,
                'item_code' => $itemModel->item_code,
                'item_name' => $itemModel->name,
                'batch_no' => $item['batch_no'] ?? null,
                'expiry_date' => $item['expiry_date'] ?? null,
                'transfer_quantity' => $item['transfer_quantity'],
                'transfer_price' => 0,
                'line_total' => 0,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    /**
     * Search items with stock for autocomplete
     */
    public function searchItemsWithStock(int $branchId, int $organizationId, string $search = ''): array
    {
        $query = ItemMaster::where('organization_id', $organizationId)
            ->where('is_active', true);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('item_code', 'ILIKE', "%{$search}%");
            });
        }

        $items = $query->limit(20)
            ->get()
            ->map(function ($item) use ($branchId) {
                $stock = ItemTransaction::stockOnHand($item->id, $branchId);
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit_of_measurement' => $item->unit_of_measurement,
                    'stock_on_hand' => $stock,
                    'max_transfer' => $stock * 1.1,
                    'display_text' => "{$item->name} ({$item->item_code}) - Stock: {$stock}"
                ];
            })
            ->filter(function ($item) {
                return $item['stock_on_hand'] > 0;
            })
            ->values()
            ->toArray();

        return $items;
    }

    /**
     * Get stock for a specific item and branch
     */
    public function getItemStock(int $itemId, int $branchId, int $organizationId): float
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
}
