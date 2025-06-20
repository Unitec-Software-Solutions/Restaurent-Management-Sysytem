@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Debug Info Card for Admin Reservations --}}
    @if(config('app.debug'))
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-medium text-indigo-800">🔍 Admin Reservations Debug Info</h3>
                <a href="{{ route('admin.reservations.index', array_merge(request()->all(), ['debug' => 1])) }}" 
                   class="text-xs text-indigo-600 hover:text-indigo-800">
                    Full Debug (@dd)
                </a>
            </div>
            <div class="text-xs text-indigo-700 mt-2 grid grid-cols-3 gap-4">
                <div>
                    <p><strong>Reservations Variable:</strong> {{ isset($reservations) ? 'Set (' . $reservations->count() . ')' : 'NOT SET' }}</p>
                    <p><strong>Phone Search:</strong> {{ request('phone') ?? 'None' }}</p>
                </div>
                <div>
                    <p><strong>DB Total Reservations:</strong> {{ \App\Models\Reservation::count() }}</p>
                    <p><strong>Today's Reservations:</strong> {{ \App\Models\Reservation::whereDate('reservation_date', today())->count() }}</p>
                </div>
                <div>
                    <p><strong>Admin:</strong> {{ auth('admin')->check() ? 'Authenticated' : 'NOT AUTH' }}</p>
                    <p><strong>Organization:</strong> {{ auth('admin')->user()->organization->name ?? 'None' }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Reservations Management</h1>
        <a href="{{ route('admin.reservations.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Add Reservation
        </a>
    </div>

    <form method="GET" action="{{ route('admin.reservations.index') }}" class="mb-6 flex items-center gap-4">
        <input type="text" name="phone" value="{{ request('phone') }}" placeholder="Search by phone number" class="border rounded px-4 py-2 w-64" required>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
        @if(request('phone'))
            <a href="{{ route('admin.reservations.index') }}" class="text-gray-600 ml-2">Clear</a>
        @endif
    </form>

    <!-- Reservations Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tables</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($reservations as $reservation)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $reservation->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $reservation->date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $reservation->start_time }} - {{ $reservation->end_time }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($reservation->status) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($reservation->tables && $reservation->tables->count())
                                @foreach($reservation->tables as $table)
                                    <span class="inline-block bg-gray-200 rounded px-2 py-1 text-xs mr-1 mb-1">Table {{ $table->number }}</span>
                                @endforeach
                            @else
                                <span class="text-gray-400">None</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex flex-col gap-1">
                            @if($reservation)
                                <a href="{{ route('admin.reservations.show', $reservation) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            @else
                                <span class="text-gray-400">No Reservation</span>
                            @endif
                            <a href="{{ route('admin.reservations.edit', $reservation) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                            <a href="{{ route('admin.orders.reservations.index', ['reservation_id' => $reservation->id]) }}" class="text-green-600 hover:text-green-900">Reservation Orders</a>
                            <a href="{{ route('admin.orders.reservations.create', ['reservation' => $reservation->id]) }}" class="text-purple-600 hover:text-purple-900">Add Order</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No reservations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection