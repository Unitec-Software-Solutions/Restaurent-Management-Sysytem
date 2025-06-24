@extends('layouts.admin')

@section('title', 'Aggregate Production Requests')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Aggregate Production Requests</h1>
                    <p class="text-gray-600 mt-1">Select and combine production requests to create production orders</p>
                </div>
                <a href="{{ route('admin.production.requests.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Requests
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.production.orders.store') }}" method="POST" id="aggregateForm">
                @csrf

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Production Requests</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="required_date_from" class="block text-sm font-medium text-gray-700 mb-2">Required
                                Date From</label>
                            <input type="date" name="required_date_from" id="required_date_from"
                                value="{{ request('required_date_from') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="required_date_to" class="block text-sm font-medium text-gray-700 mb-2">Required Date
                                To</label>
                            <input type="date" name="required_date_to" id="required_date_to"
                                value="{{ request('required_date_to') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                            <select name="branch_id" id="branch_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="applyFilters()"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-filter mr-2"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Available Requests -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Available Production Requests</h3>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="selectAll"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Select All</span>
                                </label>
                                <span class="text-sm text-gray-500">{{ $requests->count() }} requests available</span>
                            </div>
                        </div>
                    </div>

                    @if ($requests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-12 px-6 py-3"></th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Required Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($requests as $request)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <input type="checkbox" name="selected_requests[]"
                                                    value="{{ $request->id }}"
                                                    class="request-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    data-request-id="{{ $request->id }}"
                                                    data-branch="{{ $request->branch->name }}"
                                                    data-items="{{ $request->items->toJson() }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">Request #{{ $request->id }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $request->created_at->format('M d, Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $request->branch->name }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">{{ $request->items->count() }} items
                                                </div>
                                                <div class="text-xs text-gray-500 max-w-48 truncate">
                                                    {{ $request->items->pluck('item.name')->join(', ') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $request->required_date->format('M d, Y') }}
                                                @if ($request->required_date->isPast())
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                                                        Overdue
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($request->getTotalQuantityApproved()) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center text-gray-500">
                            <i class="fas fa-clipboard-list text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">No approved production requests found</p>
                            <p class="text-sm">Try adjusting your filters or check back later</p>
                        </div>
                    @endif
                </div>

                <!-- Include the dynamic summary and order creation sections -->
                @include('admin.production.orders.partials.aggregate_summary')

            </form>
        </div>
    </div>

    @include('admin.production.orders.partials.aggregate_scripts')
@endsection
