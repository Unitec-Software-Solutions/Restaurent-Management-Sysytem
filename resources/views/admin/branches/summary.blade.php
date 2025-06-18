@extends('layouts.admin')

@section('title', 'Branch Summary')

@section('content')
<div class="container mx-auto px-4 py-8">
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
        </div>
    </div>

    <!-- Activation Key Section -->
    <div class="bg-white rounded-2xl shadow p-8 mb-8">
        <label class="block font-medium mb-1">Activation Key</label>
        <div class="flex items-center gap-2">
            <input type="text" id="activation-key" value="{{ $branch->activation_key }}" readonly class="w-full px-3 py-2 border rounded bg-gray-100 text-gray-700" />
            <button type="button" onclick="copyActivationKey()" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Copy</button>
            @can('update', $branch)
                <form action="{{ route('branches.regenerate-key', $branch->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 ml-2">Regenerate</button>
                </form>
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
</div>
@endsection