@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <x-card>
        <!-- Header -->
        <div class="mb-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Expiry Report</h3>
            <p class="mt-1 text-sm text-gray-500">Monitor items approaching their expiration date.</p>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <form action="{{ route('inventory.expiry-report') }}" method="GET" class="sm:flex sm:items-center">
                <div class="w-full sm:max-w-xs">
                    <label for="days" class="block text-sm font-medium text-gray-700">Days Until Expiry</label>
                    <select name="days" id="days" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="7" {{ request('days', $daysThreshold) == 7 ? 'selected' : '' }}>Next 7 days</option>
                        <option value="14" {{ request('days', $daysThreshold) == 14 ? 'selected' : '' }}>Next 14 days</option>
                        <option value="30" {{ request('days', $daysThreshold) == 30 ? 'selected' : '' }}>Next 30 days</option>
                        <option value="60" {{ request('days', $daysThreshold) == 60 ? 'selected' : '' }}>Next 60 days</option>
                        <option value="90" {{ request('days', $daysThreshold) == 90 ? 'selected' : '' }}>Next 90 days</option>
                    </select>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-4">
                    <button type="submit" 
                            class="w-full sm:w-auto bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Update Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Items Table -->
        <x-table :headers="['Item Details', 'Category', 'Current Stock', 'Expiry Date', 'Status']">
            @forelse($expiringItems as $item)
                @php
                    $daysUntilExpiry = Carbon\Carbon::parse($item->expiry_date)->diffInDays(now());
                    $badgeType = $daysUntilExpiry <= 7 ? 'danger' : 
                                ($daysUntilExpiry <= 14 ? 'warning' : 'success');
                    $status = $daysUntilExpiry <= 7 ? 'Critical' : 
                             ($daysUntilExpiry <= 14 ? 'Warning' : 'Monitor');
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                        <div class="text-sm text-gray-500">SKU: {{ $item->sku }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $item->category->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            {{ $item->stocks->sum('current_quantity') }} {{ $item->unit_of_measurement }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            {{ Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $daysUntilExpiry }} days remaining
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-badge :type="$badgeType">
                            {{ $status }}
                        </x-badge>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No items expiring within {{ $daysThreshold }} days
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
</div>
@endsection