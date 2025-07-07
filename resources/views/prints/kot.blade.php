<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Order Ticket - {{ $order->order_number ?? 'Order #' . $order->id }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .kot-container {
            max-width: 300px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 15px;
        }
        
        .kot-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .kot-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        
        .kot-subtitle {
            font-size: 12px;
            margin: 0;
        }
        
        .order-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        
        .order-info-item {
            text-align: center;
            flex: 1;
        }
        
        .order-info-label {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }
        
        .order-info-value {
            font-size: 14px;
            font-weight: bold;
        }
        
        .items-section {
            margin-bottom: 15px;
        }
        
        .items-header {
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .item {
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .item-name {
            flex: 1;
        }
        
        .item-qty {
            background: #000;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            min-width: 20px;
            text-align: center;
        }
        
        .item-instructions {
            font-style: italic;
            color: #666;
            font-size: 10px;
            margin-top: 3px;
        }
        
        .special-instructions {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        
        .special-instructions-label {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .special-instructions-text {
            font-size: 10px;
        }
        
        .kot-footer {
            text-align: center;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .timing-info {
            margin-top: 10px;
            font-size: 10px;
        }
        
        .priority-badge {
            background: #ff4444;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .kot-container {
                border: 2px solid #000;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="kot-container">
        <div class="kot-header">
            <div class="kot-title">KITCHEN ORDER TICKET</div>
            <div class="kot-subtitle">{{ config('app.name', 'Restaurant') }}</div>
        </div>
        
        <div class="order-info">
            <div class="order-info-item">
                <div class="order-info-label">ORDER #</div>
                <div class="order-info-value">{{ $order->order_number ?? $order->id }}</div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">{{ $order->order_type ? strtoupper($order->order_type->getLabel()) : 'DINE IN' }}</div>
                <div class="order-info-value">
                    @if($order->table)
                        TABLE {{ $order->table->table_number }}
                    @elseif($order->order_type && $order->order_type->isTakeaway())
                        TAKEAWAY
                    @else
                        COUNTER
                    @endif
                </div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">TIME</div>
                <div class="order-info-value">{{ $order->created_at->format('H:i') }}</div>
            </div>
        </div>
        
        @if($order->special_instructions)
            <div class="special-instructions">
                <div class="special-instructions-label">SPECIAL INSTRUCTIONS:</div>
                <div class="special-instructions-text">{{ $order->special_instructions }}</div>
            </div>
        @endif
        
        <div class="items-section">
            <div class="items-header">ORDER ITEMS</div>
            
            @forelse($order->orderItems ?? [] as $item)
                <div class="item">
                    <div class="item-header">
                        <div class="item-name">
                            {{ $item->menuItem->name ?? 'Unknown Item' }}
                            @if($item->is_priority ?? false)
                                <span class="priority-badge">RUSH</span>
                            @endif
                        </div>
                        <div class="item-qty">{{ $item->quantity }}x</div>
                    </div>
                    
                    @if($item->special_instructions)
                        <div class="item-instructions">
                            Note: {{ $item->special_instructions }}
                        </div>
                    @endif
                    
                    @if($item->menuItem && $item->menuItem->preparation_time)
                        <div class="item-instructions">
                            Prep Time: {{ $item->menuItem->preparation_time }} min
                        </div>
                    @endif
                </div>
            @empty
                <div class="item">
                    <div class="item-header">
                        <div class="item-name">No items in this order</div>
                    </div>
                </div>
            @endforelse
        </div>
        
        <div class="kot-footer">
            <div class="timing-info">
                <strong>Order Time:</strong> {{ $order->created_at->format('d/m/Y H:i:s') }}<br>
                <strong>Printed:</strong> {{ now()->format('d/m/Y H:i:s') }}<br>
                @if($order->requested_time)
                    <strong>Requested By:</strong> {{ $order->requested_time->format('H:i') }}<br>
                @endif
            </div>
            
            @if($order->customer_name)
                <div style="margin-top: 10px;">
                    <strong>Customer:</strong> {{ $order->customer_name }}
                    @if($order->customer_phone)
                        <br><strong>Phone:</strong> {{ $order->customer_phone }}
                    @endif
                </div>
            @endif
            
            @if($order->staff)
                <div style="margin-top: 5px;">
                    <strong>Server:</strong> {{ $order->staff->name }}
                </div>
            @endif
        </div>
    </div>
    
    <script>
        // Auto-print functionality for KOT
        document.addEventListener('DOMContentLoaded', function() {
            // Check if this is a print request
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === '1') {
                window.print();
            }
        });
        
        // Print function for manual printing
        function printKOT() {
            window.print();
        }
        
        // Auto-refresh for real-time updates (optional)
        @if(request()->get('refresh'))
            setTimeout(function() {
                location.reload();
            }, {{ request()->get('refresh') * 1000 }});
        @endif
    </script>
</body>
</html>
