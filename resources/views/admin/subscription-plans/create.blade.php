@extends('layouts.admin')
@section('title', 'Add Subscription Plan')
@section('content')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <h1 class="text-2xl font-bold mb-6">Add Subscription Plan</h1>
    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-2 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('admin.subscription-plans.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block mb-1 font-medium">Name</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Modules <span class="text-red-600">*</span>
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($modules as $module)
                    <label>
                        <input type="checkbox" name="modules[]" value="{{ $module->id }}">
                        {{ $module->name }}
                    </label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="block mb-1 font-medium">Price (in cents)</label>
            <input type="number" name="price" step="0.01" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Currency</label>
            <input type="text" name="currency" class="w-full border rounded px-3 py-2" value="LKR" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Description</label>
            <textarea name="description" class="w-full border rounded px-3 py-2"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Is Trial Plan?</label>
            <input type="checkbox" name="is_trial" value="1" {{ old('is_trial') ? 'checked' : '' }}>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Trial Period (days)</label>
            <input type="number" name="trial_period_days" value="{{ old('trial_period_days', 30) }}" min="1" max="365" class="w-full px-4 py-2 border rounded-lg">
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.subscription-plans.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection


