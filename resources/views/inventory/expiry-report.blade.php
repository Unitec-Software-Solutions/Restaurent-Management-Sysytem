@extends('layouts.app')

@section('content')
    <div class="grid gap-4">
        <!-- Filter Form -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
            <form method="GET" class="flex gap-4 items-end">
                <div>
                    <label for="days" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Days Threshold</label>
                    <input type="number" id="days" name="days" value="{{ $daysThreshold }}" min="1" max="90"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                </div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                    Apply Filter
                </button>
            </form>
        </div>

        <!-- Expiring Items Table -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Items Expiring Within {{ $daysThreshold }} Days</h3>
                <button type="button" onclick="window.print()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Item Name</th>
                            <th scope="col" class="px-6 py-3">Category</th>
                            <th scope="col" class="px-6 py-3">Current Stock</th>
                            <th scope="col" class="px-6 py-3">Expiry Date</th>
                            <th scope="col" class="px-6 py-3">Days Until Expiry</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expiringItems as $item)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                                <td class="px-6 py-4">{{ $item->category->name }}</td>
                                <td class="px-6 py-4">{{ $item->stocks->sum('current_quantity') }}</td>
                                <td class="px-6 py-4">{{ $item->expiry_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4">{{ $item->expiry_date->diffInDays(now()) }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $daysLeft = $item->expiry_date->diffInDays(now());
                                        $statusColor = $daysLeft <= 3 ? 'red' : ($daysLeft <= 7 ? 'yellow' : 'green');
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $statusColor === 'red' ? 'bg-red-100 text-red-800' : 
                                           ($statusColor === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                        {{ $daysLeft <= 3 ? 'Critical' : ($daysLeft <= 7 ? 'Warning' : 'Upcoming') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center">No items expiring within the specified timeframe.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection