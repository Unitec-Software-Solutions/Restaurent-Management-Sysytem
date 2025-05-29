@extends('layouts.admin')

@section('content')
    <div class="p-6">
        <div class="mb-6">
            <x-nav-buttons :items="[
                ['name' => 'Back to GRNs', 'link' => route('admin.grn.index')],
            ]" active="GRN Details" />
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">GRN Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div><strong>GRN Number:</strong> {{ $grn->grn_number }}</div>
                <div><strong>Received Date:</strong> {{ $grn->received_date->format('Y-m-d') }}</div>
                <div><strong>Supplier:</strong> {{ $grn->supplier->name ?? '-' }}</div>
                <div><strong>Branch:</strong> {{ $grn->branch->name ?? '-' }}</div>
                <div><strong>Received By:</strong> {{ $grn->receivedByUser->name ?? '-' }}</div>
                <div><strong>Verified By:</strong> {{ $grn->verifiedByUser->name ?? '-' }}</div>
                <div><strong>Status:</strong> 
                    <span class="px-2 py-1 rounded-full text-white text-xs {{ 
                        $grn->status === 'Verified' ? 'bg-green-600' : 
                        ($grn->status === 'Rejected' ? 'bg-red-600' : 'bg-yellow-500') }}">
                        {{ $grn->status }}
                    </span>
                </div>
                <div><strong>Invoice No:</strong> {{ $grn->invoice_number ?? '-' }}</div>
                <div><strong>Delivery Note No:</strong> {{ $grn->delivery_note_number ?? '-' }}</div>
                <div><strong>Total Amount:</strong> Rs. {{ number_format($grn->total_amount, 2) }}</div>
                <div><strong>Organization:</strong> {{ $grn->organization->name ?? '-' }}</div>
                <div class="md:col-span-2"><strong>Notes:</strong> {{ $grn->notes ?? '-' }}</div>
            </div>
        </div>

        <!-- GRN Items Table -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">GRN Items</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-gray-200">
                    <thead class="bg-gray-50 text-gray-700 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-4 py-2 border">Item Code</th>
                            <th class="px-4 py-2 border">Item Name</th>
                            <th class="px-4 py-2 border">Batch No</th>
                            <th class="px-4 py-2 border">Ordered Qty</th>
                            <th class="px-4 py-2 border">Received Qty</th>
                            <th class="px-4 py-2 border">Accepted Qty</th>
                            <th class="px-4 py-2 border">Rejected Qty</th>
                            <th class="px-4 py-2 border">Buying Price</th>
                            <th class="px-4 py-2 border">Line Total</th>
                            <th class="px-4 py-2 border">MFG Date</th>
                            <th class="px-4 py-2 border">EXP Date</th>
                            <th class="px-4 py-2 border">Rejection Reason</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach ($grn->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border">{{ $item->item_code }}</td>
                                <td class="px-4 py-2 border">{{ $item->item->item_name ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $item->batch_no }}</td>
                                <td class="px-4 py-2 border text-right">{{ number_format($item->ordered_quantity, 2) }}</td>
                                <td class="px-4 py-2 border text-right">{{ number_format($item->received_quantity, 2) }}</td>
                                <td class="px-4 py-2 border text-right text-green-600">{{ number_format($item->accepted_quantity, 2) }}</td>
                                <td class="px-4 py-2 border text-right text-red-600">{{ number_format($item->rejected_quantity, 2) }}</td>
                                <td class="px-4 py-2 border text-right">Rs. {{ number_format($item->buying_price, 4) }}</td>
                                <td class="px-4 py-2 border text-right">Rs. {{ number_format($item->line_total, 2) }}</td>
                                <td class="px-4 py-2 border">{{ optional($item->manufacturing_date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-2 border">{{ optional($item->expiry_date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-2 border">{{ $item->rejection_reason ?? '-' }}</td>
                            </tr>
                        @endforeach

                        @if($grn->items->isEmpty())
                            <tr>
                                <td colspan="12" class="px-4 py-3 text-center text-gray-500">No GRN items found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
