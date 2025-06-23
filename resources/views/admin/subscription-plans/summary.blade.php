@extends('layouts.admin')
@section('title', 'Subscription Plan Details')
@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-extrabold mb-8 text-gray-900 tracking-tight">Subscription Plan Details</h1>
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <ul class="mb-8 space-y-4">
            <li>
                <span class="block text-xs text-gray-500 uppercase font-semibold">Name</span>
                <span class="text-lg font-bold text-gray-800">{{ $subscriptionPlan->name }}</span>
            </li>            <li>
                <span class="block text-xs text-gray-500 uppercase font-semibold">Modules</span>
                @php
                    $allModules = \App\Models\Module::pluck('name', 'id')->toArray();
                    $modules = $subscriptionPlan->getModulesArray();
                @endphp
                <div class="flex flex-wrap gap-2 mt-1">
                    @forelse($modules as $moduleData)
                        @php
                            $moduleName = is_array($moduleData) ? ($moduleData['name'] ?? $moduleData) : $moduleData;
                            $moduleId = is_numeric($moduleName) ? $moduleName : null;
                            $displayName = $moduleId ? ($allModules[$moduleId] ?? $moduleId) : $moduleName;
                        @endphp
                        <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                            {{ $displayName }}
                        </span>
                    @empty
                        <span class="text-gray-500 text-sm">No modules assigned</span>
                    @endforelse
                </div>
            </li>
            <li>
                <span class="block text-xs text-gray-500 uppercase font-semibold">Price</span>
                <span class="text-lg text-gray-700">{{ number_format($subscriptionPlan->price, 2) }} {{ $subscriptionPlan->currency }}</span>
            </li>
            <li>
                <span class="block text-xs text-gray-500 uppercase font-semibold">Description</span>
                <span class="text-gray-700">{{ $subscriptionPlan->description ?: '-' }}</span>
            </li>
            <li class="flex gap-8">
                <div>
                    <span class="block text-xs text-gray-500 uppercase font-semibold">Created At</span>
                    <span class="text-gray-700">{{ $subscriptionPlan->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 uppercase font-semibold">Updated At</span>
                    <span class="text-gray-700">{{ $subscriptionPlan->updated_at->format('Y-m-d H:i') }}</span>
                </div>
            </li>
        </ul>
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.subscription-plans.edit', $subscriptionPlan->id) }}"
               class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                Edit
            </a>
            <a href="{{ route('admin.subscription-plans.index') }}"
               class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-semibold">
                Back
            </a>
        </div>
    </div>
</div>
@endsection