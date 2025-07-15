@extends('layouts.admin')

@section('title', 'Activate Branch')
@section('header-title', 'Activate Branch')
@section('content')
<div class="max-w-2xl mx-auto mt-10 bg-white p-8 rounded-2xl shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Activate Branch</h2>
        <a href="{{ route('admin.branches.index', ['organization' => $branch->organization_id ?? ($branches[0]->organization_id ?? null)]) }}"
           class="inline-block bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 transition font-semibold">
            ← Back to Branches
        </a>
    </div>
    @if(session('error'))
        <div class="mb-4 bg-red-100 text-red-700 p-3 rounded border border-red-200">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 p-3 rounded border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if(isset($branches) && count($branches))
        @foreach($branches as $branch)
            <div class="mb-8 pb-6 border-b last:border-b-0 last:mb-0 last:pb-0">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-lg text-gray-800">{{ $branch->name }}</span>
                    <span class="text-xs text-gray-500">({{ $branch->organization->name ?? '-' }})</span>
                    @if($branch->is_active)
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                    @else
                        <span class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Inactive</span>
                    @endif
                </div>
                @if(!$branch->is_active)
                    @if(!$branch->organization->is_active)
                        <div class="mb-2 text-red-600 font-semibold">
                            Cannot activate branch. The parent organization is not active.
                        </div>
                    @else
                        <form action="{{ route('admin.branches.activate.submit') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                            <div>
                                <label class="block mb-1 font-medium text-gray-700">
                                    Activation Key <span class="text-red-600">*</span>
                                </label>
                                <input type="text" name="activation_key"
                                       class="w-full border border-gray-300 rounded px-3 py-2 focus:ring focus:ring-blue-200"
                                       required>
                            </div>
                            <button type="submit"
                                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition font-semibold">
                                Activate
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        @endforeach
    @elseif(isset($branch))
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <span class="font-semibold text-lg text-gray-800">{{ $branch->name }}</span>
            <span class="text-xs text-gray-500">({{ $branch->organization->name ?? '-' }})</span>
            @if($branch->is_active)
                <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
            @else
                <span class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Inactive</span>
            @endif
        </div>
        @if(!$branch->is_active)
            @if(!$branch->organization->is_active)
                <div class="mb-2 text-red-600 font-semibold">
                    Cannot activate branch. The parent organization is not active.
                </div>
            @else
                <form action="{{ route('admin.branches.activate.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">
                            Activation Key <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="activation_key"
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:ring focus:ring-blue-200"
                               required>
                    </div>
                    <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition font-semibold">
                        Activate
                    </button>
                </form>
            @endif
        @endif
    @else
        <div class="text-gray-500 text-center py-8">No branches found.</div>
    @endif
</div>
@endsection
