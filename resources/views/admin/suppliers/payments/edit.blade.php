@extends('layouts.admin')

@section('header-title', 'Edit Supplier Payment')

@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Back and Header -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.payments.show', $payment->id) }}"
                class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Payment
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Edit Payment #{{ $payment->payment_number }}</h1>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.payments.update', $payment->id) }}" method="POST"
            class="bg-white rounded-xl shadow-sm p-6">
            @csrf
            @method('PUT')

            <!-- Payment Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Supplier -->
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" id="supplier_id"
                        class="w-full p-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}"
                                {{ $payment->supplier_id == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Branch -->
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id" id="branch_id"
                        class="w-full p-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $payment->branch_id == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} ({{ $branch->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Date -->
                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date"
                        value="{{ $payment->payment_date instanceof \Carbon\Carbon ? $payment->payment_date->format('Y-m-d') : \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}"
                        class="w-full p-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('payment_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Total Amount -->
                <div>
                    <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-1">Total Amount
                        (Rs.)</label>
                    <input type="number" name="total_amount" id="total_amount" step="0.01"
                        value="{{ $payment->total_amount }}"
                        class="w-full p-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('total_amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Status -->
                <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                    <select name="payment_status" id="payment_status"
                        class="w-full p-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="draft" {{ $payment->payment_status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ $payment->payment_status == 'pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="partial" {{ $payment->payment_status == 'partial' ? 'selected' : '' }}>Partial
                        </option>
                        <option value="paid" {{ $payment->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                    @error('payment_status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Method Type -->
                <div>
                    <label for="method_type" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="method_type" id="method_type"
                        class="w-full p-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="cash" {{ $payment->paymentDetails->method_type == 'cash' ? 'selected' : '' }}>Cash
                        </option>
                        <option value="bank_transfer"
                            {{ $payment->paymentDetails->method_type == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer
                        </option>
                        <option value="check" {{ $payment->paymentDetails->method_type == 'check' ? 'selected' : '' }}>
                            Check</option>
                        <option value="credit_card"
                            {{ $payment->paymentDetails->method_type == 'credit_card' ? 'selected' : '' }}>Credit Card
                        </option>
                    </select>
                    @error('method_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reference Number -->
                <div>
                    <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference
                        Number</label>
                    <input type="text" name="reference_number" id="reference_number"
                        value="{{ $payment->paymentDetails->reference_number ?? '' }}"
                        class="w-full p-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('reference_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Value Date -->
                <div>
                    <label for="value_date" class="block text-sm font-medium text-gray-700 mb-1">Value Date</label>
                    <input type="date" name="value_date" id="value_date"
                        value="{{ $payment->paymentDetails->value_date ? \Carbon\Carbon::parse($payment->paymentDetails->value_date)->format('Y-m-d') : '' }}"
                        class="w-full p-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('value_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="4"
                    class="w-full p-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">{{ $payment->notes }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.payments.show', $payment->id) }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                    Update Payment
                </button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
        }
    </style>
@endpush
