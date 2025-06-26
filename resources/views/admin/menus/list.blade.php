@extends('layouts.admin')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">All Menus</h1>
                <p class="text-gray-600">Browse and manage all restaurant menus</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.menus.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
                <a href="{{ route('admin.menus.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Create Menu
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.menus.list') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="breakfast" {{ request('type') === 'breakfast' ? 'selected' : '' }}>Breakfast</option>
                    <option value="lunch" {{ request('type') === 'lunch' ? 'selected' : '' }}>Lunch</option>
                    <option value="dinner" {{ request('type') === 'dinner' ? 'selected' : '' }}>Dinner</option>
                    <option value="all_day" {{ request('type') === 'all_day' ? 'selected' : '' }}>All Day</option>
                    <option value="special" {{ request('type') === 'special' ? 'selected' : '' }}>Special</option>
                    <option value="seasonal" {{ request('type') === 'seasonal' ? 'selected' : '' }}>Seasonal</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('admin.menus.list') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Menus Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        @forelse($menus as $menu)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Menu Header -->
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $menu->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $menu->branch->name ?? 'All Branches' }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <!-- Status Badge -->
                            @if($menu->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @elseif($menu->valid_from > now())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Upcoming
                                </span>
                            @elseif($menu->valid_until && $menu->valid_until < now())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Expired
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif

                            <!-- Type Badge -->
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $menu->type === 'breakfast' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $menu->type === 'lunch' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $menu->type === 'dinner' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $menu->type === 'all_day' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $menu->type === 'special' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $menu->type === 'seasonal' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $menu->type)) }}
                            </span>
                        </div>
                    </div>

                    <!-- Menu Description -->
                    @if($menu->description)
                        <p class="text-sm text-gray-600 mb-4">{{ Str::limit($menu->description, 100) }}</p>
                    @endif

                    <!-- Menu Details -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-calendar-alt w-4"></i>
                            <span class="ml-2">
                                @if($menu->valid_from)
                                    {{ \Carbon\Carbon::parse($menu->valid_from)->format('M j, Y') }}
                                    @if($menu->valid_until)
                                        - {{ \Carbon\Carbon::parse($menu->valid_until)->format('M j, Y') }}
                                    @endif
                                @else
                                    No dates set
                                @endif
                            </span>
                        </div>

                        @if($menu->start_time && $menu->end_time)
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-clock w-4"></i>
                                <span class="ml-2">
                                    {{ \Carbon\Carbon::parse($menu->start_time)->format('g:i A') }} - 
                                    {{ \Carbon\Carbon::parse($menu->end_time)->format('g:i A') }}
                                </span>
                            </div>
                        @endif

                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-utensils w-4"></i>
                            <span class="ml-2">{{ $menu->menuItems->count() }} items</span>
                        </div>

                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-calendar-week w-4"></i>
                            <span class="ml-2">
                                @if($menu->available_days && is_array($menu->available_days))
                                    {{ implode(', ', array_map('ucfirst', $menu->available_days)) }}
                                @else
                                    All days
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Menu Actions -->
                <div class="border-t border-gray-200 p-4 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.menus.show', $menu) }}" class="text-indigo-600 hover:text-indigo-700">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.menus.edit', $menu) }}" class="text-gray-600 hover:text-gray-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('admin.menus.preview', $menu) }}" class="text-blue-600 hover:text-blue-700" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>

                        <div class="flex gap-2">
                            @if($menu->is_active)
                                <button type="button" onclick="deactivateMenu({{ $menu->id }})" 
                                        class="px-3 py-1 text-xs font-medium text-red-600 border border-red-300 rounded hover:bg-red-50">
                                    Deactivate
                                </button>
                            @else
                                <button type="button" onclick="activateMenu({{ $menu->id }})" 
                                        class="px-3 py-1 text-xs font-medium text-green-600 border border-green-300 rounded hover:bg-green-50">
                                    Activate
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="col-span-full text-center py-12">
                <div class="text-gray-400 text-5xl mb-4">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No menus found</h3>
                <p class="text-gray-500 max-w-md mx-auto mb-6">
                    @if(request()->hasAny(['status', 'type', 'branch_id', 'date_from', 'date_to']))
                        No menus match your current filters. Try adjusting your search criteria.
                    @else
                        Get started by creating your first menu.
                    @endif
                </p>
                <div class="flex justify-center gap-3">
                    @if(request()->hasAny(['status', 'type', 'branch_id', 'date_from', 'date_to']))
                        <a href="{{ route('admin.menus.list') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                            Clear Filters
                        </a>
                    @endif
                    <a href="{{ route('admin.menus.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i> Create Menu
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($menus->hasPages())
        <div class="bg-white rounded-lg shadow-sm p-6">
            {{ $menus->withQueryString()->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
function activateMenu(menuId) {
    if (confirm('Are you sure you want to activate this menu?')) {
        fetch(`/menus/${menuId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If not JSON, it might be a redirect (authentication issue)
                if (response.status === 302 || response.status === 401 || response.status === 419) {
                    throw new Error('Authentication required. Please refresh the page and try again.');
                }
                throw new Error(`Server error: ${response.status}`);
            }
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to activate menu');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Failed to activate menu');
        });
    }
}

function deactivateMenu(menuId) {
    if (confirm('Are you sure you want to deactivate this menu?')) {
        fetch(`/menus/${menuId}/deactivate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If not JSON, it might be a redirect (authentication issue)
                if (response.status === 302 || response.status === 401 || response.status === 419) {
                    throw new Error('Authentication required. Please refresh the page and try again.');
                }
                throw new Error(`Server error: ${response.status}`);
            }
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to deactivate menu');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Failed to deactivate menu');
        });
    }
}
</script>
@endpush
@endsection
