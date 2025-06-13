@extends('layouts.admin')

@section('title', 'Organization Summary')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Organization Summary</h1>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">{{ $organization->name }}</h2>
        <ul class="mb-4">
            <li><strong>ID:</strong> {{ $organization->id }}</li>
            <li><strong>Name:</strong> {{ $organization->name }}</li>
            <li><strong>Email:</strong> {{ $organization->email }}</li>
            <li><strong>Address:</strong> {{ $organization->address }}</li>
            <li><strong>Phone:</strong> {{ $organization->phone }}</li>
            <li><strong>Status:</strong> {{ $organization->is_active ? 'Active' : 'Inactive' }}</li>
        </ul>
        <a href="{{ route('admin.branches.create', ['organization' => $organization->id]) }}"
           class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
            + Add Branch
        </a>
    </div>

    {{-- Show Activation Key --}}
    <div class="mb-4">
        <label class="block font-medium mb-1">Activation Key</label>
        <div class="flex items-center gap-2">
            <input type="text" id="activation-key" value="{{ $organization->activation_key }}" readonly class="w-full px-3 py-2 border rounded bg-gray-100 text-gray-700" />
            <button type="button" onclick="copyActivationKey()" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Copy</button>
            <form action="{{ route('admin.organizations.regenerate-key', $organization) }}" method="POST" class="inline">
                @csrf
                @method('PUT')
                <button type="submit" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 ml-2">Regenerate</button>
            </form>
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

    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Branches for {{ $organization->name }}</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Branch Name</th>
                    <th class="px-4 py-2 text-left">Phone</th>
                    <th class="px-4 py-2 text-left">Address</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organization->branches as $branch)
                    <tr>
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2">{{ $branch->name }}</td>
                        <td class="px-4 py-2">{{ $branch->phone }}</td>
                        <td class="px-4 py-2">{{ $branch->address }}</td>
                        <td class="px-4 py-2">
                            @if($branch->is_active)
                                <span class="text-green-600 font-semibold">Active</span>
                            @else
                                <span class="text-red-600 font-semibold">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 flex gap-2">
                            <a href="{{ route('admin.branches.edit', ['organization' => $organization->id, 'branch' => $branch->id]) }}" class="text-blue-600 hover:underline">Edit</a>
                            <a href="{{ route('admin.branches.index', ['organization' => $organization->id]) }}#branch-{{ $branch->id }}" class="text-green-600 hover:underline">View</a>
                            <form action="{{ route('admin.branches.destroy', ['organization' => $organization->id, 'branch' => $branch->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this branch?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-2 text-center text-gray-500">No branches found for this organization.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection