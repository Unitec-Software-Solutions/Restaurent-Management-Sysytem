@extends('layouts.admin')

@section('title', 'Organizations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Organizations</h1>
        <a href="{{ route('admin.organizations.create') }}"
           class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition font-semibold">
            + Add Organization
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full table-auto divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Contact Person</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Designation</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Contact Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($organizations as $org)
                    <tr class="hover:bg-blue-50 transition">
                        <td class="px-4 py-3">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $org->name }}</td>
                        <td class="px-4 py-3">{{ $org->email ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $org->contact_person ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $org->contact_person_designation ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $org->contact_person_phone ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-1 rounded {{ $org->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $org->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2 flex-wrap">
                                <a href="{{ route('admin.organizations.summary', $org) }}"
                                   class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded hover:bg-green-200 transition text-xs font-semibold">
                                    View
                                </a>
                                <a href="{{ route('admin.organizations.edit', $org) }}"
                                   class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded hover:bg-blue-200 transition text-xs font-semibold">
                                    Edit
                                </a>
                                @if(auth('admin')->user()->isSuperAdmin())
                                    <a href="{{ route('admin.organizations.activate.form', $org) }}"
                                       class="inline-block {{ $org->is_active ? 'bg-orange-100 text-orange-800 hover:bg-orange-200' : 'bg-purple-100 text-purple-800 hover:bg-purple-200' }} px-3 py-1 rounded transition text-xs font-semibold">
                                        {{ $org->is_active ? 'Manage' : 'Activate' }}
                                    </a>
                                    
                                    {{-- Only super admins can delete inactive organizations --}}
                                    @can('delete', $org)
                                        <form action="{{ route('admin.organizations.destroy', $org) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this inactive organization? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 transition text-xs font-semibold">
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        @if(!$org->is_active)
                                            <button type="button" disabled
                                                    title="Only super administrators can delete inactive organizations."
                                                    class="inline-block bg-gray-100 text-gray-500 px-3 py-1 rounded cursor-not-allowed text-xs font-semibold">
                                                Delete
                                            </button>
                                        @else
                                            <button type="button" disabled
                                                    title="Cannot delete active organization. Please deactivate it first."
                                                    class="inline-block bg-gray-100 text-gray-500 px-3 py-1 rounded cursor-not-allowed text-xs font-semibold">
                                                Delete
                                            </button>
                                        @endif
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No organizations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection