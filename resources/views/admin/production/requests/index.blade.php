@extends('layouts.admin')

@section('title', 'Production Requests')

@section('header-title', 'Production Requests')

@section('content')
    <div class="p-4 space-y-8">
        <!-- Navigation Buttons -->
        <div class="rounded-lg">
            <x-nav-buttons :items="[
                ['name' => 'Production', 'link' => route('admin.production.index')],
                ['name' => 'Production Requests', 'link' => route('admin.production.requests.index')],
                ['name' => 'Production Orders', 'link' => route('admin.production.orders.index')],
                ['name' => 'Production Sessions', 'link' => route('admin.production.sessions.index')],
            ]" active="Production Requests" />
        </div>
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Production Requests</h1>
                <p class="text-gray-600 mt-1">Manage production requests from branches</p>
            </div>
            @if (Auth::user()->branch_id)
                <a href="{{ route('admin.production.requests.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow transition duration-200 flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    New Production Request
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Requests</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
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
                        <h3 class="text-sm font-medium text-gray-500">Pending Approval</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_approval'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Approved</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['approved_requests'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-cog text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">In Production</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['in_production'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Production Requests</h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted
                        </option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="in_production" {{ request('status') == 'in_production' ? 'selected' : '' }}>In
                            Production</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                    <select name="branch_id"
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Request Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Request Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Required Date From</label>
                    <input type="date" name="required_date_from" value="{{ request('required_date_from') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>

            <!-- Quick Filter Buttons -->
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.production.requests.index') }}"
                    class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-full {{ !request()->hasAny(['status', 'branch_id', 'date_from', 'date_to', 'required_date_from']) ? 'bg-blue-100 text-blue-800' : 'text-gray-700' }}">
                    All
                </a>
                <a href="{{ route('admin.production.requests.index', ['status' => 'submitted']) }}"
                    class="px-3 py-1 text-sm bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-full">
                    Pending Approval ({{ $stats['pending_approval'] }})
                </a>
                <a href="{{ route('admin.production.requests.index', ['status' => 'approved']) }}"
                    class="px-3 py-1 text-sm bg-green-100 hover:bg-green-200 text-green-800 rounded-full">
                    Approved ({{ $stats['approved_requests'] }})
                </a>
                <a href="{{ route('admin.production.requests.index', ['status' => 'in_production']) }}"
                    class="px-3 py-1 text-sm bg-purple-100 hover:bg-purple-200 text-purple-800 rounded-full">
                    In Production ({{ $stats['in_production'] }})
                </a>
                @if (request('required_date_from') || request('required_date_to'))
                    <span class="px-3 py-1 text-sm bg-orange-100 text-orange-800 rounded-full">
                        <i class="fas fa-calendar mr-1"></i>Required Date Filter Active
                    </span>
                @endif
            </div>
        </div>

        <!-- Requests Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Production Requests
                            @if (request()->filled('branch_id'))
                                - {{ $branches->where('id', request('branch_id'))->first()->name ?? 'Unknown Branch' }}
                            @endif
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of
                            {{ $requests->total() }} requests
                            @if (request()->filled('status'))
                                with status: <span class="font-medium">{{ ucfirst(request('status')) }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if (request()->hasAny(['status', 'branch_id', 'date_from', 'date_to']))
                            <a href="{{ route('admin.production.requests.index') }}"
                                class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-lg">
                                <i class="fas fa-times mr-1"></i>Clear Filters
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Request Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items
                                Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Required Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($requests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                Request #{{ $request->id }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Created: {{ $request->request_date->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->branch->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->items->count() }} items</div>
                                    <div class="text-sm text-gray-500">
                                        {{ number_format($request->getTotalQuantityRequested()) }} total qty</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->required_date->format('M d, Y') }}
                                    </div>
                                    @if ($request->required_date->isPast())
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Overdue
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $request->getStatusBadgeClass() }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($request->status === 'approved' || $request->status === 'in_production' || $request->status === 'completed')
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ $request->getProductionProgress() }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ number_format($request->getProductionProgress(), 1) }}% complete</div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('admin.production.requests.show', $request) }}"
                                            class="text-blue-600 hover:text-blue-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if ($request->canBeSubmitted() && $request->created_by_user_id === Auth::id())
                                            <form method="POST"
                                                action="{{ route('admin.production.requests.submit', $request) }}"
                                                class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900"
                                                    title="Submit">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($request->canBeApproved() && !Auth::user()->branch_id)
                                            <form method="POST"
                                                action="{{ route('admin.production.requests.approve', $request) }}"
                                                class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900"
                                                    title="Approve">
                                                    <i class="fas fa-check"></i>
                                            </form>
                                        @endif

                                        @if ($request->canBeCancelled())
                                            <form method="POST"
                                                action="{{ route('admin.production.requests.cancel', $request) }}"
                                                class="inline"
                                                onsubmit="return confirm('Are you sure you want to cancel this request?')">
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
                                    <i class="fas fa-clipboard-list text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">No production requests found</p>
                                    <p class="text-sm">Start by creating your first production request</p>
                                    @if (Auth::user()->branch_id)
                                        <a href="{{ route('admin.production.requests.create') }}"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                            <i class="fas fa-plus mr-2"></i>
                                            Create Request
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>

        @if (!Auth::user()->branch_id && $requests->where('status', 'approved')->count() > 0)
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-blue-900">Ready for Production Planning</h3>
                        <p class="text-blue-700">You have approved requests that can be aggregated into production orders.
                        </p>
                    </div>
                    <a href="{{ route('admin.production.requests.aggregate') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-layer-group mr-2"></i>
                        Aggregate Requests
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
