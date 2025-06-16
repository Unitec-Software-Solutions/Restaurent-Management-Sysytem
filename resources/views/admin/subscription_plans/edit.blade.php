{{-- resources/views/admin/subscription_plans/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Edit Subscription Plan')
@section('content')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <h1 class="text-2xl font-bold mb-6">Edit Subscription Plan</h1>
    <form action="{{ route('admin.subscription-plans.update', $subscriptionPlan->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block mb-1 font-medium">Name</label>
            <input type="text" name="name" value="{{ old('name', $subscriptionPlan->name) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Modules (comma separated)</label>
            <input type="text" name="modules"
                value="{{ old('modules', is_array($subscriptionPlan->modules) ? implode(',', $subscriptionPlan->modules) : implode(',', json_decode($subscriptionPlan->modules, true) ?? [])) }}"
                class="w-full border rounded px-3 py-2" required>
            <small>Enter modules separated by commas.</small>
        </div>
        <div>
            <label class="block mb-1 font-medium">Price (in cents)</label>
            <input type="number" name="price" value="{{ old('price', $subscriptionPlan->price) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Currency</label>
            <input type="text" name="currency" value="{{ old('currency', $subscriptionPlan->currency) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Description</label>
            <textarea name="description" class="w-full border rounded px-3 py-2">{{ old('description', $subscriptionPlan->description) }}</textarea>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
            <a href="{{ route('admin.subscription-plans.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection