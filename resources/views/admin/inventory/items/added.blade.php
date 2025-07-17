@extends('layouts.admin')
@section('header-title', 'Recently Added Items')
@section('content')
    <div class="container mx-auto px-4 py-8">

        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Recently Added Items</h2>
                    <p class="text-sm text-gray-500">Items from your last creation session</p>
                    @if ($items->count() > 0)
                        <p class="text-xs text-blue-600 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Showing {{ $items->count() }} item(s) from your last batch creation
                        </p>
                    @endif
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('admin.inventory.items.create') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add More Items
                    </a>
                    <a href="{{ route('admin.inventory.items.index') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Items
                    </a>
                </div>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
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
                                        <div
                                            class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
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
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('admin.inventory.items.show', $item->id) }}"
                                            class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.inventory.items.edit', $item->id) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Items Found</h3>
                                        <p class="text-gray-500 mb-4">No items were found from your last creation session.
                                        </p>
                                        <a href="{{ route('admin.inventory.items.create') }}"
                                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                            <i class="fas fa-plus mr-2"></i> Create Items
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Success Message -->
            @if ($items->count() > 0)
                <div class="p-6 bg-green-50 border-t border-green-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                Successfully added {{ $items->count() }} item{{ $items->count() > 1 ? 's' : '' }} to
                                inventory.
                            </p>
                            <p class="text-xs text-green-700 mt-1">
                                All items are now available in your inventory and can be used for stock management.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
