# Unified GTN System Implementation

## Overview
This implementation provides a comprehensive GTN (Goods Transfer Note) system with direct inventory transactions, eliminating the need for separate GRN creation and providing real-time inventory updates.

## Key Features

### 1. Dual-Status Tracking
- **Origin Status**: `draft` → `confirmed` → `in_delivery` → `delivered`
- **Receiver Status**: `pending` → `received` → `verified` → `accepted`/`rejected`/`partially_accepted`

### 2. Direct Inventory Transactions
- Stock deducted immediately when GTN is confirmed
- Stock added to receiver when items are accepted
- Rejected items returned automatically to sender
- Full audit trail of all stock movements

### 3. Item-Level Acceptance/Rejection
- Individual items can be accepted or rejected
- Partial acceptance supported
- Rejection reasons tracked at item level
- Quality notes and inspection details

## Database Schema Changes

### Enhanced GTN Master Table (`gtn_master`)
```sql
-- Dual status fields
origin_status ENUM('draft', 'confirmed', 'in_delivery', 'delivered')
receiver_status ENUM('pending', 'received', 'verified', 'accepted', 'rejected', 'partially_accepted')

-- Workflow timestamps
confirmed_at TIMESTAMP NULL
delivered_at TIMESTAMP NULL
received_at TIMESTAMP NULL
verified_at TIMESTAMP NULL
accepted_at TIMESTAMP NULL
rejected_at TIMESTAMP NULL

-- User tracking
verified_by INT NULL
received_by INT NULL
rejected_by INT NULL

-- Rejection tracking
rejection_reason TEXT NULL
```

### Enhanced GTN Items Table (`gtn_items`)
```sql
-- Quantity tracking
quantity_accepted DECIMAL(10,2) NULL
quantity_rejected DECIMAL(10,2) DEFAULT 0

-- Item status and rejection
item_status ENUM('pending', 'accepted', 'rejected', 'partially_accepted')
item_rejection_reason TEXT NULL

-- Quality control
quality_notes JSON NULL
inspected_by INT NULL
inspected_at TIMESTAMP NULL
```

### Enhanced Item Transactions Table (`item_transactions`)
```sql
-- GTN reference
gtn_id BIGINT NULL
verified_by INT NULL

-- Foreign key constraint
FOREIGN KEY (gtn_id) REFERENCES gtn_master(gtn_id)
```

## Workflow Implementation

### 1. GTN Creation (Draft)
```php
$gtn = GoodsTransferNote::create([
    'origin_status' => 'draft',
    'receiver_status' => 'pending',
    // ... other fields
]);
```

### 2. GTN Confirmation (Stock Deduction)
```php
$gtn->confirmTransfer($userId);
// This automatically:
// - Updates origin_status to 'confirmed'
// - Creates outgoing inventory transactions (negative quantities)
// - Deducts stock from sender branch
```

### 3. GTN Receipt
```php
$gtn->receiveTransfer($userId, $notes);
// Updates receiver_status to 'received'
```

### 4. GTN Verification
```php
$gtn->verifyTransfer($userId, $notes);
// Updates receiver_status to 'verified'
```

### 5. GTN Acceptance/Rejection
```php
$acceptanceData = [
    'item_id_1' => [
        'quantity_accepted' => 100,
        'rejection_reason' => null
    ],
    'item_id_2' => [
        'quantity_accepted' => 80,
        'rejection_reason' => 'Damaged packaging'
    ]
];

$gtn->acceptTransfer($userId, $acceptanceData);
// This automatically:
// - Creates incoming transactions for accepted quantities
// - Creates rejection transactions for rejected quantities
// - Updates final status based on acceptance rates
```

## Inventory Transaction Types

### GTN-Specific Transaction Types
- `gtn_outgoing`: Stock deducted from sender (negative quantity)
- `gtn_incoming`: Stock added to receiver (positive quantity)
- `gtn_rejection`: Stock returned to sender due to rejection (positive quantity)

### Transaction Flow Example
```php
// 1. GTN Confirmation - Deduct from Sender
ItemTransaction::create([
    'branch_id' => $senderBranchId,
    'quantity' => -100, // Negative for deduction
    'transaction_type' => 'gtn_outgoing',
    'gtn_id' => $gtnId
]);

// 2. GTN Acceptance - Add to Receiver
ItemTransaction::create([
    'branch_id' => $receiverBranchId,
    'quantity' => 80, // Positive for addition
    'transaction_type' => 'gtn_incoming',
    'gtn_id' => $gtnId
]);

// 3. GTN Rejection - Return to Sender
ItemTransaction::create([
    'branch_id' => $senderBranchId,
    'quantity' => 20, // Positive for return
    'transaction_type' => 'gtn_rejection',
    'gtn_id' => $gtnId
]);
```

## API Endpoints

### Workflow Actions
- `POST /admin/inventory/gtn/{id}/confirm` - Confirm GTN and deduct stock
- `POST /admin/inventory/gtn/{id}/receive` - Mark GTN as received
- `POST /admin/inventory/gtn/{id}/verify` - Verify GTN items
- `POST /admin/inventory/gtn/{id}/accept` - Process item acceptance/rejection
- `POST /admin/inventory/gtn/{id}/reject` - Reject entire GTN
- `GET /admin/inventory/gtn/{id}/audit-trail` - Get complete audit trail

### Data Endpoints
- `GET /admin/inventory/gtn/items-with-stock` - Get items with current stock
- `GET /admin/inventory/gtn/search-items` - Search items with stock
- `GET /admin/inventory/gtn/item-stock` - Get stock for specific item/branch

## Service Layer

### GTNService Methods
```php
// Core workflow methods
confirmGTN($gtnId, $userId = null)
receiveGTN($gtnId, $userId = null, $notes = null)
verifyGTN($gtnId, $userId = null, $notes = null)
processGTNAcceptance($gtnId, array $acceptanceData, $userId = null)
rejectGTN($gtnId, $rejectionReason, $userId = null)

// Data retrieval methods
getItemsWithStock($branchId, $organizationId)
searchItemsWithStock($branchId, $organizationId, $search = '')
getGTNStatusSummary($organizationId, $dateRange = null)
getGTNAuditTrail($gtnId)
```

## Model Relationships

### GoodsTransferNote Model
```php
// Workflow user relationships
public function createdBy() // Employee who created
public function approvedBy() // Employee who approved/confirmed
public function receivedBy() // Employee who received
public function verifiedBy() // Employee who verified
public function rejectedBy() // Employee who rejected

// Inventory relationships
public function inventoryTransactions() // All related transactions
public function items() // GTN items

// Workflow state checks
public function isDraft()
public function isConfirmed()
public function isReceived()
public function isVerified()
public function isAccepted()
public function isRejected()
```

### GoodsTransferItem Model
```php
// Status management
public function accept($quantity = null, $userId = null, $notes = null)
public function reject($reason, $userId = null)
public function addQualityNote($note, $userId = null)

// Calculated properties
public function getAcceptanceRateAttribute()
public function getRejectionRateAttribute()
public function getAcceptedValueAttribute()
public function getRejectedValueAttribute()
```

## Benefits of This Implementation

### 1. Eliminates GRN Duplication
- No need to create separate GRN records
- Single GTN handles entire transfer lifecycle
- Reduces data redundancy and complexity

### 2. Real-Time Inventory Updates
- Stock changes immediately upon GTN confirmation
- Accurate stock levels at all times
- Automated reconciliation for rejections

### 3. Comprehensive Audit Trail
- Every stock movement tracked with GTN reference
- Complete timeline of GTN workflow
- User accountability at each step

### 4. Flexible Acceptance Process
- Item-level acceptance/rejection
- Partial acceptance support
- Quality control integration

### 5. Better Error Handling
- Stock validation before confirmation
- Automatic rollback for rejections
- Exception handling with proper error messages

## Usage Examples

### Creating a GTN
```php
$gtnData = [
    'from_branch_id' => 1,
    'to_branch_id' => 2,
    'transfer_date' => '2025-06-16',
    'items' => [
        [
            'item_id' => 1,
            'transfer_quantity' => 100,
            'transfer_price' => 10.50,
            'notes' => 'Handle with care'
        ]
    ],
    'notes' => 'Urgent transfer for branch 2'
];

$gtn = $gtnService->createGTN($gtnData);
```

### Processing GTN Workflow
```php
// 1. Confirm GTN (deducts stock)
$gtn = $gtnService->confirmGTN($gtnId);

// 2. Receive GTN
$gtn = $gtnService->receiveGTN($gtnId, $userId, 'Received in good condition');

// 3. Verify GTN
$gtn = $gtnService->verifyGTN($gtnId, $userId, 'Quality check completed');

// 4. Accept/Reject items
$acceptanceData = [
    1 => ['quantity_accepted' => 95, 'rejection_reason' => null],
    2 => ['quantity_accepted' => 0, 'rejection_reason' => 'Damaged goods']
];
$gtn = $gtnService->processGTNAcceptance($gtnId, $acceptanceData, $userId);
```

### Checking Stock Movements
```php
// Get all inventory transactions for a GTN
$transactions = ItemTransaction::getGTNStockMovements($gtnId);

// Get current stock after GTN processing
$currentStock = ItemTransaction::stockOnHand($itemId, $branchId);

// Get GTN audit trail
$auditData = $gtnService->getGTNAuditTrail($gtnId);
```

## Best Practices

### 1. Stock Validation
- Always validate stock availability before GTN confirmation
- Use database transactions for atomic operations
- Implement proper error handling and rollback mechanisms

### 2. User Permissions
- Implement role-based access control for GTN actions
- Track user actions for audit purposes
- Validate user organization access

### 3. Data Integrity
- Use foreign key constraints
- Implement proper validation rules
- Use enum types for status fields

### 4. Performance Optimization
- Use eager loading for related models
- Implement proper indexing on status and date fields
- Consider pagination for large datasets

### 5. Testing
- Write comprehensive unit tests for workflow methods
- Test edge cases like partial acceptance and rejections
- Validate inventory calculations under various scenarios

This unified GTN system provides a robust, efficient, and user-friendly solution for inter-branch inventory transfers while maintaining accurate stock levels and comprehensive audit trails.
