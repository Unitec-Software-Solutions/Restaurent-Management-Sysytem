@extends('layouts.admin')

@section('title', 'Activate Branch')

@section('content')
<div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Activate Branch</h2>
    @if(session('error'))
        <div class="mb-4 text-red-600">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif

    @if(isset($branches) && count($branches))
        @foreach($branches as $branch)
            <div class="mb-6 border-b pb-4">
                <div class="mb-2 font-semibold">
                    {{ $branch->name }}
                    <span class="text-xs text-gray-500 ml-2">({{ $branch->organization->name ?? '' }})</span>
                    @if($branch->is_active)
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                    @else
                        <span class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Inactive</span>
                    @endif
                </div>
                @if(!$branch->is_active)
                <form action="{{ route('admin.branches.activate.submit') }}" method="POST" class="space-y-2">
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                    <div>
                        <label class="block mb-1 font-medium">Activation Key</label>
                        <input type="text" name="activation_key" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Activate</button>
                </form>
                @endif
            </div>
        @endforeach
    @elseif(isset($branch))
        {{-- For branch admins: only their branch --}}
        <div class="mb-2 font-semibold">{{ $branch->name }} ({{ $branch->organization->name ?? '' }})</div>
        @if(!$branch->is_active)
        <form action="{{ route('admin.branches.activate.submit') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
            <div>
                <label class="block mb-1 font-medium">Activation Key</label>
                <input type="text" name="activation_key" class="w-full border rounded px-3 py-2" required>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Activate</button>
        </form>
        @else
            <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
        @endif
    @else
        <div class="text-gray-500">No branches found.</div>
    @endif
</div>
@endsection