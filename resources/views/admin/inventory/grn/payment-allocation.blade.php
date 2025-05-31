@extends('layouts.admin')

@section('header-title', 'Allocate Payment to GRN')
@section('content')
    <div class="p-4 rounded-lg">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.grn.show', $grn->grn_id) }}" 
               class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to GRN
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Allocate Payment to GRN</h1>
            <p class="text-gray-600 mb-6">GRN #{{ $grn->grn_number }} - {{ $grn->supplier->name }}</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">GRN Summary</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Amount:</span>
                            <span class="font-medium">Rs. {{ number_format($grn->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Paid Amount:</span>
                            <span class="font-medium">Rs. {{ number_format($paidAmount, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-gray-600 font-medium">Balance:</span>
                            <span class="font-bold">Rs. {{ number_format($balance, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Payment Status</h3>
                    <div class="flex items-center mb-2">
                        <span class="mr-3">Current Status:</span>
                        @if($grn->isPaymentPaid())
                            <x-partials.badges.status-badge status="success" text="Fully Paid" />
                        @elseif($grn->isPaymentPartial())
                            <x-partials.badges.status-badge status="info" text="Partially Paid" />
                        @else
                            <x-partials.badges.status-badge status="warning" text="Pending Payment" />
                        @endif
                    </div>
                    <p class="text-sm text-gray-600">
                        Payment status will automatically update when payments are allocated
                    </p>
                </div>
            </div>
            
            <h3 class="text-lg font-medium text-gray-900 mb-4">Allocate New Payment</h3>
            
            <form method="POST" action="{{ route('admin.grn.link-payment', $grn->grn_id) }}">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="payment_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Select Payment
                        </label>
                        <select id="payment_id" name="payment_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a Payment</option>
                            @foreach($payments as $payment)
                                <option value="{{ $payment->id }}">
                                    Payment #{{ $payment->payment_number }} - 
                                    Rs. {{ number_format($payment->total_amount, 2) }} - 
                                    {{ $payment->payment_date->format('M d, Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Amount to Allocate (Max: Rs. {{ number_format($balance, 2) }})
                        </label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0.01" 
                            max="{{ $balance }}" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg flex items-center">
                        <i class="fas fa-link mr-2"></i> Allocate Payment
                    </button>
                </div>
            </form>
            
            @if($grn->payments->count() > 0)
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Allocated Payments</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Payment
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Method
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Allocated At
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($grn->payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('admin.payments.show', $payment->id) }}" 
                                               class="text-indigo-600 hover:text-indigo-800">
                                                #{{ $payment->payment_number }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $payment->payment_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $payment->paymentDetails->first()->method_type ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            Rs. {{ number_format($payment->pivot->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $payment->pivot->allocated_at->format('M d, Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection