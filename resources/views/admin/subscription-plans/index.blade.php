{{-- resources/views/admin/subscription-plans/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Subscription Plans')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Subscription Plans</h1>
        <a href="{{ route('admin.subscription-plans.create') }}"
           class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition font-semibold">
            + Add Plan
        </a>
    </div>
    @if(session('success'))
        <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
            {{ session('success') }}
        </div>
    @endif
    @php
        $allModules = \App\Models\Module::pluck('name', 'id')->toArray();
    @endphp
    <div class="overflow-x-auto rounded-lg shadow">
        <table class="min-w-full bg-white divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Currency</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Modules</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($plans as $plan)
                    <tr class="hover:bg-blue-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $plan->name }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ number_format($plan->price, 2) }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $plan->currency }}</td>                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $modules = $plan->getModulesArray();
                                @endphp
                                @forelse($modules as $moduleData)
                                    @php
                                        $moduleName = is_array($moduleData) ? ($moduleData['name'] ?? $moduleData) : $moduleData;
                                        $moduleId = is_numeric($moduleName) ? $moduleName : null;
                                    @endphp
                                    <span class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs font-semibold">
                                        {{ $moduleId ? ($allModules[$moduleId] ?? $moduleId) : $moduleName }}
                                    </span>
                                @empty
                                    <span class="text-gray-500 text-sm">No modules</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.subscription-plans.show', $plan->id) }}"
                                   class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded hover:bg-green-200 transition text-xs font-semibold">
                                    View
                                </a>
                                <a href="{{ route('admin.subscription-plans.edit', $plan->id) }}"
                                   class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded hover:bg-blue-200 transition text-xs font-semibold">
                                    Edit
                                </a>
                                <form action="{{ route('admin.subscription-plans.destroy', $plan->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this plan?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 transition text-xs font-semibold">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No subscription plans found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection