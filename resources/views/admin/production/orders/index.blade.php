@extends('layouts.admin')

@section('title', 'Production Orders')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Production Orders</h1>
                <p class="text-gray-600 mt-1">Manage kitchen production orders and sessions</p>
            </div>
            <div class="flex items-center gap-3">
                @if (!Auth::user()->branch_id)
                    <a href="{{ route('admin.production.requests.manage') }}"
                        class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg shadow transition duration-200 flex items-center gap-2">
                        <i class="fas fa-clipboard-check"></i>
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
                            <span class="bg-orange-800 text-white px-2 py-1 rounded-full text-xs">{{ $pendingCount }}</span>
                        @endif
                    </a>

                    @if ($pendingRequests > 0)
                        <a href="{{ route('admin.production.requests.aggregate') }}"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg shadow transition duration-200 flex items-center gap-2">
                            <i class="fas fa-layer-group"></i>
                            Aggregate Requests
                            <span
                                class="bg-green-800 text-white px-2 py-1 rounded-full text-xs">{{ $pendingRequests }}</span>
                        </a>
                    @endif
                @endif
                <a href="{{ route('admin.production.sessions.index') }}"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg shadow transition duration-200 flex items-center gap-2">
                    <i class="fas fa-play"></i>
                    Production Sessions
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-blue-600"></i>
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
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600"></i>
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
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
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
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-percentage text-purple-600"></i>
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
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm">
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Production Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Production Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Items</label>
                    <select name="item_id" class="w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="">All Items</option>
                        @foreach ($productionItems as $item)
                            <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <div class="flex gap-3 w-full">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.production.orders.index') }}"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200 text-center">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Production Orders Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order
                                Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Production Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From
                                Requests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                Production Order #{{ $order->id }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Created: {{ $order->created_at->format('M d, Y') }}
                                            </div>
                                            @if ($order->production_notes)
                                                <div class="text-xs text-gray-400 mt-1 max-w-48 truncate">
                                                    {{ $order->production_notes }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $order->production_date->format('M d, Y') }}
                                    </div>
                                    @if ($order->expected_completion_date)
                                        <div class="text-sm text-gray-500">
                                            Expected: {{ $order->expected_completion_date->format('M d, Y') }}
                                        </div>
                                    @endif
                                    @if ($order->production_date->isPast() && $order->status !== 'completed')
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                            Overdue
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $order->items->count() }} unique items</div>
                                    <div class="text-sm text-gray-500">
                                        {{ number_format($order->getTotalQuantityOrdered()) }} total qty</div>
                                    <div class="text-xs text-gray-400 max-w-48 truncate mt-1">
                                        {{ $order->items->pluck('item.name')->join(', ') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $order->productionRequests ? $order->productionRequests->count() : 0 }} requests
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $order->productionRequests ? $order->productionRequests->pluck('branch.name')->unique()->join(', ') : 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if (in_array($order->status, ['approved', 'in_production', 'completed']))
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ $order->getProductionProgress() }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ number_format($order->getProductionProgress(), 1) }}% complete</div>
                                        @if ($order->activeSessions->count() > 0)
                                            <div class="text-xs text-green-600 mt-1">
                                                <i class="fas fa-play mr-1"></i>{{ $order->activeSessions->count() }}
                                                active session(s)
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('admin.production.orders.show', $order) }}"
                                            class="text-blue-600 hover:text-blue-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if ($order->canStartProduction())
                                            <a href="{{ route('admin.production.sessions.create', ['order_id' => $order->id]) }}"
                                                class="text-green-600 hover:text-green-900" title="Start Production">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        @endif

                                        @if ($order->canBeApproved())
                                            <form method="POST"
                                                action="{{ route('admin.production.orders.approve', $order) }}"
                                                class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900"
                                                    title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($order->canBeCancelled())
                                            <form method="POST"
                                                action="{{ route('admin.production.orders.cancel', $order) }}"
                                                class="inline"
                                                onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900"
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
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
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
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        @if (!Auth::user()->branch_id)
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-layer-group text-blue-600"></i>
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

                <div class="bg-purple-50 border border-purple-200 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-play text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-purple-900">Production Sessions</h3>
                    </div>
                    <p class="text-purple-700 mb-4">Manage active production sessions and track real-time progress.</p>
                    <a href="{{ route('admin.production.sessions.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-tasks mr-2"></i>
                        Manage Sessions
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
