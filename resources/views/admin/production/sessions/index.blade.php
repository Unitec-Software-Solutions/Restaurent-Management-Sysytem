@extends('layouts.admin')

@section('title', 'Production Sessions')
@section('header-title', 'Production Sessions')

@section('content')
    <div class="p-4 space-y-8">
        <!-- Navigation Buttons -->
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
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Production Sessions</h1>
                <p class="text-gray-600 mt-1">Manage kitchen production sessions and track progress</p>
            </div>
            <a href="{{ route('admin.production.sessions.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow transition duration-200 flex items-center gap-2">
                <i class="fas fa-plus"></i>
                New Session
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="">All Statuses</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled
                        </option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress
                        </option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div class="flex items-end">
                    <div class="flex gap-3 w-full">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.production.sessions.index') }}"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200 text-center">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sessions Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Session</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Production Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supervisor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sessions as $session)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $session->session_name }}</div>
                                    <div class="text-sm text-gray-500">
                                        Created: {{ $session->created_at->format('M d, Y g:i A') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $session->productionOrder->production_order_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $session->productionOrder->items->count() }}
                                        items</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if ($session->start_time)
                                            Started: {{ $session->start_time->format('M d, g:i A') }}
                                        @else
                                            Not started
                                        @endif
                                    </div>
                                    @if ($session->end_time)
                                        <div class="text-sm text-gray-500">
                                            Ended: {{ $session->end_time->format('M d, g:i A') }}
                                        </div>
                                        <div class="text-sm text-green-600">
                                            Duration: {{ $session->getFormattedDuration() }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $session->supervisor ? $session->supervisor->name : 'Not assigned' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $session->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('admin.production.sessions.show', $session) }}"
                                            class="text-blue-600 hover:text-blue-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if ($session->canBeStarted())
                                            <form method="POST"
                                                action="{{ route('admin.production.sessions.start', $session) }}"
                                                class="inline">
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
                                                onsubmit="return confirm('Are you sure you want to cancel this session?')">
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
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sessions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
