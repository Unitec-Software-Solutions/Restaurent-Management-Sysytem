@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Edit Item</h2>
    <form method="POST" action="{{ route('items.update', $item->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item Name</label>
            <input type="text" name="name" value="{{ $item->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Stock</label>
            <input type="number" name="stock" value="{{ $item->stocks->sum('current_quantity') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
        </div>

        <!-- More editable fields -->

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Update
            </button>
        </div>a
    </form>
</div>
@endsection
