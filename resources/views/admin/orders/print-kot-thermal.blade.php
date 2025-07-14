<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOT #{{ $kot->id ?? $order->id }} - Thermal Print</title>
    <style>
        /* Thermal Printer Optimized Styles */
        @media print {
            @page {
                size: 80mm auto; /* 80mm thermal paper width */
                margin: 2mm; /* Minimal margins */
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
                font-family: 'Courier New', 'Monaco', 'Lucida Console', monospace !important;
                font-size: 11px !important;
                line-height: 1.2 !important;
                color: #000 !important;
                background: white !important;
                width: 76mm !important; /* Slightly less than paper width */
            }
            
            .no-print {
                display: none !important;
            }
            
            .thermal-kot {
                width: 100% !important;
                max-width: 76mm !important;
                margin: 0 !important;
                padding: 2mm !important;
                border: none !important;
                box-shadow: none !important;
            }
            
            .thermal-line {
                border-bottom: 1px dashed #000 !important;
                margin: 2mm 0 !important;
                padding: 0 !important;
                height: 1px !important;
                width: 100% !important;
            }
            
            .thermal-center {
                text-align: center !important;
            }
            
            .thermal-bold {
                font-weight: bold !important;
                font-size: 12px !important;
            }
            
            .thermal-row {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin: 1mm 0 !important;
                padding: 0 !important;
            }
            
            .thermal-left {
                text-align: left !important;
                flex: 1 !important;
            }
            
            .thermal-right {
                text-align: right !important;
                white-space: nowrap !important;
            }
            
            .thermal-item {
                margin: 1mm 0 !important;
                padding: 0 !important;
            }
            
            .thermal-qty {
                background: #000 !important;
                color: white !important;
                padding: 1px 3px !important;
                border-radius: 2px !important;
                font-size: 10px !important;
                font-weight: bold !important;
            }
            
            .thermal-note {
                font-style: italic !important;
                font-size: 9px !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .thermal-footer {
                margin-top: 3mm !important;
                text-align: center !important;
                font-size: 9px !important;
            }
        }
        
        /* Screen styles for preview */
        body {
            font-family: 'Courier New', 'Monaco', 'Lucida Console', monospace;
            font-size: 14px;
            line-height: 1.3;
            margin: 20px;
            background: #f5f5f5;
        }
        
        .thermal-kot {
            width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .thermal-line {
            border-bottom: 1px dashed #333;
            margin: 8px 0;
            height: 1px;
        }
        
        .thermal-center {
            text-align: center;
        }
        
        .thermal-bold {
            font-weight: bold;
            font-size: 16px;
        }
        
        .thermal-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 4px 0;
        }
        
        .thermal-left {
            text-align: left;
            flex: 1;
        }
        
        .thermal-right {
            text-align: right;
            white-space: nowrap;
        }
        
        .thermal-item {
            margin: 6px 0;
        }
        
        .thermal-qty {
            background: #000;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .thermal-note {
            font-style: italic;
            font-size: 12px;
            color: #666;
            margin: 2px 0;
        }
        
        .thermal-footer {
            margin-top: 15px;
            text-align: center;
            font-size: 11px;
        }
        
        .print-controls {
            text-align: center;
            margin: 20px 0;
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            text-decoration: none;
            display: inline-block;
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
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <!-- Print Controls (hidden during print) -->
    <div class="print-controls no-print">
        <h3>Thermal KOT Print Preview</h3>
        <p>This is optimized for 80mm thermal printers</p>
        <button class="btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print to Thermal Printer
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
        <button class="btn btn-success" onclick="markAsStarted()">
            <i class="fas fa-play"></i> Start Cooking
        </button>
    </div>

    <!-- Thermal KOT Content -->
    <div class="thermal-kot">
        <!-- Header -->
        <div class="thermal-center">
            <div class="thermal-bold">KITCHEN ORDER TICKET</div>
            <div>{{ $order->organization->name ?? 'RESTAURANT' }}</div>
            <div>{{ $order->branch->name ?? 'MAIN BRANCH' }}</div>
        </div>
        
        <div class="thermal-line"></div>
        
        <!-- Order Information -->
        <div class="thermal-row">
            <div class="thermal-left thermal-bold">KOT #{{ $kot->id ?? $order->id }}</div>
            <div class="thermal-right">{{ ($kot->created_at ?? $order->created_at)->format('d/m/y H:i') }}</div>
        </div>
        
        <div class="thermal-row">
            <div class="thermal-left">Order #{{ $order->order_number ?? $order->id }}</div>
            <div class="thermal-right">{{ strtoupper($order->order_type ? $order->getOrderTypeLabel() : 'TAKEAWAY') }}</div>
        </div>
        
        @if($order->customer_name)
        <div class="thermal-row">
            <div class="thermal-left">Customer:</div>
            <div class="thermal-right">{{ $order->customer_name }}</div>
        </div>
        @endif
        
        @if($order->customer_phone)
        <div class="thermal-row">
            <div class="thermal-left">Phone:</div>
            <div class="thermal-right">{{ $order->customer_phone }}</div>
        </div>
        @endif
        
        @if($order->table_number)
        <div class="thermal-row">
            <div class="thermal-left">Table:</div>
            <div class="thermal-right">{{ $order->table_number }}</div>
        </div>
        @endif
        
        <div class="thermal-line"></div>
        
        <!-- Items Section -->
        <div class="thermal-center thermal-bold">ITEMS TO PREPARE</div>
        <div class="thermal-line"></div>
        
        @forelse($order->orderItems ?? $items ?? [] as $item)
        <div class="thermal-item">
            <div class="thermal-row">
                <div class="thermal-left thermal-bold">{{ $item->menuItem->name ?? $item['name'] ?? 'Unknown Item' }}</div>
                <div class="thermal-right">
                    <span class="thermal-qty">{{ $item->quantity ?? $item['quantity'] ?? 1 }}x</span>
                </div>
            </div>
            
            @if(!empty($item->special_instructions) || !empty($item['special_instructions']))
            <div class="thermal-note">
                * {{ $item->special_instructions ?? $item['special_instructions'] }}
            </div>
            @endif
            
            @if($item->menuItem && $item->menuItem->preparation_time)
            <div class="thermal-note">
                Prep: {{ $item->menuItem->preparation_time }}min
            </div>
            @endif
        </div>
        @empty
        <div class="thermal-item">
            <div class="thermal-center">No items found</div>
        </div>
        @endforelse
        
        <div class="thermal-line"></div>
        
        <!-- Special Instructions -->
        @if($order->special_instructions)
        <div class="thermal-center thermal-bold">SPECIAL INSTRUCTIONS</div>
        <div class="thermal-note">{{ $order->special_instructions }}</div>
        <div class="thermal-line"></div>
        @endif
        
        <!-- Footer Information -->
        <div class="thermal-row">
            <div class="thermal-left">Status:</div>
            <div class="thermal-right thermal-bold">{{ strtoupper($order->status ?? 'PENDING') }}</div>
        </div>
        
        <div class="thermal-row">
            <div class="thermal-left">Station:</div>
            <div class="thermal-right">{{ $kitchenStation->name ?? 'MAIN KITCHEN' }}</div>
        </div>
        
        <div class="thermal-line"></div>
        
        <!-- Footer -->
        <div class="thermal-footer">
            <div>Printed: {{ now()->format('d/m/Y H:i:s') }}</div>
            <div>Prepare items in order received</div>
            <div class="thermal-bold">--- END OF KOT ---</div>
        </div>
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
                updateKOTStatus('preparing');
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
        
        // Thermal printer specific functions
        function sendToThermalPrinter() {
            // If you have thermal printer drivers or APIs, you can integrate here
            window.print();
        }
    </script>
</body>
</html>
