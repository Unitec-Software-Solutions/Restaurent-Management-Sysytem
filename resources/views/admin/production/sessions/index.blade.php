@extends('layouts.admin')

@section('title', 'Production Sessions')

@section('header-title', 'Production Sessions')

@section('content')
    <div class="container mx-auto px-4 py-8">
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
                ]" active="Production Sessions" />
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Search and Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.production.sessions.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Session</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Enter session name or order number" aria-label="Search Session" autocomplete="off"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled
                        </option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                    </select>
                </div>
                <!-- Date Range -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
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
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('admin.production.sessions.index') }}"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Production Sessions List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Production Sessions</h2>
                    <p class="text-sm text-gray-500">
                        Showing {{ $sessions->firstItem() ?? 0 }} to {{ $sessions->lastItem() ?? 0 }} of
                        {{ $sessions->total() }} sessions
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
                    <a href="{{ route('admin.production.sessions.create') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Production Session
                    </a>
                </div>
            </div>

            <!-- Quick Filter Buttons -->
            <div class="border-b px-6 pt-4 pb-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.production.sessions.index') }}"
                    class="px-3 py-1 text-sm rounded-full {{ !request()->hasAny(['status', 'date_from', 'date_to', 'search']) ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All
                </a>
                <a href="{{ route('admin.production.sessions.index', ['status' => 'scheduled']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'scheduled' ? 'bg-blue-200 text-blue-900 font-medium' : 'bg-blue-100 hover:bg-blue-200 text-blue-800' }}">
                    Scheduled ({{ $stats['scheduled'] ?? 0 }})
                </a>
                <a href="{{ route('admin.production.sessions.index', ['status' => 'in_progress']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'in_progress' ? 'bg-orange-200 text-orange-900 font-medium' : 'bg-orange-100 hover:bg-orange-200 text-orange-800' }}">
                    In Progress ({{ $stats['in_progress'] ?? 0 }})
                </a>
                <a href="{{ route('admin.production.sessions.index', ['status' => 'completed']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'completed' ? 'bg-emerald-200 text-emerald-900 font-medium' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800' }}">
                    Completed ({{ $stats['completed'] ?? 0 }})
                </a>
                <a href="{{ route('admin.production.sessions.index', ['status' => 'cancelled']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('status') == 'cancelled' ? 'bg-red-200 text-red-900 font-medium' : 'bg-red-100 hover:bg-red-200 text-red-800' }}">
                    Cancelled ({{ $stats['cancelled'] ?? 0 }})
                </a>
                @if (request('date_from') || request('date_to'))
                    <span class="px-3 py-1 text-sm bg-amber-100 text-amber-800 rounded-full">
                        <i class="fas fa-calendar mr-1"></i>Date Filter Active
                    </span>
                @endif
                @if (request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                    <a href="{{ route('admin.production.sessions.index') }}"
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
                                Session Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Production Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supervisor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($sessions as $session)
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                onclick="window.location='{{ route('admin.production.sessions.show', $session) }}'">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-indigo-600">{{ $session->session_name }}</div>
                                    <div class="text-sm text-gray-500">
                                        Created: {{ $session->created_at->format('d M Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $session->productionOrder->production_order_number }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $session->productionOrder->items->count() }}
                                        items</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($session->start_time)
                                        <div class="font-medium">{{ $session->start_time->format('d M Y, g:i A') }}</div>
                                        @if ($session->end_time)
                                            <div class="text-sm text-gray-500">
                                                Ended: {{ $session->end_time->format('d M Y, g:i A') }}
                                            </div>
                                            <div class="text-sm text-green-600">
                                                Duration: {{ $session->getFormattedDuration() }}
                                            </div>
                                        @else
                                            <div class="text-sm text-orange-600">In progress</div>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400">Not started</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">
                                        {{ $session->supervisor ? $session->supervisor->name : 'Not assigned' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $session->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.production.sessions.show', $session) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if ($session->canBeStarted())
                                            <form method="POST"
                                                action="{{ route('admin.production.sessions.start', $session) }}"
                                                class="inline"
                                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to start this session?');">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900"
                                                    title="Start Session">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($session->canBeCancelled())
                                            <form method="POST"
                                                action="{{ route('admin.production.sessions.cancel', $session) }}"
                                                class="inline"
                                                onsubmit="event.stopPropagation(); return confirm('Are you sure you want to cancel this session?');">
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
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-tasks text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">No production sessions found</p>
                                    <p class="text-sm">Create a new session to get started</p>
                                    <a href="{{ route('admin.production.sessions.create') }}"
                                        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create Session
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sessions->hasPages())
                <div class="px-6 py-4 bg-white border-t border-gray-200">
                    {{ $sessions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
