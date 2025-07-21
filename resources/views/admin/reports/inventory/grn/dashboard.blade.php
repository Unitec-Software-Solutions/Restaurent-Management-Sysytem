@extends('layouts.admin')

@section('title', 'GRN Dashboard')
@section('header-title', 'Goods Received Notes (GRN) Dashboard')

@section('content')
<div class="mx-auto px-4 py-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">GRN Metrics Overview</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-3xl font-bold text-blue-600">{{ $grnCount }}</div>
            <div class="text-gray-700 mt-2">Total GRNs</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-3xl font-bold text-green-600">{{ number_format($totalAmount, 2) }}</div>
            <div class="text-gray-700 mt-2">Total Amount</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-3xl font-bold text-yellow-600">{{ $pendingCount }}</div>
            <div class="text-gray-700 mt-2">Pending</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-3xl font-bold text-emerald-600">{{ $verifiedCount }}</div>
            <div class="text-gray-700 mt-2">Verified</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-3xl font-bold text-red-600">{{ $rejectedCount }}</div>
            <div class="text-gray-700 mt-2">Rejected</div>
        </div>
    </div>

    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-2">Payment Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($paymentStatusCounts as $status => $count)
                <div class="bg-blue-50 rounded-lg p-4 flex flex-col items-center">
                    <div class="text-xl font-bold">{{ $count }}</div>
                    <div class="text-gray-700 mt-1">{{ ucfirst($status) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-2">Recent GRNs</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow">
                <thead>
                    <tr>
                        <th class="px-4 py-2">GRN #</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentGrns as $grn)
                        <tr>
                            <td class="px-4 py-2">{{ $grn->grn_number }}</td>
                            <td class="px-4 py-2">{{ $grn->received_date ?? $grn->created_at }}</td>
                            <td class="px-4 py-2">{{ number_format($grn->total_amount, 2) }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 rounded text-xs {{
                                    $grn->status === 'Verified' ? 'bg-emerald-100 text-emerald-700' :
                                    ($grn->status === 'Pending' ? 'bg-yellow-100 text-yellow-700' :
                                    ($grn->status === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'))
                                }}">
                                    {{ $grn->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 rounded text-xs {{
                                    $grn->payment_status === 'Paid' ? 'bg-green-100 text-green-700' :
                                    ($grn->payment_status === 'Partial' ? 'bg-yellow-100 text-yellow-700' :
                                    ($grn->payment_status === 'Pending' ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-700'))
                                }}">
                                    {{ $grn->payment_status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
