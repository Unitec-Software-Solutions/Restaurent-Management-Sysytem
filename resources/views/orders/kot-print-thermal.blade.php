<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOT - Order #{{ $order->order_number }} - Thermal</title>
    <style>
        /* Thermal Printer Optimized Styles */
        @media print {
            @page {
                size: 80mm auto; /* 80mm thermal paper width */
                margin: 1mm; /* Minimal margins */
            }
            
            * {
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box !important;
            }
            
            body {
                font-family: 'Courier New', monospace !important;
                font-size: 10px !important;
                line-height: 1.1 !important;
                color: #000 !important;
                background: white !important;
                width: 78mm !important;
                margin: 0 !important;
                padding: 1mm !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .thermal-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
            
            .thermal-header {
                text-align: center !important;
                margin-bottom: 2mm !important;
            }
            
            .thermal-title {
                font-size: 12px !important;
                font-weight: bold !important;
                margin-bottom: 1mm !important;
            }
            
            .thermal-subtitle {
                font-size: 9px !important;
                margin-bottom: 0.5mm !important;
            }
            
            .thermal-separator {
                text-align: center !important;
                margin: 1mm 0 !important;
                font-size: 8px !important;
            }
            
            .thermal-info-row {
                display: flex !important;
                justify-content: space-between !important;
                margin: 0.5mm 0 !important;
                font-size: 9px !important;
            }
            
            .thermal-info-left {
                text-align: left !important;
            }
            
            .thermal-info-right {
                text-align: right !important;
            }
            
            .thermal-section-title {
                text-align: center !important;
                font-weight: bold !important;
                font-size: 10px !important;
                margin: 2mm 0 1mm 0 !important;
            }
            
            .thermal-item {
                margin: 1mm 0 !important;
                border-bottom: 1px dotted #000 !important;
                padding-bottom: 0.5mm !important;
            }
            
            .thermal-item-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                font-size: 9px !important;
            }
            
            .thermal-item-name {
                font-weight: bold !important;
                flex: 1 !important;
                margin-right: 2mm !important;
            }
            
            .thermal-item-qty {
                background: #000 !important;
                color: white !important;
                padding: 0.5mm 1mm !important;
                border-radius: 1mm !important;
                font-size: 8px !important;
                font-weight: bold !important;
                min-width: 8mm !important;
                text-align: center !important;
            }
            
            .thermal-item-note {
                font-size: 8px !important;
                font-style: italic !important;
                margin-top: 0.5mm !important;
                color: #333 !important;
            }
            
            .thermal-instructions {
                margin: 2mm 0 !important;
                padding: 1mm !important;
                border: 1px solid #000 !important;
                font-size: 8px !important;
            }
            
            .thermal-instructions-title {
                font-weight: bold !important;
                margin-bottom: 0.5mm !important;
            }
            
            .thermal-footer {
                text-align: center !important;
                margin-top: 2mm !important;
                font-size: 8px !important;
                border-top: 1px dashed #000 !important;
                padding-top: 1mm !important;
            }
            
            .thermal-bold {
                font-weight: bold !important;
            }
        }
        
        /* Screen Preview Styles */
        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.3;
            margin: 20px;
            background: #f0f0f0;
        }
        
        .thermal-container {
            width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 10px;
            border: 1px solid #ccc;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .thermal-header {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .thermal-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .thermal-subtitle {
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .thermal-separator {
            text-align: center;
            margin: 8px 0;
            font-size: 10px;
        }
        
        .thermal-info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 12px;
        }
        
        .thermal-section-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0 5px 0;
        }
        
        .thermal-item {
            margin: 8px 0;
            border-bottom: 1px dotted #333;
            padding-bottom: 3px;
        }
        
        .thermal-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }
        
        .thermal-item-name {
            font-weight: bold;
            flex: 1;
            margin-right: 10px;
        }
        
        .thermal-item-qty {
            background: #000;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
        }
        
        .thermal-item-note {
            font-size: 10px;
            font-style: italic;
            margin-top: 2px;
            color: #666;
        }
        
        .thermal-instructions {
            margin: 10px 0;
            padding: 5px;
            border: 1px solid #000;
            font-size: 10px;
        }
        
        .thermal-instructions-title {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .thermal-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        
        .thermal-bold {
            font-weight: bold;
        }
        
        .preview-controls {
            text-align: center;
            margin: 20px 0;
            background: white;
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
    </style>
</head>

<body>
    <!-- Preview Controls (hidden during print) -->
    <div class="preview-controls no-print">
        <h3>Thermal KOT Preview (80mm)</h3>
        <p>Optimized for thermal receipt printers</p>
        <button class="btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print KOT
        </button>
        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Order
        </a>
    </div>

    <!-- Thermal KOT Content -->
    <div class="thermal-container">
        <!-- Header -->
        <div class="thermal-header">
            <div class="thermal-title">KITCHEN ORDER TICKET</div>
            <div class="thermal-subtitle">{{ $order->branch->name ?? 'RESTAURANT' }}</div>
            @if($order->branch->phone)
            <div class="thermal-subtitle">{{ $order->branch->phone }}</div>
            @endif
        </div>

        <div class="thermal-separator">================================</div>

        <!-- Order Information -->
        <div class="thermal-info-row">
            <div class="thermal-info-left thermal-bold">ORDER #{{ $order->order_number }}</div>
            <div class="thermal-info-right">{{ $order->created_at->format('d/m/y') }}</div>
        </div>

        <div class="thermal-info-row">
            <div class="thermal-info-left">Time:</div>
            <div class="thermal-info-right thermal-bold">{{ $order->created_at->format('H:i') }}</div>
        </div>

        <div class="thermal-info-row">
            <div class="thermal-info-left">Type:</div>
            <div class="thermal-info-right">{{ strtoupper($order->order_type ? $order->order_type->getLabel() : 'TAKEAWAY') }}</div>
        </div>

        @if($order->customer_name)
        <div class="thermal-info-row">
            <div class="thermal-info-left">Customer:</div>
            <div class="thermal-info-right">{{ $order->customer_name }}</div>
        </div>
        @endif

        @if($order->reservation && $order->reservation->table_number)
        <div class="thermal-info-row">
            <div class="thermal-info-left">Table:</div>
            <div class="thermal-info-right thermal-bold">{{ $order->reservation->table_number }}</div>
        </div>
        @endif

        @if($order->steward)
        <div class="thermal-info-row">
            <div class="thermal-info-left">Steward:</div>
            <div class="thermal-info-right">{{ $order->steward->first_name }}</div>
        </div>
        @endif

        <div class="thermal-separator">--------------------------------</div>

        <!-- Items Section -->
        <div class="thermal-section-title">ITEMS TO PREPARE</div>

        @forelse($order->orderItems as $item)
        <div class="thermal-item">
            <div class="thermal-item-header">
                <div class="thermal-item-name">{{ $item->menuItem->name }}</div>
                <div class="thermal-item-qty">{{ $item->quantity }}x</div>
            </div>
            
            @if($item->special_instructions)
            <div class="thermal-item-note">* {{ $item->special_instructions }}</div>
            @endif
            
            @if($item->menuItem && $item->menuItem->preparation_time)
            <div class="thermal-item-note">Prep: {{ $item->menuItem->preparation_time }}min</div>
            @endif
        </div>
        @empty
        <div class="thermal-item">
            <div class="thermal-item-header">
                <div class="thermal-item-name">No items in this order</div>
                <div class="thermal-item-qty">0</div>
            </div>
        </div>
        @endforelse

        <!-- Special Instructions -->
        @if($order->special_instructions)
        <div class="thermal-instructions">
            <div class="thermal-instructions-title">SPECIAL INSTRUCTIONS:</div>
            <div>{{ $order->special_instructions }}</div>
        </div>
        @endif

        <div class="thermal-separator">--------------------------------</div>

        <!-- Status Information -->
        <div class="thermal-info-row">
            <div class="thermal-info-left">Status:</div>
            <div class="thermal-info-right thermal-bold">{{ strtoupper($order->status) }}</div>
        </div>

        @if($order->order_type && ($order->order_type->value === 'dine_in_walk_in_demand' || $order->order_type->value === 'takeaway_walk_in_demand'))
        <div class="thermal-info-row">
            <div class="thermal-info-left thermal-bold">PRIORITY:</div>
            <div class="thermal-info-right thermal-bold">URGENT</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="thermal-footer">
            <div>KOT Generated: {{ now()->format('d/m/Y H:i') }}</div>
            <div>Please prepare items in order</div>
            <div class="thermal-bold">======= END OF KOT =======</div>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.addEventListener('load', function() {
            setTimeout(() => {
                window.print();
            }, 500);
        });

        // Print function for manual triggering
        function printKOT() {
            window.print();
        }
    </script>
</body>
</html>
