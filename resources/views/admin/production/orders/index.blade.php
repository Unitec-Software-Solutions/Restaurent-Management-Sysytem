@extends('layouts.admin')

@section('title', 'Production Orders')

@section('header-title', 'Production Orders')

@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Header with navigation buttons -->
        <div class="justify-between items-center mb-4">
            <div class="rounded-lg">
                <x-nav-buttons :items="[
                    ['name' => 'Production', 'link' => route('admin.production.index')],
                    ['name' => 'Production Requests', 'link' => route('admin.production.requests.index')],
                    ['name' => 'Production Orders', 'link' => route('admin.production.orders.index')],
                    ['name' => 'Production Sessions', 'link' => route('admin.production.sessions.index')],
                    ['name' => 'Production Recipes', 'link' => route('admin.production.recipes.index')],
                    // ['name' => 'Ingredient Management', 'link' => '#', 'disabled' => true],
                ]" active="Production Orders" />
            </div>
        </div>



        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 mt-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <span class="text-sm text-green-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- KPI Cards -->
        {{-- <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 mt-4">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-blue-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Orders</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ $orders->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">In Production</h3>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $orders->where('status', 'in_production')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Completed</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ $orders->where('status', 'completed')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-percentage text-indigo-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Completion Rate</h3>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $orders->count() > 0 ? number_format(($orders->where('status', 'completed')->count() / $orders->count()) * 100, 1) : 0 }}%
                        </p>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.production.orders.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Order</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Enter order ID" aria-label="Search Order" autocomplete="off"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Items Filter -->
                <div>
                    <label for="item_id" class="block text-sm font-medium text-gray-700 mb-1">Items</label>
                    <select name="item_id" id="item_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Items</option>
                        @foreach ($productionItems as $item)
                            <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="in_production" {{ request('status') === 'in_production' ? 'selected' : '' }}>In
                            Production</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label for="sort_by" class="block text-sm font-medium text-gray-400 mb-1">Sort By</label>
                    <select name="sort_by" id="sort_by"
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-400" disabled>
                        <option value="">Default</option>
                        <option value="name_asc" {{ request('sort_by') == 'name_asc' ? 'selected' : '' }}>
                            Name (A-Z)</option>
                        <option value="name_desc" {{ request('sort_by') == 'name_desc' ? 'selected' : '' }}>
                            Name (Z-A)</option>
                        <option value="price_asc" {{ request('sort_by') == 'price_asc' ? 'selected' : '' }}>
                            Price (Low to High)</option>
                        <option value="price_desc" {{ request('sort_by') == 'price_desc' ? 'selected' : '' }}>
                            Price (High to Low)</option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end space-x-2 col-span-full md:col-span-1">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('admin.production.orders.index') }}"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>

                <!-- Production Date Range -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Production Date
                        Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            datepicker datepicker-buttons datepicker-autoselect-today datepicker-format="yyyy-mm-dd"
                            type="text"
                            name="date_from"
                            id="date_from"
                            value="{{ request('date_from', now()->subDays(30)->toDateString()) }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                        <input
                            datepicker datepicker-buttons datepicker-autoselect-today datepicker-format="yyyy-mm-dd"
                        type="text"
                        name="date_to"
                        id="date_to"
                        value="{{ request('date_to', now()->toDateString()) }}"

                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>
            </form>
        </div>

        <!-- Production Orders List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Production Orders</h2>
                    <p class="text-sm text-gray-500">
                        Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of
                        {{ $orders->total() }} orders
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        @if (Auth::guard('admin')->user()->is_super_admin)
                            Organization: All Organizations (Super Admin)
                        @elseif(Auth::guard('admin')->user()->organization)
                            Organization: {{ Auth::guard('admin')->user()->organization->name }}
                        @else
                            Organization: Not Assigned
                        @endif
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    @if (!Auth::user()->branch_id)
                        <a href="{{ route('admin.production.requests.manage') }}"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-clipboard-check mr-2"></i>
                            Approve Requests
                            @php
                                $pendingCount = \App\Models\ProductionRequestMaster::where(
                                    'organization_id',
                                    Auth::user()->organization_id,
                                )
                                    ->where('status', 'submitted')
                                    ->count();
                            @endphp
                            @if ($pendingCount > 0)
                                <span
                                    class="bg-orange-800 text-white px-2 py-1 rounded-full text-xs ml-2">{{ $pendingCount }}</span>
                            @endif
                        </a>

                        @if ($pendingRequests > 0)
                            <a href="{{ route('admin.production.requests.aggregate') }}"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-layer-group mr-2"></i>
                                Aggregate Requests
                                <span
                                    class="bg-green-800 text-white px-2 py-1 rounded-full text-xs ml-2">{{ $pendingRequests }}</span>
                            </a>
                        @endif
                    @endif

                    <a href="{{ route('admin.production.sessions.index') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-play mr-2"></i>
                        Production Sessions
                    </a>
                </div>
            </div>

            <!-- Quick Filter Buttons -->
            <div class="border-b px-6 pt-4 pb-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.production.orders.index') }}"
                    class="px-3 py-1 text-sm rounded-full {{ !request()->hasAny(['status', 'item_id', 'date_from', 'date_to', 'search']) ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All
                </a>
                <a href="{{ route('admin.production.orders.index', ['status' => 'approved']) }}"
                    class="px-3 py-1 text-sm rounded-full bg-green-100 hover:bg-green-200 text-green-800">
                    Approved
                </a>
                <a href="{{ route('admin.production.orders.index', ['status' => 'in_production']) }}"
                    class="px-3 py-1 text-sm rounded-full bg-indigo-100 hover:bg-indigo-200 text-indigo-800">
                    In Production
                </a>
                <a href="{{ route('admin.production.orders.index', ['status' => 'completed']) }}"
                    class="px-3 py-1 text-sm rounded-full bg-blue-100 hover:bg-blue-200 text-blue-800">
                    Completed
                </a>
                <a href="{{ route('admin.production.orders.index', ['status' => 'cancelled']) }}"
                    class="px-3 py-1 text-sm rounded-full bg-orange-100 hover:bg-orange-200 text-orange-800">
                    Cancelled
                </a>
                @if (request('date_from') || request('date_to'))
                    <span class="px-3 py-1 text-sm bg-orange-100 text-orange-800 rounded-full">
                        <i class="fas fa-calendar mr-1"></i>Date Filter Active
                    </span>
                @endif
                @if (request()->hasAny(['status', 'item_id', 'date_from', 'date_to', 'search']))
                    <a href="{{ route('admin.production.orders.index') }}"
                        class="px-3 py-1 text-sm bg-red-100 hover:bg-red-200 text-red-800 rounded-full">
                        <i class="fas fa-times mr-1"></i>Clear Filters
                    </a>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Production Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                From Requests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                onclick="window.location='{{ route('admin.production.orders.show', $order) }}'">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-indigo-600">
                                        Production Order #{{ $order->id }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Created: {{ $order->created_at->format('d M Y') }}
                                    </div>
                                    @if ($order->production_notes)
                                        <div class="text-sm text-gray-400 mt-1 max-w-48 truncate">
                                            {{ $order->production_notes }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $order->production_date->format('d M Y') }}
                                    </div>
                                    @if ($order->expected_completion_date)
                                        <div class="text-sm text-gray-500">
                                            Expected: {{ $order->expected_completion_date->format('d M Y') }}
                                        </div>
                                    @endif
                                    @if ($order->production_date->isPast() && $order->status !== 'completed')
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 mt-1">
                                            Overdue
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div>{{ $order->items->count() }} items</div>
                                    <div class="text-sm text-gray-500">
                                        Total: {{ number_format($order->getTotalQuantityOrdered()) }} units</div>
                                    <div class="text-sm text-gray-400 max-w-48 truncate mt-1">
                                        {{ $order->items->pluck('item.name')->join(', ') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">
                                        {{ $order->productionRequests ? $order->productionRequests->count() : 0 }} requests
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $order->productionRequests ? $order->productionRequests->pluck('branch.name')->unique()->join(', ') : 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $order->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.production.orders.show', $order) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if ($order->canStartProduction())
                                            <a href="{{ route('admin.production.sessions.create', ['order_id' => $order->id]) }}"
                                                class="text-green-600 hover:text-green-800" title="Start Production">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        @endif

                                        @if ($order->canBeApproved())
                                            <form method="POST"
                                                action="{{ route('admin.production.orders.approve', $order) }}"
                                                class="inline"
                                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to approve this order?');">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800"
                                                    title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($order->canBeCancelled())
                                            <form method="POST"
                                                action="{{ route('admin.production.orders.cancel', $order) }}"
                                                class="inline"
                                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to cancel this order?');">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-800"
                                                    title="Cancel">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-industry text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">No production orders found</p>
                                    <p class="text-sm">Start by aggregating approved production requests</p>
                                    @if (!Auth::user()->branch_id && $pendingRequests > 0)
                                        <a href="{{ route('admin.production.requests.aggregate') }}"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                            <i class="fas fa-layer-group mr-2"></i>
                                            Aggregate Requests
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="px-6 py-4 bg-white border-t border-gray-200">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        @if (!Auth::user()->branch_id)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-layer-group text-blue-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-blue-900">Aggregate Requests</h3>
                    </div>
                    <p class="text-blue-700 mb-4">Combine multiple approved production requests into a single efficient
                        production order.</p>
                    @if ($pendingRequests > 0)
                        <a href="{{ route('admin.production.requests.aggregate') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Aggregate {{ $pendingRequests }} Request{{ $pendingRequests != 1 ? 's' : '' }}
                        </a>
                    @else
                        <p class="text-blue-600 text-sm">No pending requests available for aggregation.</p>
                    @endif
                </div>

                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-play text-indigo-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-indigo-900">Production Sessions</h3>
                    </div>
                    <p class="text-indigo-700 mb-4">Manage active production sessions and track real-time progress.</p>
                    <a href="{{ route('admin.production.sessions.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-tasks mr-2"></i>
                        Manage Sessions
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
