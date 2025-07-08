<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Order Ticket - Order #{{ $order->order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/print.css') }}">
    <style>
        @media print {
            @page {
                size: 80mm 120mm;
                margin: 5mm;
            }

            html, body {
                padding: 0;
                margin: 0;
                background: white;
                font-size: 10px;
                line-height: 1.3;
                color: #000;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
            }

            .kot-header {
                text-align: center;
                border-bottom: 2px solid #000;
                padding-bottom: 5px;
                margin-bottom: 10px;
            }

            .kot-title {
                font-size: 14px;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .kot-info {
                margin-bottom: 8px;
            }

            .kot-items {
                border-top: 1px solid #000;
                border-bottom: 1px solid #000;
                padding: 5px 0;
            }

            .kot-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
                padding: 2px 0;
            }

            .kot-footer {
                text-align: center;
                margin-top: 10px;
                font-size: 8px;
            }

            .dotted-line {
                border-bottom: 1px dotted #000;
                margin: 5px 0;
            }
        }

        /* Screen styles */
        .kot-container {
            max-width: 300px;
            margin: 0 auto;
            background: white;
            border: 1px solid #ccc;
            padding: 20px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>

<body class="bg-gray-100 p-4">
    <!-- Print Actions (hidden during print) -->
    <div class="no-print mb-6 text-center">
        <div class="flex justify-center space-x-4">
            <button onclick="window.print()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-print mr-2"></i> Print KOT
            </button>
            <a href="{{ route('orders.show', $order->id) }}" 
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Order
            </a>
        </div>
    </div>

    <!-- KOT Content -->
    <div class="kot-container print-container">
        <!-- Header -->
        <div class="kot-header">
            <div class="kot-title">KITCHEN ORDER TICKET</div>
            <div class="text-xs">{{ $order->branch->name ?? 'Restaurant' }}</div>
        </div>

        <!-- Order Information -->
        <div class="kot-info">
            <div class="flex justify-between mb-1">
                <span class="font-bold">Order #:</span>
                <span>{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-bold">Table/Customer:</span>
                <span>{{ $order->reservation->table_number ?? $order->customer_name }}</span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-bold">Order Type:</span>
                <span class="capitalize">{{ $order->order_type ? $order->order_type->getLabel() : 'Unknown' }}</span>
            </div>
            @if($order->steward)
            <div class="flex justify-between mb-1">
                <span class="font-bold">Steward:</span>
                <span>{{ $order->steward->first_name }} {{ $order->steward->last_name }}</span>
            </div>
            @endif
            <div class="flex justify-between mb-1">
                <span class="font-bold">Time:</span>
                <span>{{ $order->created_at->format('H:i') }}</span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-bold">Date:</span>
                <span>{{ $order->created_at->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="dotted-line"></div>

        <!-- Order Items -->
        <div class="kot-items">
            <div class="font-bold mb-2 text-center">ITEMS TO PREPARE</div>
            @forelse($order->orderItems as $item)
            <div class="kot-item">
                <div>
                    <div class="font-bold">{{ $item->menuItem->name ?? $item->item_name }}</div>
                    @if($item->special_instructions)
                        <div class="text-xs italic text-gray-600">Note: {{ $item->special_instructions }}</div>
                    @endif
                </div>
                <div class="font-bold text-right">
                    x{{ $item->quantity }}
                </div>
            </div>
            @if(!$loop->last)
                <div class="border-b border-dotted border-gray-300 my-1"></div>
            @endif
            @empty
            <div class="kot-item">
                <div class="text-center">No items found to prepare</div>
            </div>
            @endforelse
        </div>

        <div class="dotted-line"></div>

        <!-- Special Instructions -->
        @if($order->notes)
        <div class="mb-3">
            <div class="font-bold text-xs mb-1">SPECIAL INSTRUCTIONS:</div>
            <div class="text-xs">{{ $order->notes }}</div>
        </div>
        <div class="dotted-line"></div>
        @endif

        <!-- Priority & Status -->
        <div class="flex justify-between items-center mb-3">
            <div class="text-xs">
                <span class="font-bold">Priority:</span>
                @if($order->order_type && ($order->order_type->value === 'dine_in_walk_in_demand' || $order->order_type->value === 'takeaway_walk_in_demand'))
                    <span class="bg-red-100 text-red-800 px-1 rounded">URGENT</span>
                @else
                    <span class="bg-green-100 text-green-800 px-1 rounded">NORMAL</span>
                @endif
            </div>
            <div class="text-xs">
                <span class="font-bold">Status:</span>
                <span class="bg-yellow-100 text-yellow-800 px-1 rounded uppercase">{{ $order->status }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="kot-footer">
            <div class="dotted-line"></div>
            <div>KOT Generated: {{ now()->format('d/m/Y H:i:s') }}</div>
            <div class="mt-1">Please prepare items in order of receipt</div>
            <div class="mt-1 font-bold">--- END OF KOT ---</div>
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
