@extends('layouts.admin')

@section('title', 'Branch Details - ' . $branch->name)
@section('header-title', 'Branch Details - ' . $branch->name)
@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $branch->name }}</h1>
            <p class="text-gray-600">{{ $organization->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.branches.index', $organization) }}"
               class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                ← Back to Branches
            </a>
            @can('update', $branch)
                <a href="{{ route('admin.branches.edit', ['organization' => $organization->id, 'branch' => $branch->id]) }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Edit Branch
                </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 p-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- Status Banner -->
    <div class="mb-6">
        @if($branch->is_active)
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span class="font-semibold">Active Branch</span>
                    @if($branch->is_head_office)
                        <span class="ml-2 px-2 py-1 bg-green-200 text-green-800 rounded text-xs">Head Office</span>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-orange-100 border border-orange-300 text-orange-700 px-4 py-3 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span class="font-semibold">Inactive Branch</span>
                    @if($branch->is_head_office)
                        <span class="ml-2 px-2 py-1 bg-orange-200 text-orange-800 rounded text-xs">Head Office</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Admins</p>
                    <p class="text-2xl font-bold">{{ $stats['admins_count'] }}</p>
                    <p class="text-xs text-green-600">{{ $stats['active_admins_count'] }} active</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-utensils text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Kitchen Stations</p>
                    <p class="text-2xl font-bold">{{ $stats['kitchen_stations_count'] }}</p>
                    <p class="text-xs text-green-600">{{ $stats['active_kitchen_stations_count'] ?? 0 }} active</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-shopping-cart text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold">{{ $stats['orders_count'] ?? 0 }}</p>
                    <p class="text-xs text-blue-600">{{ $stats['today_orders_count'] ?? 0 }} today</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-chair text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Capacity</p>
                    <p class="text-2xl font-bold">{{ $branch->total_capacity ?? 0 }}</p>
                    <p class="text-xs text-gray-600">people</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Branch Information -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Branch Information</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-600">Address</label>
                    <p class="text-gray-900">{{ $branch->address }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Phone</label>
                        <p class="text-gray-900">{{ $branch->phone }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Email</label>
                        <p class="text-gray-900">{{ $branch->email ?? '-' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Opening Time</label>
                        <p class="text-gray-900">{{ $branch->opening_time ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Closing Time</label>
                        <p class="text-gray-900">{{ $branch->closing_time ?? '-' }}</p>
                    </div>
                </div>
                @if($branch->contact_person)
                    <div>
                        <label class="text-sm font-medium text-gray-600">Contact Person</label>
                        <p class="text-gray-900">
                            {{ $branch->contact_person }}
                            @if($branch->contact_person_designation)
                                <span class="text-gray-600">({{ $branch->contact_person_designation }})</span>
                            @endif
                        </p>
                        @if($branch->contact_person_phone)
                            <p class="text-sm text-gray-600">{{ $branch->contact_person_phone }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Operational Details -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Operational Details</h3>
            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Reservation Fee</label>
                        <p class="text-gray-900">LKR {{ number_format($branch->reservation_fee ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Cancellation Fee</label>
                        <p class="text-gray-900">LKR {{ number_format($branch->cancellation_fee ?? 0, 2) }}</p>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Created</label>
                    <p class="text-gray-900">{{ $branch->created_at->format('M j, Y \a\t g:i A') }}</p>
                </div>
                @if($branch->activated_at)
                    <div>
                        <label class="text-sm font-medium text-gray-600">Activated</label>
                        <p class="text-gray-900">{{ $branch->activated_at->format('M j, Y \a\t g:i A') }}</p>
                    </div>
                @endif
                <div>
                    <label class="text-sm font-medium text-gray-600">Currently Open</label>
                    <p class="text-gray-900">
                        @if($branch->isCurrentlyOpen())
                            <span class="text-green-600 font-semibold">Yes</span>
                        @else
                            <span class="text-red-600 font-semibold">No</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Admins -->
    @if($branch->admins->count() > 0)
        <div class="mt-6 bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Branch Admins</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($branch->admins as $admin)
                            <tr>
                                <td class="px-4 py-2">{{ $admin->first_name }} {{ $admin->last_name }}</td>
                                <td class="px-4 py-2">{{ $admin->email }}</td>
                                <td class="px-4 py-2">
                                    @forelse($admin->roles as $role)
                                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs mr-1">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-gray-500">No roles assigned</span>
                                    @endforelse
                                </td>
                                <td class="px-4 py-2">
                                    @if($admin->is_active)
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Kitchen Stations -->
    @if($branch->kitchenStations->count() > 0)
        <div class="mt-6 bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Kitchen Stations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($branch->kitchenStations as $station)
                    <div class="border rounded-lg p-4 {{ $station->is_active ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <h4 class="font-semibold">{{ $station->name }}</h4>
                        @if($station->description)
                            <p class="text-sm text-gray-600 mt-1">{{ $station->description }}</p>
                        @endif
                        <div class="mt-2 flex justify-between items-center">
                            <span class="text-xs {{ $station->is_active ? 'text-green-600' : 'text-gray-500' }}">
                                {{ $station->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($station->max_concurrent_orders)
                                <span class="text-xs text-gray-600">Max: {{ $station->max_concurrent_orders }} orders</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
