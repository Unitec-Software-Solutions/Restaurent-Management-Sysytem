<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified GTN System - {{ $gtn->gtn_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .workflow-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .step {
            text-align: center;
            flex: 1;
            position: relative;
        }

        .step::after {
            content: '';
            position: absolute;
            top: 25px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }

        .step:last-child::after {
            display: none;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            position: relative;
            z-index: 2;
        }

        .step.active .step-icon {
            background: #0d6efd;
            color: white;
        }

        .step.completed .step-icon {
            background: #198754;
            color: white;
        }

        .step.completed::after {
            background: #198754;
        }

        .status-badge {
            font-size: 0.9em;
            padding: 5px 10px;
        }

        .item-status {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-accepted {
            background: #d1edff;
            color: #0c63e4;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-partially-accepted {
            background: #e2e3e5;
            color: #383d41;
        }

        .audit-timeline {
            border-left: 3px solid #dee2e6;
            padding-left: 20px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -27px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0d6efd;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-exchange-alt text-primary"></i>
                                GTN: {{ $gtn->gtn_number }}
                            </h4>
                            <small class="text-muted">
                                {{ $gtn->fromBranch->name }} → {{ $gtn->toBranch->name }}
                            </small>
                        </div>
                        <div>
                            <span class="badge bg-info status-badge">
                                Origin: {{ ucfirst(str_replace('_', ' ', $gtn->origin_status)) }}
                            </span>
                            <span class="badge bg-warning status-badge">
                                Receiver: {{ ucfirst(str_replace('_', ' ', $gtn->receiver_status)) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Workflow Progress -->
                        <div class="workflow-steps">
                            <div class="step {{ $gtn->origin_status == 'draft' ? 'active' : 'completed' }}">
                                <div class="step-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="step-title">Draft</div>
                                <small class="text-muted">Created</small>
                            </div>
                            <div
                                class="step {{ $gtn->origin_status == 'confirmed' ? 'active' : ($gtn->confirmed_at ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="step-title">Confirmed</div>
                                <small class="text-muted">Stock Deducted</small>
                            </div>
                            <div
                                class="step {{ $gtn->receiver_status == 'received' ? 'active' : ($gtn->received_at ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <div class="step-title">Received</div>
                                <small class="text-muted">Goods Arrived</small>
                            </div>
                            <div
                                class="step {{ $gtn->receiver_status == 'verified' ? 'active' : ($gtn->verified_at ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div class="step-title">Verified</div>
                                <small class="text-muted">Quality Check</small>
                            </div>
                            <div
                                class="step {{ in_array($gtn->receiver_status, ['accepted', 'rejected', 'partially_accepted']) ? 'completed' : '' }}">
                                <div class="step-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div class="step-title">Completed</div>
                                <small class="text-muted">Final Status</small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="btn-group" role="group">
                                    @if ($gtn->isDraft())
                                        <button type="button" class="btn btn-success" onclick="confirmGTN()">
                                            <i class="fas fa-check"></i> Confirm GTN
                                        </button>
                                    @endif

                                    @if ($gtn->isPending() && $gtn->isConfirmed())
                                        <button type="button" class="btn btn-primary" onclick="receiveGTN()">
                                            <i class="fas fa-truck"></i> Mark as Received
                                        </button>
                                    @endif

                                    @if ($gtn->isReceived())
                                        <button type="button" class="btn btn-info" onclick="verifyGTN()">
                                            <i class="fas fa-search"></i> Verify Items
                                        </button>
                                    @endif

                                    @if ($gtn->isVerified())
                                        <button type="button" class="btn btn-success" onclick="showAcceptanceModal()">
                                            <i class="fas fa-clipboard-check"></i> Process Acceptance
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="showRejectionModal()">
                                            <i class="fas fa-times"></i> Reject GTN
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-outline-secondary" onclick="viewAuditTrail()">
                                        <i class="fas fa-history"></i> Audit Trail
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- GTN Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Transfer Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>GTN Number:</strong></td>
                                                <td>{{ $gtn->gtn_number }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>From Branch:</strong></td>
                                                <td>{{ $gtn->fromBranch->name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>To Branch:</strong></td>
                                                <td>{{ $gtn->toBranch->name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Transfer Date:</strong></td>
                                                <td>{{ $gtn->transfer_date->format('d M Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Created By:</strong></td>
                                                <td>{{ $gtn->createdBy->name ?? 'Unknown' }}</td>
                                            </tr>
                                            @if ($gtn->notes)
                                                <tr>
                                                    <td><strong>Notes:</strong></td>
                                                    <td>{{ $gtn->notes }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Status Timeline</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="audit-timeline">
                                            <div class="timeline-item">
                                                <strong>Created</strong>
                                                <br><small
                                                    class="text-muted">{{ $gtn->created_at->format('d M Y H:i') }}</small>
                                            </div>
                                            @if ($gtn->confirmed_at)
                                                <div class="timeline-item">
                                                    <strong>Confirmed</strong>
                                                    <br><small
                                                        class="text-muted">{{ $gtn->confirmed_at->format('d M Y H:i') }}</small>
                                                    <br><small class="text-success">Stock deducted from sender</small>
                                                </div>
                                            @endif
                                            @if ($gtn->received_at)
                                                <div class="timeline-item">
                                                    <strong>Received</strong>
                                                    <br><small
                                                        class="text-muted">{{ $gtn->received_at->format('d M Y H:i') }}</small>
                                                </div>
                                            @endif
                                            @if ($gtn->verified_at)
                                                <div class="timeline-item">
                                                    <strong>Verified</strong>
                                                    <br><small
                                                        class="text-muted">{{ $gtn->verified_at->format('d M Y H:i') }}</small>
                                                </div>
                                            @endif
                                            @if ($gtn->accepted_at)
                                                <div class="timeline-item">
                                                    <strong>{{ ucfirst($gtn->receiver_status) }}</strong>
                                                    <br><small
                                                        class="text-muted">{{ $gtn->accepted_at->format('d M Y H:i') }}</small>
                                                    <br><small class="text-info">Stock updated accordingly</small>
                                                </div>
                                            @endif
                                            @if ($gtn->rejected_at)
                                                <div class="timeline-item">
                                                    <strong>Rejected</strong>
                                                    <br><small
                                                        class="text-muted">{{ $gtn->rejected_at->format('d M Y H:i') }}</small>
                                                    <br><small class="text-danger">Stock returned to sender</small>
                                                    @if ($gtn->rejection_reason)
                                                        <br><small class="text-muted">Reason:
                                                            {{ $gtn->rejection_reason }}</small>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Transfer Items</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Item Code</th>
                                                        <th>Item Name</th>
                                                        <th>Transfer Qty</th>
                                                        <th>Accepted Qty</th>
                                                        <th>Rejected Qty</th>
                                                        <th>Status</th>
                                                        <th>Acceptance Rate</th>
                                                        @if (in_array($gtn->receiver_status, ['verified', 'accepted', 'rejected', 'partially_accepted']))
                                                            <th>Notes</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($gtn->items as $item)
                                                        <tr>
                                                            <td><code>{{ $item->item_code }}</code></td>
                                                            <td>{{ $item->item_name }}</td>
                                                            <td>{{ number_format($item->transfer_quantity, 2) }}</td>
                                                            <td>
                                                                @if ($item->quantity_accepted !== null)
                                                                    <span
                                                                        class="text-success">{{ number_format($item->quantity_accepted, 2) }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($item->quantity_rejected !== null && $item->quantity_rejected > 0)
                                                                    <span
                                                                        class="text-danger">{{ number_format($item->quantity_rejected, 2) }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="item-status status-{{ $item->item_status }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $item->item_status)) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if ($item->quantity_accepted !== null)
                                                                    <div class="progress" style="height: 20px;">
                                                                        <div class="progress-bar bg-success"
                                                                            style="width: {{ $item->acceptance_rate }}%">
                                                                            {{ number_format($item->acceptance_rate, 1) }}%
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    <span class="text-muted">Pending</span>
                                                                @endif
                                                            </td>
                                                            @if (in_array($gtn->receiver_status, ['verified', 'accepted', 'rejected', 'partially_accepted']))
                                                                <td>
                                                                    @if ($item->item_rejection_reason)
                                                                        <small
                                                                            class="text-danger">{{ $item->item_rejection_reason }}</small>
                                                                    @elseif($item->quality_notes)
                                                                        <small class="text-info">Quality notes
                                                                            available</small>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-info">
                                                        <th colspan="2">Total</th>
                                                        <th>{{ number_format($gtn->items->sum('transfer_quantity'), 2) }}
                                                        </th>
                                                        <th>{{ number_format($gtn->items->sum('quantity_accepted'), 2) }}
                                                        </th>
                                                        <th>{{ number_format($gtn->items->sum('quantity_rejected'), 2) }}
                                                        </th>
                                                        <th>{{ number_format($gtn->getAcceptanceRate(), 1) }}% Accepted
                                                        </th>
                                                        <th></th>
                                                        @if (in_array($gtn->receiver_status, ['verified', 'accepted', 'rejected', 'partially_accepted']))
                                                            <th></th>
                                                        @endif
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card text-white bg-primary">
                                    <div class="card-body text-center">
                                        <h5>{{ number_format($gtn->getTotalTransferValue(), 2) }}</h5>
                                        <small>Total Transfer Value</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body text-center">
                                        <h5>{{ number_format($gtn->getTotalAcceptedValue(), 2) }}</h5>
                                        <small>Accepted Value</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-danger">
                                    <div class="card-body text-center">
                                        <h5>{{ number_format($gtn->getTotalRejectedValue(), 2) }}</h5>
                                        <small>Rejected Value</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-info">
                                    <div class="card-body text-center">
                                        <h5>{{ number_format($gtn->getAcceptanceRate(), 1) }}%</h5>
                                        <small>Acceptance Rate</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Acceptance Modal -->
    <div class="modal fade" id="acceptanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Item Acceptance/Rejection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="acceptanceForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Transfer Qty</th>
                                        <th>Accept Qty</th>
                                        <th>Rejection Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gtn->items as $item)
                                        <tr>
                                            <td>{{ $item->item_name }}</td>
                                            <td>{{ $item->transfer_quantity }}</td>
                                            <td>
                                                <input type="number" class="form-control"
                                                    name="acceptance_data[{{ $item->gtn_item_id }}][quantity_accepted]"
                                                    value="{{ $item->transfer_quantity }}" min="0"
                                                    max="{{ $item->transfer_quantity }}" step="0.01">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control"
                                                    name="acceptance_data[{{ $item->gtn_item_id }}][rejection_reason]"
                                                    placeholder="Reason if rejecting...">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="processAcceptance()">Process
                        Acceptance</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject GTN</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectionForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason</label>
                            <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="rejectGTN()">Reject GTN</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmGTN() {
            if (confirm('Are you sure you want to confirm this GTN? This will deduct stock from the sender branch.')) {
                fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/confirm`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.error);
                        }
                    });
            }
        }

        function receiveGTN() {
            const notes = prompt('Any notes about the receipt?');
            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/receive`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function verifyGTN() {
            const notes = prompt('Any verification notes?');
            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function showAcceptanceModal() {
            new bootstrap.Modal(document.getElementById('acceptanceModal')).show();
        }

        function showRejectionModal() {
            new bootstrap.Modal(document.getElementById('rejectionModal')).show();
        }

        function processAcceptance() {
            const formData = new FormData(document.getElementById('acceptanceForm'));
            const acceptanceData = {};

            for (let [key, value] of formData.entries()) {
                const match = key.match(/acceptance_data\[(\d+)\]\[(.+)\]/);
                if (match) {
                    const itemId = match[1];
                    const field = match[2];
                    if (!acceptanceData[itemId]) acceptanceData[itemId] = {};
                    acceptanceData[itemId][field] = value;
                }
            }

            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/accept`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        acceptance_data: acceptanceData
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function rejectGTN() {
            const formData = new FormData(document.getElementById('rejectionForm'));
            const rejectionReason = formData.get('rejection_reason');

            if (!rejectionReason) {
                alert('Please provide a rejection reason.');
                return;
            }

            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        rejection_reason: rejectionReason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function viewAuditTrail() {
            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/audit-trail`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show audit trail in modal or alert
                        alert('Audit trail loaded successfully.');
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }
    </script>
</body>

</html>
