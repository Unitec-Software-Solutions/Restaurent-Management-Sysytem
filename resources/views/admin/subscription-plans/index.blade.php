{{-- resources/views/admin/subscription_plans/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Subscription Plans')
@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Subscription Plans</h1>
    <a href="{{ route('admin.subscription-plans.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">+ Add Plan</a>
    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 p-3 rounded">{{ session('success') }}</div>
    @endif
    @php
        $allModules = \App\Models\Module::pluck('name', 'id')->toArray();
    @endphp
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Currency</th>
                <th>Modules</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $plan)
                <tr>
                    <td>{{ $plan->name }}</td>
                    <td>{{ number_format($plan->price/100, 2) }}</td>
                    <td>{{ $plan->currency }}</td>
                    <td>
                        @foreach(json_decode($plan->modules, true) as $moduleId)
                            <span class="inline-block bg-gray-100 px-2 py-1 rounded text-xs">
                                {{ $allModules[$moduleId] ?? $moduleId }}
                            </span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('admin.subscription-plans.show', $plan->id) }}" class="text-green-600 hover:underline">View</a>
                        <a href="{{ route('admin.subscription-plans.edit', $plan->id) }}" class="text-blue-600 hover:underline ml-2">Edit</a>
                        <form action="{{ route('admin.subscription-plans.destroy', $plan->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this plan?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-2">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection