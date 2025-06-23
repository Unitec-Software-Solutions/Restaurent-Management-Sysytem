@extends('layouts.admin')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Menu Management</h1>
                <p class="text-gray-600">Manage restaurant menus and scheduling</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.menus.calendar') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-calendar mr-2"></i> Calendar View
                </a>
                <a href="{{ route('admin.menus.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Create Menu
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Active Menus</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalActiveMenus }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-list text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Menus</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalMenus }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <i class="fas fa-calendar-day text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Today's Activations</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $todayActivations }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-clock text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Upcoming Menus</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $upcomingMenus->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Menus Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Current Active Menus -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Currently Active</h3>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    {{ $activeMenus->count() }} Active
                </span>
            </div>

            @if($activeMenus->count() > 0)
                <div class="space-y-3">
                    @foreach($activeMenus as $menu)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $menu->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $menu->branch->name ?? 'All Branches' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $menu->type === 'breakfast' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $menu->type === 'lunch' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $menu->type === 'dinner' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $menu->type === 'all_day' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $menu->type === 'special' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $menu->type)) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <span>{{ $menu->menuItems->count() }} items</span>
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.menus.show', $menu) }}" class="text-indigo-600 hover:text-indigo-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.menus.edit', $menu) }}" class="text-gray-600 hover:text-gray-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-3">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <p class="text-gray-500">No active menus</p>
                    <p class="text-sm text-gray-400">Create a menu to get started</p>
                </div>
            @endif
        </div>

        <!-- Upcoming Menus -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Upcoming Menus</h3>
                <a href="{{ route('admin.menus.list') }}" class="text-indigo-600 hover:text-indigo-700 text-sm">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            @if($upcomingMenus->count() > 0)
                <div class="space-y-3">
                    @foreach($upcomingMenus as $menu)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $menu->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $menu->branch->name ?? 'All Branches' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $menu->type)) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-blue-600">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ \Carbon\Carbon::parse($menu->valid_from)->format('M j, Y') }}
                                </span>
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.menus.show', $menu) }}" class="text-indigo-600 hover:text-indigo-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.menus.edit', $menu) }}" class="text-gray-600 hover:text-gray-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-3">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <p class="text-gray-500">No upcoming menus</p>
                    <p class="text-sm text-gray-400">Schedule menus for future dates</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.menus.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <i class="fas fa-plus text-indigo-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">Create Menu</p>
                    <p class="text-sm text-gray-500">Add a new menu</p>
                </div>
            </a>

            <a href="{{ route('admin.menus.bulk.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-copy text-purple-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">Bulk Create</p>
                    <p class="text-sm text-gray-500">Create multiple menus</p>
                </div>
            </a>

            <a href="{{ route('admin.menus.calendar') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-calendar text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">Calendar View</p>
                    <p class="text-sm text-gray-500">Schedule overview</p>
                </div>
            </a>

            <a href="{{ route('admin.menus.list') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-list text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">All Menus</p>
                    <p class="text-sm text-gray-500">Browse all menus</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
