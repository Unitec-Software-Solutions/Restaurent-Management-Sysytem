<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOT #{{ $kot->id ?? $order->id }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 10px;
            background: white;
        }
        
        .kot-container {
            max-width: 300px;
            margin: 0 auto;
            border: 2px dashed #000;
            padding: 10px;
            background: white;
        }
        
        .kot-header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .kot-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        
        .organization-name {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .branch-name {
            font-size: 12px;
            margin: 2px 0;
        }
        
        .kot-info {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 11px;
        }
        
        .kot-items {
            margin: 15px 0;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            padding: 2px 0;
        }
        
        .item-name {
            flex: 1;
        }
        
        .item-qty {
            width: 30px;
            text-align: right;
        }
        
        .special-instructions {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }
        
        .kot-footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 10px;
        }
        
        .print-controls {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .order-type {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            display: inline-block;
            margin: 5px 0;
        }
        
        .priority {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="kot-container">
        <!-- Header -->
        <div class="kot-header">
            <h1 class="kot-title">KITCHEN ORDER TICKET</h1>
            <div class="organization-name">{{ $order->organization->name ?? 'Restaurant' }}</div>
            <div class="branch-name">{{ $order->branch->name ?? 'Main Branch' }}</div>
        </div>
        
        <!-- Order Information -->
        <div class="kot-info">
            <div>
                <strong>KOT #:</strong> {{ $kot->id ?? $order->id }}<br>
                <strong>Order #:</strong> {{ $order->id }}<br>
                <strong>Type:</strong> <span class="order-type">{{ strtoupper($order->getOrderTypeLabel()) }}</span>
                @if($order->priority ?? false)
                    <span class="priority">PRIORITY</span>
                @endif
            </div>
            <div style="text-align: right;">
                <strong>Date:</strong> {{ ($kot->created_at ?? $order->created_at)->format('d/m/Y') }}<br>
                <strong>Time:</strong> {{ ($kot->created_at ?? $order->created_at)->format('H:i') }}<br>
                <strong>Table:</strong> {{ $order->table_number ?? 'Takeaway' }}
            </div>
        </div>
        
        <!-- Customer Info -->
        @if($order->customer_name || $order->customer_phone)
        <div style="margin: 10px 0; font-size: 11px;">
            @if($order->customer_name)
                <strong>Customer:</strong> {{ $order->customer_name }}<br>
            @endif
            @if($order->customer_phone)
                <strong>Phone:</strong> {{ $order->customer_phone }}
            @endif
        </div>
        @endif
        
        <!-- Items -->
        <div class="kot-items">
            <div class="item-header">
                <div class="item-name">ITEM</div>
                <div class="item-qty">QTY</div>
            </div>
            
            @forelse($order->orderItems ?? $items ?? [] as $item)
            <div class="item-row">
                <div class="item-name">
                    {{ $item->menuItem->name ?? $item['name'] ?? 'Unknown Item' }}
                    @if(!empty($item->special_requests) || !empty($item['special_requests']))
                        <br><small style="font-style: italic;">* {{ $item->special_requests ?? $item['special_requests'] }}</small>
                    @endif
                </div>
                <div class="item-qty">{{ $item->quantity ?? $item['quantity'] ?? 1 }}</div>
            </div>
            @empty
            <div class="item-row">
                <div class="item-name">No items found</div>
                <div class="item-qty">0</div>
            </div>
            @endforelse
        </div>
        
        <!-- Special Instructions -->
        @if($order->special_instructions)
        <div class="special-instructions">
            <strong>SPECIAL INSTRUCTIONS:</strong><br>
            {{ $order->special_instructions }}
        </div>
        @endif
        
        <!-- Footer -->
        <div class="kot-footer">
            <div>Kitchen Station: {{ $kitchenStation->name ?? 'Main Kitchen' }}</div>
            <div style="margin-top: 5px;">
                <strong>Preparation Time:</strong> 
                {{ $order->estimated_preparation_time ?? '15' }} minutes
            </div>
            <div style="margin-top: 10px; font-weight: bold;">
                ORDER STATUS: {{ strtoupper($order->status ?? 'PENDING') }}
            </div>
        </div>
    </div>
    
    <!-- Print Controls -->
    <div class="print-controls no-print">
        <button class="btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print KOT
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
        <button class="btn" onclick="markAsStarted()">
            <i class="fas fa-play"></i> Start Cooking
        </button>
        <button class="btn" onclick="markAsReady()">
            <i class="fas fa-check"></i> Mark Ready
        </button>
    </div>

    <script>
        // Auto-print when page loads
        window.addEventListener('load', function() {
            // Small delay to ensure page is fully loaded
            setTimeout(function() {
                window.print();
            }, 500);
        });
        
        function markAsStarted() {
            if (confirm('Mark this order as started cooking?')) {
                updateKOTStatus('cooking');
            }
        }
        
        function markAsReady() {
            if (confirm('Mark this order as ready for pickup/serving?')) {
                updateKOTStatus('ready');
            }
        }
        
        function updateKOTStatus(status) {
            const orderId = {{ $order->id ?? 0 }};
            
            fetch(`/admin/orders/${orderId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order status updated successfully!');
                    location.reload();
                } else {
                    alert('Failed to update order status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update order status');
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>
