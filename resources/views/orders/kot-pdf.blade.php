<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOT - Order #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            background: white;
            width: 80mm;
            margin: 0;
            padding: 2mm;
        }

        .kot-container {
            width: 100%;
        }

        .kot-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        .kot-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .branch-name {
            font-size: 12px;
            font-weight: bold;
        }

        .order-info {
            margin-bottom: 5px;
            font-size: 9px;
        }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        .order-info-left {
            font-weight: bold;
        }

        .order-info-right {
            text-align: right;
        }

        .separator {
            border-bottom: 1px dashed #000;
            margin: 3px 0;
        }

        .items-section {
            margin-bottom: 5px;
        }

        .item-row {
            margin-bottom: 2px;
            font-size: 9px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 1px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 8px;
        }

        .item-qty {
            font-weight: bold;
        }

        .item-type {
            font-style: italic;
            color: #666;
        }

        .instructions {
            margin: 3px 0;
            padding: 2px;
            border: 1px solid #000;
            font-size: 8px;
        }

        .instructions-title {
            font-weight: bold;
            margin-bottom: 1px;
        }

        .priority-urgent {
            background: #000;
            color: white;
            padding: 1px 2px;
            font-weight: bold;
            text-align: center;
            margin: 2px 0;
        }

        .status-info {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 8px;
        }

        .kot-footer {
            border-top: 2px solid #000;
            padding-top: 3px;
            margin-top: 5px;
            text-align: center;
            font-size: 8px;
        }

        .footer-row {
            margin-bottom: 1px;
        }

        .end-marker {
            font-weight: bold;
            margin-top: 3px;
        }

        /* PDF specific adjustments */
        @page {
            size: 80mm auto;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="kot-container">
        <!-- Header -->
        <div class="kot-header">
            <div class="kot-title">KITCHEN ORDER TICKET</div>
            @if($order->branch)
                <div class="branch-name">{{ $order->branch->name }}</div>
            @endif
        </div>

        <!-- Order Information -->
        <div class="order-info">
            <div class="order-info-row">
                <div class="order-info-left">Order #:</div>
                <div class="order-info-right">{{ $order->order_number ?? $order->id }}</div>
            </div>
            <div class="order-info-row">
                <div class="order-info-left">Date:</div>
                <div class="order-info-right">{{ $order->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="order-info-row">
                <div class="order-info-left">Customer:</div>
                <div class="order-info-right">{{ $order->customer_name }}</div>
            </div>
            @if($order->customer_phone)
            <div class="order-info-row">
                <div class="order-info-left">Phone:</div>
                <div class="order-info-right">{{ $order->customer_phone }}</div>
            </div>
            @endif
            @if($order->reservation)
            <div class="order-info-row">
                <div class="order-info-left">Table:</div>
                <div class="order-info-right">{{ $order->reservation->table_number ?? 'N/A' }}</div>
            </div>
            @endif
            <div class="order-info-row">
                <div class="order-info-left">Type:</div>
                <div class="order-info-right">
                    @if($order->order_type)
                        {{ $order->getOrderTypeLabel() }}
                    @else
                        Standard
                    @endif
                </div>
            </div>
        </div>

        <div class="separator"></div>

        <!-- Items Section -->
        <div class="items-section">
            @forelse($order->orderItems ?? [] as $item)
            <div class="item-row">
                <div class="item-name">{{ $item->item_name }}</div>
                <div class="item-details">
                    <div class="item-qty">Qty: {{ $item->quantity }}</div>
                    @if($item->menuItem && $item->menuItem->type)
                        <div class="item-type">
                            {{ $item->menuItem->type === 'kot' ? 'Kitchen' : 'Bar/Cold' }}
                        </div>
                    @endif
                </div>
                @if($item->notes)
                <div style="font-size: 8px; font-style: italic; margin-top: 1px;">
                    Note: {{ $item->notes }}
                </div>
                @endif
            </div>
            @empty
            <div class="item-row">
                <div class="item-name">No items found</div>
            </div>
            @endforelse
        </div>

        <div class="separator"></div>

        <!-- Special Instructions -->
        @if($order->special_instructions || $order->notes)
        <div class="instructions">
            <div class="instructions-title">SPECIAL INSTRUCTIONS:</div>
            <div>{{ $order->special_instructions ?? $order->notes }}</div>
        </div>
        @endif

        <!-- Priority Alert -->
        @php
            $isPriority = $order->order_type && (
                $order->order_type->isOnDemand() || 
                ($order->order_type->value && str_contains($order->order_type->value, 'urgent'))
            );
        @endphp
        @if($isPriority)
        <div class="priority-urgent">
            *** URGENT PRIORITY ***
        </div>
        @endif

        <!-- Status Information -->
        <div class="status-info">
            <div>Status: <strong>{{ strtoupper($order->status) }}</strong></div>
            <div>Items: {{ $order->orderItems->count() }}</div>
        </div>

        <!-- Footer -->
        <div class="kot-footer">
            <div class="footer-row">KOT Generated: {{ now()->format('d/m/Y H:i:s') }}</div>
            <div class="footer-row">Please prepare items in order</div>
            @if($order->steward)
            <div class="footer-row">Steward: {{ $order->steward->first_name }}</div>
            @endif
            <div class="end-marker">======= END OF KOT =======</div>
        </div>
    </div>
</body>
</html>
