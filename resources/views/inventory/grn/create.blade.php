@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Goods Received Note</h2>
        <a href="{{ route('inventory.grn.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Back</a>
    </div>

    @if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('inventory.grn.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Branch Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch*</label>
                <select name="branch_id" required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Supplier Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier*</label>
                <select name="supplier_id" required 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Invoice Number -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Number*</label>
                <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Invoice Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Date*</label>
                <input type="date" name="invoice_date" value="{{ old('invoice_date') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="3" 
                          class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <button type="submit" name="action" value="save" 
                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                Save Draft
            </button>
            <button type="submit" name="action" value="save_and_add_items" 
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Save and Add Items
            </button>
        </div>
    </form>
</div>
@endsection