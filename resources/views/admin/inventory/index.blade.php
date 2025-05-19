@extends('layouts.main')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Inventory Items</h2>
                    <a href="{{ route('admin.inventory.items.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Add New Item
                    </a>
                </div>

                <!-- Search and Filters -->
                <div class="mb-6">
                    <form action="{{ route('admin.inventory.items.index') }}" method="GET" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Search by name or code" 
                                class="w-full px-4 py-2 border rounded-md">
                        </div>

                        <div class="w-full md:w-auto">
                            <select name="category" class="w-full px-4 py-2 border rounded-md">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-auto">
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                Filter
                            </button>
                            <a href="{{ route('admin.inventory.items.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 ml-2">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">Code</th>
                                <th class="py-3 px-6 text-left">Name</th>
                                <th class="py-3 px-6 text-left">Category</th>
                                <th class="py-3 px-6 text-left">Unit</th>
                                <th class="py-3 px-6 text-right">Buying Price</th>
                                <th class="py-3 px-6 text-right">Selling Price</th>
                                <th class="py-3 px-6 text-center">Status</th>
                                <th class="py-3 px-6 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            @forelse($items as $item)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-6 text-left">{{ $item->item_code }}</td>
                                    <td class="py-3 px-6 text-left">{{ $item->name }}</td>
                                    <td class="py-3 px-6 text-left">{{ $item->category->name ?? 'N/A' }}</td>
                                    <td class="py-3 px-6 text-left">{{ $item->unit_of_measurement }}</td>
                                    <td class="py-3 px-6 text-right">{{ number_format($item->buying_price, 2) }}</td>
                                    <td class="py-3 px-6 text-right">{{ number_format($item->selling_price, 2) }}</td>
                                    <td class="py-3 px-6 text-center">
                                        @if($item->deleted_at)
                                            <span class="bg-red-200 text-red-800 py-1 px-3 rounded-full text-xs">Deleted</span>
                                        @else
                                            <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs">Active</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="flex item-center justify-center">
                                            <a href="{{ route('admin.inventory.items.show', $item->id) }}" class="transform hover:text-blue-500 hover:scale-110 mr-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.inventory.items.edit', $item->id) }}" class="transform hover:text-yellow-500 hover:scale-110 mr-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('admin.inventory.items.destroy', $item->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="transform hover:text-red-500 hover:scale-110" 
                                                        onclick="return confirm('Are you sure you want to delete this item?')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-6 text-center text-gray-500">No items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection