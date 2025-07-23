@extends('layouts.admin')

@section('title', 'Branch Summary')
@section('header-title', 'Branch Summary - ' . $branch->name)
@section('content')
<div class="mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Branch Summary</h1>
        <a href="{{ route('admin.branches.index', ['organization' => $branch->organization_id]) }}"
           class="inline-block bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 transition font-semibold">
            ← Back to Branches
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow p-8 mb-8">
        <h2 class="text-xl font-semibold mb-6 text-indigo-700 flex items-center gap-2">
            <span>{{ $branch->name }}</span>
            <span class="inline-block px-2 py-1 rounded {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-xs">
                {{ $branch->is_active ? 'Active' : 'Inactive' }}
            </span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <ul class="space-y-2 text-gray-700">
                <li><span class="font-semibold">ID:</span> {{ $branch->id }}</li>
                <li><span class="font-semibold">Organization:</span> {{ $branch->organization->name ?? '-' }}</li>
                <li><span class="font-semibold">Phone:</span> {{ $branch->phone }}</li>
                <li><span class="font-semibold">Address:</span> {{ $branch->address }}</li>
            </ul>
            <ul class="space-y-2 text-gray-700">
                @php
                    $isHeadOffice = $branch->id == optional($branch->organization->branches->sortBy('id')->first())->id;
                @endphp
                @if($isHeadOffice)
                    <li><span class="font-semibold">Contact Person:</span> {{ $branch->organization->contact_person ?? '-' }}</li>
                    <li><span class="font-semibold">Designation:</span> {{ $branch->organization->contact_person_designation ?? '-' }}</li>
                    <li><span class="font-semibold">Contact Phone:</span> {{ $branch->organization->contact_person_phone ?? '-' }}</li>
                @else
                    <li><span class="font-semibold">Contact Person:</span> {{ $branch->contact_person ?? '-' }}</li>
                    <li><span class="font-semibold">Designation:</span> {{ $branch->contact_person_designation ?? '-' }}</li>
                    <li><span class="font-semibold">Contact Phone:</span> {{ $branch->contact_person_phone ?? '-' }}</li>
                @endif
            </ul>
        </div>
        <div class="mt-8 flex gap-3">
            @can('update', $branch)
                <a href="{{ route('admin.branches.edit', ['organization' => $branch->organization_id, 'branch' => $branch->id]) }}"
                   class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition font-semibold">
                    Edit Branch
                </a>
            @endcan
            <a href="{{ route('admin.users.create', ['organization' => $branch->organization_id, 'branch' => $branch->id]) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition font-semibold">
                + Create User
            </a>
        </div>
    </div>

    <!-- Activation Key Section -->
    <div class="bg-white rounded-2xl shadow p-8 mb-8">
        <label class="block font-medium mb-1">Activation Key</label>

        {{-- Debug Info --}}
        @if(config('app.debug'))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
            <p class="text-sm text-blue-700">
                <strong>Debug:</strong> Is Super Admin = {{ auth('admin')->user()->isSuperAdmin() ? 'YES' : 'NO' }}
                | Can Regenerate Key = {{ auth('admin')->user()->can('regenerateKey', $branch) ? 'YES' : 'NO' }}
            </p>
        </div>
        @endif

        <div class="flex items-center gap-2">
            <input type="text" id="activation-key" value="{{ $branch->activation_key }}" readonly class="w-full px-3 py-2 border rounded bg-gray-100 text-gray-700" />
            <button type="button" onclick="copyActivationKey()" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Copy</button>
            @can('regenerateKey', $branch)
                <form action="{{ route('admin.branches.regenerate-key', $branch->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <button type="submit"
                            onclick="return confirm('Are you sure you want to regenerate the activation key? This will invalidate the current key.')"
                            class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 ml-2">
                        Regenerate
                    </button>
                </form>
            @else
                {{-- Debug: Show why regenerate button is hidden --}}
                @if(config('app.debug'))
                <span class="text-xs text-red-500 px-2">Regenerate hidden: Not super admin</span>
                @endif
            @endcan
        </div>
    </div>
    <script>
    function copyActivationKey() {
        const input = document.getElementById('activation-key');
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand('copy');
        alert('Activation key copied!');
    }
    </script>

    {{-- Users Section --}}
    <div class="bg-white rounded-2xl shadow p-8 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-indigo-700">Users</h3>
            <a href="{{ route('admin.branch.users.create', ['organization' => $branch->organization_id, 'branch' => $branch->id]) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition font-semibold">
                + Create User
            </a>
        </div>
        @if($branch->users->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($branch->users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $user->getRoleNames()->implode(', ') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-block px-2 py-1 rounded {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-xs">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex gap-2">
                                        @can('view', $user)
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-700">
                                                View
                                            </a>
                                        @endcan
                                        @can('update', $user)
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-700">
                                                Edit
                                            </a>
                                        @endcan
                                        @can('delete', $user)
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700">
                                                    Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm py-4">No users found for this branch.</p>
        @endif
    </div>

    {{-- Branch Statistics Section --}}
    @if(isset($stats))
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-user-shield text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Admins</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['total_admins'] }}</p>
                    <p class="text-xs text-gray-500">{{ $stats['active_admins'] }} active</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-users text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Users</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['total_users'] }}</p>
                    <p class="text-xs text-gray-500">{{ $stats['active_users'] }} active</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100">
                    <i class="fas fa-utensils text-orange-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Kitchen Stations</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['kitchen_stations'] }}</p>
                    <p class="text-xs text-gray-500">{{ $stats['active_kitchen_stations'] }} active</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-table text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tables</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['total_tables'] }}</p>
                    <p class="text-xs text-gray-500">dining capacity</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Branch Admins Section --}}
    @if($branch->admins->count() > 0)
    <div class="bg-white rounded-2xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Branch Administrators</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($branch->admins as $admin)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $admin->name }}</div>
                                <div class="text-sm text-gray-500">{{ $admin->job_title }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $admin->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($admin->roles->count() > 0)
                                    @foreach($admin->roles as $role)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 mr-1">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-sm text-gray-500">No roles assigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $admin->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $admin->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $admin->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Kitchen Stations Section --}}
    @if($branch->kitchenStations->count() > 0)
    <div class="bg-white rounded-2xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Kitchen Stations</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($branch->kitchenStations as $station)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-gray-900">{{ $station->name }}</h4>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $station->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $station->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">{{ $station->description }}</p>
                    <div class="text-xs text-gray-500">
                        <p>Code: {{ $station->code }}</p>
                        <p>Type: {{ ucfirst($station->type) }}</p>
                        <p>Capacity: {{ $station->max_capacity ?? 'N/A' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Available Modules Section --}}
    @if(isset($stats['available_modules']) && count($stats['available_modules']) > 0)
    <div class="bg-white rounded-2xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Available Modules</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($stats['available_modules'] as $module)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-gray-900">{{ $module['name'] }}</h4>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($module['tier'] ?? 'basic') }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">Access level: {{ ucfirst($module['tier'] ?? 'basic') }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
