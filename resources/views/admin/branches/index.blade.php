@extends('layouts.admin')

@section('title', 'Branches')
@section('header-title', 'Branches')

@section('content')
<div class="mx-auto px-4 py-8">

    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Branches</h1>
                <p class="text-gray-600 mt-1">Manage branches and their details</p>
            </div>
           @if(isset($organization))
                <a href="{{ route('admin.branches.create', ['organization' => $organization->id]) }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Create Branch
                </a>
            @endif
        </div>
    </div>


    {{-- <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Branches</h1>
        @can('create', App\Models\Branch::class)
            @if(isset($organization))
                <a href="{{ route('admin.branches.create', ['organization' => $organization->id]) }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    + Add Branch
                </a>
            @endif
        @endcan
    </div> --}}

    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 p-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @if(isset($organization))
                    @forelse($organization->branches as $branch)
                        <tr>
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 font-semibold">{{ $branch->name }}</td>
                            <td class="px-4 py-2">{{ $organization->name }}</td>
                            <td class="px-4 py-2">{{ $branch->phone }}</td>
                            <td class="px-4 py-2">{{ $branch->address }}</td>
                            <td class="px-4 py-2">
                                @if($branch->is_active)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 flex gap-2">
                                @can('update', $branch)
                                    <a href="{{ route('admin.branches.edit', ['organization' => $organization->id, 'branch' => $branch->id]) }}"
                                       class="text-blue-600 hover:underline">Edit</a>
                                @endcan
                                <a href="{{ route('admin.branches.summary', $branch->id) }}" class="text-green-600 hover:underline">View</a>
                                @can('delete', $branch)
                                    <form action="{{ route('admin.branches.destroy', ['organization' => $organization->id, 'branch' => $branch->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this inactive branch? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                @else
                                    @if($branch->is_active)
                                        <button type="button" disabled
                                                title="Cannot delete active branch. Please deactivate it first."
                                                class="text-gray-400 cursor-not-allowed">Delete</button>
                                    @else
                                        <button type="button" disabled
                                                title="Only super administrators can delete inactive branches."
                                                class="text-gray-400 cursor-not-allowed">Delete</button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-center text-gray-500">No branches found.</td>
                        </tr>
                    @endforelse
                @elseif(isset($branches))
                    @forelse($branches as $branch)
                        <tr>
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 font-semibold">{{ $branch->name }}</td>
                            <td class="px-4 py-2">{{ $branch->organization->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $branch->phone }}</td>
                            <td class="px-4 py-2">{{ $branch->address }}</td>
                            <td class="px-4 py-2">
                                @if($branch->is_active)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 flex gap-2">
                                @can('update', $branch)
                                    <a href="{{ route('admin.branches.edit', ['organization' => $branch->organization_id ?? $organization->id, 'branch' => $branch->id]) }}"
                                       class="text-blue-600 hover:underline">Edit</a>
                                @endcan
                                <a href="{{ route('admin.branches.summary', $branch->id) }}" class="text-green-600 hover:underline">View</a>
                                @can('delete', $branch)
                                    <form action="{{ route('admin.branches.destroy', ['organization' => $branch->organization_id ?? $organization->id, 'branch' => $branch->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this inactive branch? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                @else
                                    @if($branch->is_active)
                                        <button type="button" disabled
                                                title="Cannot delete active branch. Please deactivate it first."
                                                class="text-gray-400 cursor-not-allowed">Delete</button>
                                    @else
                                        <button type="button" disabled
                                                title="Only super administrators can delete inactive branches."
                                                class="text-gray-400 cursor-not-allowed">Delete</button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-center text-gray-500">No branches found.</td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
