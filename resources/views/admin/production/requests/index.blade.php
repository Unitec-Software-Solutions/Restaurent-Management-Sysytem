@extends('layouts.admin')

@section('title', 'Production Requests')

@section('header-title', 'Production Requests')

@section('content')
    <div class="p-4 rounded-lg">
        <!-- Header with navigation buttons -->
        <div class="justify-between items-center mb-4">
            <div class="rounded-lg">
                <x-nav-buttons :items="[
                    ['name' => 'Production', 'link' => route('admin.production.index')],
                    ['name' => 'Production Requests', 'link' => route('admin.production.requests.index')],
                    ['name' => 'Production Orders', 'link' => route('admin.production.orders.index')],
                    ['name' => 'Production Sessions', 'link' => route('admin.production.sessions.index')],
                    ['name' => 'Production Recipes', 'link' => route('admin.production.recipes.index')],
                    ['name' => 'Ingredient Management', 'link' => '#', 'disabled' => true],
                ]" active="Production Requests" />
            </div>
        </div>

        <!-- Banner for aggregating approved requests -->
        @if (!Auth::user()->branch_id && $stats['approved_requests'] > 0)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-1">
                            <i class="fas fa-layer-group text-blue-500 text-2xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-900">Ready for Production Planning</h3>
                            <p class="text-blue-700 mt-1">
                                You have {{ $stats['approved_requests'] }} approved
                                {{ Str::plural('request', $stats['approved_requests']) }} that can be aggregated into
                                production orders.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.production.requests.aggregate') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center justify-center whitespace-nowrap">
                        <i class="fas fa-layer-group mr-2"></i>
                        Aggregate Requests
                    </a>
                </div>
            </div>
        @endif

        <!-- Search and Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.production.requests.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input (if applicable, add a search field for request ID or notes) -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Request</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Enter request ID or notes" aria-label="Search Request" autocomplete="off"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <!-- Branch Filter -->
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id" id="branch_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
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
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted
                        </option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                        </option>
                        <option value="in_production" {{ request('status') == 'in_production' ? 'selected' : '' }}>In
                            Production</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
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
                    <a href="{{ route('admin.production.requests.index') }}"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
                <!-- Date Range -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Request Date
                        Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <!-- Additional Filters (Required Date Range) -->
                <div>
                    <label for="required_date_from" class="block text-sm font-medium text-gray-700 mb-1">Required Date
                        Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="required_date_from" id="required_date_from"
                            value="{{ request('required_date_from') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="date" name="required_date_to" id="required_date_to"
                            value="{{ request('required_date_to') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </form>
        </div>

        <!-- Production Request List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Production Requests</h2>
                    <p class="text-sm text-gray-500">
                        Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of
                        {{ $requests->total() }} requests
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
                    @endif
                    <a href="{{ route('admin.production.requests.create') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Production Request
                    </a>
                </div>
            </div>

            <!-- Quick Filter Buttons -->
            <div class="border-b px-6 pt-4 pb-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.production.requests.index') }}"
                    class="px-3 py-1 text-sm rounded-full {{ !request()->hasAny(['status', 'branch_id', 'date_from', 'date_to', 'required_date_from', 'required_date_to', 'search']) ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All
                </a>
                {{-- <a href="{{ route('admin.production.requests.index', ['status' => 'draft']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'draft' ? 'bg-gray-200 text-gray-900 font-medium' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}">
                    Draft ({{ $stats['draft'] ?? 0 }})
                </a>
                <a href="{{ route('admin.production.requests.index', ['status' => 'submitted']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'submitted' ? 'bg-blue-200 text-blue-900 font-medium' : 'bg-blue-100 hover:bg-blue-200 text-blue-800' }}">
                    Submitted ({{ $stats['submitted'] ?? 0 }})
                </a> --}}
                <a href="{{ route('admin.production.requests.index', ['status' => 'approved']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'approved' ? 'bg-green-200 text-green-900 font-medium' : 'bg-green-100 hover:bg-green-200 text-green-800' }}">
                    Approved ({{ $stats['approved_requests'] ?? 0 }})
                </a>
                <a href="{{ route('admin.production.requests.index', ['status' => 'in_production']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'in_production' ? 'bg-orange-200 text-orange-900 font-medium' : 'bg-orange-100 hover:bg-orange-200 text-orange-800' }}">
                    In Production ({{ $stats['in_production'] ?? 0 }})
                </a>
                <a href="{{ route('admin.production.requests.index', ['status' => 'completed']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'completed' ? 'bg-emerald-200 text-emerald-900 font-medium' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800' }}">
                    Completed ({{ $stats['completed'] ?? 0 }})
                </a>
                <a href="{{ route('admin.production.requests.index', ['status' => 'cancelled']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'cancelled' ? 'bg-red-200 text-red-900 font-medium' : 'bg-red-100 hover:bg-red-200 text-red-800' }}">
                    Cancelled ({{ $stats['cancelled'] ?? 0 }})
                </a>
                <a href="{{ route('admin.production.requests.index', ['status' => 'rejected']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'rejected' ? 'bg-purple-200 text-purple-900 font-medium' : 'bg-purple-100 hover:bg-purple-200 text-purple-800' }}">
                    Rejected ({{ $stats['rejected'] ?? 0 }})
                </a>
                @if (request('date_from') || request('date_to'))
                    <span class="px-3 py-1 text-sm bg-amber-100 text-amber-800 rounded-full">
                        <i class="fas fa-calendar mr-1"></i>Request Date Filter Active
                    </span>
                @endif
                @if (request('required_date_from') || request('required_date_to'))
                    <span class="px-3 py-1 text-sm bg-amber-100 text-amber-800 rounded-full">
                        <i class="fas fa-calendar mr-1"></i>Required Date Filter Active
                    </span>
                @endif
                @if (request()->hasAny([
                        'status',
                        'branch_id',
                        'date_from',
                        'date_to',
                        'required_date_from',
                        'required_date_to',
                        'search',
                    ]))
                    <a href="{{ route('admin.production.requests.index') }}"
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
                                Request Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Required Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Progress</th> --}}
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($requests as $request)
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                onclick="window.location='{{ route('admin.production.requests.show', $request) }}'">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-indigo-600">Request #{{ $request->id }}</div>
                                    <div class="text-sm text-gray-500">
                                        Created: {{ $request->request_date->format('d M Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $request->branch->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>{{ $request->items->count() }} items</div>
                                    <div class="text-sm text-gray-500">
                                        Total: {{ number_format($request->getTotalQuantityRequested()) }} units
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $request->required_date->format('d M Y') }}</div>
                                    @if ($request->required_date->isPast())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Overdue
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if ($request->status == 'draft') bg-gray-100 text-gray-800
                                        @elseif($request->status == 'submitted') bg-blue-100 text-blue-800
                                        @elseif($request->status == 'approved') bg-green-100 text-green-800
                                        @elseif($request->status == 'in_production') bg-orange-100 text-orange-800
                                        @elseif($request->status == 'completed') bg-emerald-100 text-emerald-800
                                        @elseif($request->status == 'cancelled') bg-red-100 text-red-800
                                        @elseif($request->status == 'rejected') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                {{-- <td class="px-6 py-4">
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
                                </td> --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.production.requests.show', $request) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if ($request->canBeSubmitted() && $request->created_by_user_id === Auth::id())
                                            <form method="POST"
                                                action="{{ route('admin.production.requests.submit', $request) }}"
                                                class="inline"
                                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to submit this request?');">
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
                                                class="inline"
                                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to approve this request?');">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900"
                                                    title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($request->canBeCancelled())
                                            <form method="POST"
                                                action="{{ route('admin.production.requests.cancel', $request) }}"
                                                class="inline"
                                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to cancel this request?');">
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
                <div class="px-6 py-4 bg-white border-t border-gray-200">
                    {{ $requests->appends(request()->query())->links() }}
                </div>
            @endif
        </div>


    </div>
@endsection
