@extends('layouts.admin')

@section('title', 'Organization Summary')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">Organization Summary</h1>

    {{-- Organization & Contact Details --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-indigo-700">Organization Info</h2>
            <ul class="space-y-2 text-gray-700">
                <li><span class="font-semibold">ID:</span> {{ $organization->id }}</li>
                <li><span class="font-semibold">Name:</span> {{ $organization->name }}</li>
                <li><span class="font-semibold">Email:</span> {{ $organization->email ?? '-' }}</li>
                <li><span class="font-semibold">Address:</span> {{ $organization->address ?? '-' }}</li>
                <li><span class="font-semibold">Phone:</span> {{ $organization->phone ?? '-' }}</li>
                <li><span class="font-semibold">Status:</span>
                    <span class="inline-block px-2 py-1 rounded {{ $organization->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $organization->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </li>
                <li><span class="font-semibold">Created At:</span> {{ $organization->created_at }}</li>
                <li><span class="font-semibold">Updated At:</span> {{ $organization->updated_at }}</li>
                <li><span class="font-semibold">Activated At:</span> {{ $organization->activated_at ?? '-' }}</li>
            </ul>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-indigo-700">Contact Person</h2>
            <ul class="space-y-2 text-gray-700">
                <li><span class="font-semibold">Name:</span> {{ $organization->contact_person ?? '-' }}</li>
                <li><span class="font-semibold">Designation:</span> {{ $organization->contact_person_designation ?? '-' }}</li>
                <li><span class="font-semibold">Phone:</span> {{ $organization->contact_person_phone ?? '-' }}</li>
            </ul>
            <div class="mt-6">
                <label class="block font-medium mb-1">Activation Key</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="activation-key" value="{{ $organization->activation_key ?? '-' }}" readonly class="w-full px-3 py-2 border rounded bg-gray-100 text-gray-700" />
                    <button type="button" onclick="copyActivationKey()" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Copy</button>
                    <form action="{{ route('admin.organizations.regenerate-key', $organization) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 ml-2">Regenerate</button>
                    </form>
                </div>
            </div>
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

    {{-- Subscription Details --}}
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4 text-indigo-700">Subscription Details</h3>
        <ul class="space-y-2 text-gray-700">
            <li><span class="font-semibold">Plan:</span> {{ $organization->plan->name ?? 'N/A' }}</li>
            <li><span class="font-semibold">Modules:</span>
                @if($organization->plan && $organization->plan->modules)
                    {{ is_array($organization->plan->modules) ? implode(', ', $organization->plan->modules) : (is_string($organization->plan->modules) ? implode(', ', json_decode($organization->plan->modules, true) ?? []) : 'N/A') }}
                @else
                    N/A
                @endif
            </li>
            <li><span class="font-semibold">Created At:</span> {{ $organization->created_at }}</li>
            <li><span class="font-semibold">Updated At:</span> {{ $organization->updated_at }}</li>
            <li><span class="font-semibold">Activation Key Generated At:</span> {{ $organization->created_at }}</li>
            <li><span class="font-semibold">Activated At:</span> {{ $organization->activated_at ?? '-' }}</li>
            <li><span class="font-semibold">Renewal (End Date):</span>
                {{ $organization->activated_at ? \Carbon\Carbon::parse($organization->activated_at)->addYear()->toDateString() : '-' }}
            </li>
            <li><span class="font-semibold">Terminating Date:</span>
                {{ $organization->activated_at ? \Carbon\Carbon::parse($organization->activated_at)->addYear()->toDateString() : '-' }}
            </li>
        </ul>
    </div>

    {{-- Branches --}}
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-indigo-700">Branches</h3>
            <a href="{{ route('admin.branches.create', ['organization' => $organization->id]) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                + Add Branch
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">Branch Name</th>
                        <th class="px-4 py-2 text-left">Phone</th>
                        <th class="px-4 py-2 text-left">Address</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Created</th>
                        <th class="px-4 py-2 text-left">Updated</th>
                        <th class="px-4 py-2 text-left">Activated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organization->branches as $branch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $branch->name }}</td>
                            <td class="px-4 py-2">{{ $branch->phone ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $branch->address ?? '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 rounded {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $branch->created_at }}</td>
                            <td class="px-4 py-2">{{ $branch->updated_at }}</td>
                            <td class="px-4 py-2">{{ $branch->activated_at ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-2 text-center text-gray-500">No branches found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection