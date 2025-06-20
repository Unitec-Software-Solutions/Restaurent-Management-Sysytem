<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill - Order #{{ $order->order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/print.css') }}">
    <style>
        @media print {
            @page {
                size: 80mm auto;
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

            .bill-header {
                text-align: center;
                border-bottom: 2px solid #000;
                padding-bottom: 5px;
                margin-bottom: 10px;
            }

            .bill-title {
                font-size: 14px;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .bill-info {
                margin-bottom: 8px;
            }

            .bill-items {
                border-top: 1px solid #000;
                border-bottom: 1px solid #000;
                padding: 5px 0;
            }

            .bill-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
                padding: 2px 0;
            }

            .bill-totals {
                margin-top: 8px;
                border-top: 1px solid #000;
                padding-top: 5px;
            }

            .bill-total-line {
                display: flex;
                justify-content: space-between;
                margin-bottom: 2px;
            }

            .bill-grand-total {
                border-top: 2px solid #000;
                padding-top: 3px;
                margin-top: 5px;
                font-weight: bold;
                font-size: 12px;
            }

            .bill-footer {
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
        .bill-container {
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
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-print mr-2"></i> Print Bill
            </button>
            <a href="{{ route('orders.show', $order->id) }}" 
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Order
            </a>
        </div>
    </div>

    <!-- Bill Content -->
    <div class="bill-container print-container">
        <!-- Header -->
        <div class="bill-header">
            <div class="bill-title">{{ $order->branch->name ?? 'RESTAURANT' }}</div>
            <div class="text-xs">{{ $order->branch->address ?? 'Address Here' }}</div>
            @if($order->branch->phone)
            <div class="text-xs">Tel: {{ $order->branch->phone }}</div>
            @endif
            <div class="text-xs">GST: {{ $order->branch->gst_number ?? 'GST123456789' }}</div>
        </div>

        <!-- Bill Information -->
        <div class="bill-info">
            <div class="flex justify-between mb-1">
                <span class="font-bold">Bill #:</span>
                <span>{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-bold">Order #:</span>
                <span>{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-bold">Date:</span>
                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between mb-1">
                <span class="font-bold">Customer:</span>
                <span>{{ $order->customer_name ?? 'Walk-in Customer' }}</span>
            </div>
            @if($order->customer_phone)
            <div class="flex justify-between mb-1">
                <span class="font-bold">Phone:</span>
                <span>{{ $order->customer_phone }}</span>
            </div>
            @endif
            @if($order->reservation && $order->reservation->table_number)
            <div class="flex justify-between mb-1">
                <span class="font-bold">Table:</span>
                <span>{{ $order->reservation->table_number }}</span>
            </div>
            @endif
            @if($order->steward)
            <div class="flex justify-between mb-1">
                <span class="font-bold">Served by:</span>
                <span>{{ $order->steward->first_name }} {{ $order->steward->last_name }}</span>
            </div>
            @endif
        </div>

        <div class="dotted-line"></div>

        <!-- Order Items -->
        <div class="bill-items">
            <div class="flex justify-between font-bold mb-2">
                <span>ITEM</span>
                <span>QTY</span>
                <span>RATE</span>
                <span>AMOUNT</span>
            </div>
            @foreach($order->items as $item)
            <div class="bill-item text-xs">
                <div class="flex-1">
                    <div class="font-medium">{{ $item->menuItem->name }}</div>
                </div>
            </div>
            <div class="bill-item text-xs">
                <div class="flex-1"></div>
                <div class="w-8 text-center">{{ $item->quantity }}</div>
                <div class="w-12 text-right">{{ number_format($item->unit_price, 2) }}</div>
                <div class="w-16 text-right">{{ number_format($item->total_price, 2) }}</div>
            </div>
            @if(!$loop->last)
                <div class="border-b border-dotted border-gray-300 my-1"></div>
            @endif
            @endforeach
        </div>

        <!-- Totals -->
        <div class="bill-totals">
            <div class="bill-total-line">
                <span>Subtotal:</span>
                <span>Rs. {{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->service_charge > 0)
            <div class="bill-total-line">
                <span>Service Charge (10%):</span>
                <span>Rs. {{ number_format($order->service_charge, 2) }}</span>
            </div>
            @endif
            @if($order->tax > 0)
            <div class="bill-total-line">
                <span>VAT (13%):</span>
                <span>Rs. {{ number_format($order->tax, 2) }}</span>
            </div>
            @endif
            @if($order->discount > 0)
            <div class="bill-total-line">
                <span>Discount:</span>
                <span>-Rs. {{ number_format($order->discount, 2) }}</span>
            </div>
            @endif
            
            <div class="bill-total-line bill-grand-total">
                <span>TOTAL AMOUNT:</span>
                <span>Rs. {{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <div class="dotted-line"></div>

        <!-- Payment Information -->
        @if($order->bills->isNotEmpty())
        <div class="mb-3">
            <div class="font-bold text-xs mb-1">PAYMENT DETAILS:</div>
            @foreach($order->bills as $bill)
            <div class="flex justify-between text-xs">
                <span>{{ ucfirst($bill->payment_method) }}:</span>
                <span>Rs. {{ number_format($bill->amount_paid, 2) }}</span>
            </div>
            @endforeach
            @php
                $totalPaid = $order->bills->sum('amount_paid');
                $balance = $order->total - $totalPaid;
            @endphp
            @if($balance > 0)
            <div class="flex justify-between text-xs font-bold mt-1">
                <span>Balance Due:</span>
                <span>Rs. {{ number_format($balance, 2) }}</span>
            </div>
            @elseif($balance < 0)
            <div class="flex justify-between text-xs font-bold mt-1">
                <span>Change:</span>
                <span>Rs. {{ number_format(abs($balance), 2) }}</span>
            </div>
            @else
            <div class="text-xs font-bold mt-1 text-center">PAID IN FULL</div>
            @endif
        </div>
        <div class="dotted-line"></div>
        @endif

        <!-- QR Code for Digital Receipt (Optional) -->
        <div class="text-center mb-3">
            <div class="text-xs mb-1">Scan for digital receipt:</div>
            <div class="text-xs">[QR CODE PLACEHOLDER]</div>
        </div>

        <!-- Footer -->
        <div class="bill-footer">
            <div class="text-center mb-2">
                <div>Thank you for dining with us!</div>
                <div>Please visit again</div>
            </div>
            <div class="dotted-line"></div>
            <div>Bill printed: {{ now()->format('d/m/Y H:i:s') }}</div>
            <div class="mt-1">
                <div>Cashier: {{ Auth::user()->name ?? 'System' }}</div>
            </div>
            <div class="mt-2 font-bold">--- END OF BILL ---</div>
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
        function printBill() {
            window.print();
        }
    </script>
</body>
</html>
