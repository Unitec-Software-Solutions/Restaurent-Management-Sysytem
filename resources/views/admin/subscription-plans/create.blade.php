@extends('layouts.admin')
@section('title', 'Add Subscription Plan')
@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-extrabold mb-8 text-gray-900 tracking-tight">Add Subscription Plan</h1>
    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 border border-red-200 shadow">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li class="mb-1">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('admin.subscription-plans.store') }}" method="POST" class="space-y-7">
        @csrf
        <div>
            <label class="block mb-2 font-semibold text-gray-700" for="name">Plan Name <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200" required>
        </div>
        <div>
            <label class="block mb-2 font-semibold text-gray-700">Modules <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach($modules as $module)
                    <label class="flex items-center space-x-2 bg-gray-50 px-2 py-1 rounded-lg cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox" name="modules[]" value="{{ $module->id }}" class="accent-blue-600">
                        <span>{{ $module->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-2 font-semibold text-gray-700" for="price">Price <span class="text-gray-500 text-xs">(e.g. 1999.99)</span></label>
                <input type="number" id="price" name="price" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200" required>
            </div>
            <div>
                <label class="block mb-2 font-semibold text-gray-700" for="currency">Currency</label>
                <input type="text" id="currency" name="currency" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200" value="LKR" required>
            </div>
        </div>
        <div>
            <label class="block mb-2 font-semibold text-gray-700" for="description">Description</label>
            <textarea id="description" name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200"></textarea>
        </div>
        <div class="flex flex-col md:flex-row md:items-center md:space-x-6 space-y-3 md:space-y-0">
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="is_trial" value="1" {{ old('is_trial') ? 'checked' : '' }} class="accent-blue-600">
                <span class="text-gray-700">Is Trial Plan?</span>
            </label>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="trial_period_days">Trial Period (days)</label>
                <input type="number" id="trial_period_days" name="trial_period_days" value="{{ old('trial_period_days', 30) }}" min="1" max="365" class="w-28 border border-gray-300 rounded-lg px-2 py-1 focus:ring focus:ring-blue-200">
            </div>
        </div>
        <div class="flex justify-end space-x-3 pt-6">
            <a href="{{ route('admin.subscription-plans.index') }}" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-semibold">Cancel</a>
            <button type="submit" class="bg-blue-600 text-white px-7 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">Save Plan</button>
        </div>
    </form>
</div>
@endsection


