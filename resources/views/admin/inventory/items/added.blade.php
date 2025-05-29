@extends('layouts.admin')

@section('content')
<div class="p-4 rounded-lg">
    
    <!-- Main Content Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Card Header -->
        <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Recently Added Items</h2>
                <p class="text-sm text-gray-500">Items successfully added to inventory</p>
            </div>
            
            <a href="{{ route('admin.inventory.items.index') }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Items Dashboard
            </a>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-indigo-600">{{ $item->item_code }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-box text-indigo-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->unit_of_measurement }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                {{ $item->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            {{ $item->unit_of_measurement }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            Rs. {{ number_format($item->buying_price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            Rs. {{ number_format($item->selling_price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-partials.badges.status-badge status="success" text="Active" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No items found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Success Message -->
        <div class="p-6 bg-green-50 border-t border-green-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        Successfully added {{ count($items) }} items to inventory.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection