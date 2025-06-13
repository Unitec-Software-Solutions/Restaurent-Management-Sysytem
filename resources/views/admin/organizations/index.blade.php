{{-- filepath: resources/views/admin/organizations/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Organizations')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Organizations</h1>
        <a href="{{ route('admin.organizations.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Add Organization
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($organizations as $org)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2">{{ $org->name }}</td>
                        <td class="px-4 py-2">{{ $org->email ?? '-' }}</td>
                        <td class="px-4 py-2">
                            @if($org->is_active)
                                <span class="text-green-600 font-semibold">Active</span>
                            @else
                                <span class="text-red-600 font-semibold">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 flex gap-2">
                            <a href="{{ route('admin.organizations.edit', $org) }}" class="text-blue-600 hover:underline">Edit</a>
                            <a href="{{ route('admin.organizations.summary', $org) }}" class="text-green-600 hover:underline">View</a>
                            <form action="{{ route('admin.organizations.destroy', $org) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No organizations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection