@extends('layouts.admin')

@section('title', 'Activate Organization')

@section('content')
<div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Activate Organization</h2>
    @if(session('error'))
        <div class="mb-4 text-red-600">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif

    @if(isset($organizations) && count($organizations))
        @foreach($organizations as $organization)
            <div class="mb-6 border-b pb-4">
                <div class="mb-2 font-semibold">
                    {{ $organization->name }}
                    <span class="text-xs text-gray-500 ml-2">({{ $organization->email }})</span>
                    @if($organization->is_active)
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                    @else
                        <span class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Inactive</span>
                    @endif
                </div>
                @if(!$organization->is_active)
                <form action="{{ route('admin.organizations.activate.submit') }}" method="POST" class="space-y-2">
                    @csrf
                    <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                    <div>
                        <label class="block mb-1 font-medium">Activation Key</label>
                        <input type="text" name="activation_key" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Activate</button>
                </form>
                @endif
            </div>
        @endforeach
    @else
        <div class="text-gray-500">No organizations found.</div>
    @endif
</div>
@endsection