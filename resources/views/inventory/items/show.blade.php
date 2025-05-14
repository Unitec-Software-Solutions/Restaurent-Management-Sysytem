<?php
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Item Details</h2>
        <div class="space-x-2">
            <a href="{{ route('inventory.items.edit', $item) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Edit</a>
            <a href="{{ route('inventory.items.index') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item Name</label>
                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $item->name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU</label>
                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $item->sku }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $item->inventoryCategory->name }}</p>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of Measurement</label>
                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $item->unit_of_measurement }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reorder Level</label>
                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $item->reorder_level }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <p class="mt-1">
                    <span class="px-2 py-1 text-sm rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Current Stock Section -->
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Current Stock</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Updated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($item->stocks as $stock)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $stock->branch->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $stock->quantity }} {{ $item->unit_of_measurement }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $stock->updated_at->format('d M, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection