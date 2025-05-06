@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <x-card>
        <!-- Header -->
        <div class="mb-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Transaction History</h3>
            <p class="mt-1 text-sm text-gray-500">View and filter all inventory transactions.</p>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <form action="{{ route('inventory.transactions') }}" method="GET" 
                  class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
                <div class="flex-1">
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full sm:w-auto bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Filter Results
                    </button>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <x-table :headers="['Date & Time', 'Item Details', 'Type', 'Quantity', 'User', 'Notes']">
            @forelse($transactions as $transaction)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $transaction->created_at->format('M d, Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $transaction->item->name }}</div>
                        <div class="text-sm text-gray-500">{{ $transaction->item->category->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-badge type="{{ $transaction->isIncomingTransaction() ? 'success' : 'danger' }}">
                            {{ $transaction->getFormattedTypeAttribute() }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm {{ $transaction->isIncomingTransaction() ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $transaction->isIncomingTransaction() ? '+' : '-' }}{{ $transaction->quantity }} {{ $transaction->item->unit_of_measurement }}
                        </div>
                        @if($transaction->unit_price)
                            <div class="text-xs text-gray-500">${{ number_format($transaction->unit_price, 2) }} per unit</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $transaction->user->name }}
                        @if($transaction->branch)
                            <div class="text-xs text-gray-400">{{ $transaction->branch->name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div class="max-w-xs truncate">{{ $transaction->notes }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No transactions found for the selected period
                    </td>
                </tr>
            @endforelse
        </x-table>

        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="mt-4">
                {{ $transactions->withQueryString()->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection