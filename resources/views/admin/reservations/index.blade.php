@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="rounded-lg ">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">Reservation Management</h1>
            <div class="flex gap-3">
                <a href="{{ route('admin.reservations.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Reservation
                </a>
            </div>
        </div>

        <!-- Filters with Export -->
        <x-module-filters 
            :action="route('admin.reservations.index')"
            :export-permission="'export_reservations'"
            :export-filename="'reservations_export.xlsx'">
            
            <!-- Date Range Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $filters['startDate'] }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $filters['endDate'] }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $filters['status'] == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $filters['status'] == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ $filters['status'] == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Branch Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $filters['branchId'] == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Phone Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ $filters['phone'] }}" 
                       placeholder="Search by phone"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
        </x-module-filters>

    <!-- Reservations Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">People</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tables</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Steward</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($reservations as $reservation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            #{{ $reservation->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $reservation->name }}</div>
                            <div class="text-sm text-gray-500">{{ $reservation->branch->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $reservation->phone }}</div>
                            <div class="text-sm text-gray-500">{{ $reservation->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($reservation->date)->format('M d, Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $reservation->start_time }} - {{ $reservation->end_time }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $reservation->number_of_people }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($reservation->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($reservation->tables && $reservation->tables->count())
                                @foreach($reservation->tables as $table)
                                    <span class="inline-block bg-gray-200 rounded px-2 py-1 text-xs mr-1 mb-1">
                                        Table {{ $table->number ?? $table->id }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-gray-400">None</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $reservation->steward->name ?? 'Not assigned' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex flex-col gap-1">
                                <a href="{{ route('admin.reservations.show', $reservation) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <a href="{{ route('admin.reservations.edit', $reservation) }}" 
                                   class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <a href="{{ route('admin.orders.reservations.index', ['reservation_id' => $reservation->id]) }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-utensils mr-1"></i> Orders
                                </a>
                                @routeexists('admin.orders.orders.reservations.create')
                                    <a href="{{ route('admin.orders.orders.reservations.create', ['reservation' => $reservation->id]) }}" 
                                       class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-plus mr-1"></i> Add Order
                                    </a>
                                @else
                                    <span class="text-gray-400">
                                        <i class="fas fa-plus mr-1"></i> Add Order (Unavailable)
                                    </span>
                                @endrouteexists
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <div class="text-gray-400 text-2xl mb-2">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <p class="text-gray-500">No reservations found</p>
                            <p class="text-xs text-gray-400 mt-1">Try adjusting your filters or create a new reservation</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($reservations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $reservations->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection