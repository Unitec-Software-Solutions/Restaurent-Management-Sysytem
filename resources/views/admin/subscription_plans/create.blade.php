{{-- resources/views/admin/subscription_plans/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Add Subscription Plan')
@section('content')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <h1 class="text-2xl font-bold mb-6">Add Subscription Plan</h1>
    <form action="{{ route('admin.subscription-plans.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block mb-1 font-medium">Name</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Modules (comma separated)</label>
            <input
                type="text"
                name="modules"
                value="{{ old('modules', isset($subscriptionPlan) ? (is_array($subscriptionPlan->modules) ? implode(',', $subscriptionPlan->modules) : $subscriptionPlan->modules) : '') }}"
                class="w-full border rounded px-3 py-2"
                placeholder="reservations,orders,inventory"
                required
            >
            <small>Enter modules separated by commas.</small>
        </div>
        <div>
            <label class="block mb-1 font-medium">Price (in cents)</label>
            <input type="number" name="price" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Currency</label>
            <input type="text" name="currency" class="w-full border rounded px-3 py-2" value="USD" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Description</label>
            <textarea name="description" class="w-full border rounded px-3 py-2"></textarea>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.subscription-plans.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection


