@extends('layouts.admin')

@section('header-title', 'View Supplier Payment')

@section('content')
    <div class="p-4 rounded-lg">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.payments.index') }}" class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Payments
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Supplier Payment Details</h1>

            @if (session('success'))
                <div class="bg-green-50 text-green-700 p-3 rounded-lg mb-6 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-6 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Payment Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Number</label>
                    <input type="text" value="{{ $payment->payment_number }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                    <input type="text" value="{{ $payment->payment_date->format('Y-m-d') }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount</label>
                    <input type="text" value="${{ number_format($payment->total_amount, 2) }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Allocated Amount</label>
                    <input type="text" value="${{ number_format($payment->allocated_amount, 2) }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <span class="inline-block px-3 py-1 text-sm font-medium rounded-full {{ $payment->payment_status == 'paid' ? 'bg-green-100 text-green-800' : ($payment->payment_status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucwords($payment->payment_status) }}
                    </span>
                </div>
            </div>

            <!-- Form for Editing (Optional) -->
            <form method="POST" action="{{ route('admin.payments.update', $payment->id) }}">
                @csrf
                @method('POST')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Branch Selection -->
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        <select id="branch_id" name="branch_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $payment->branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} ({{ $branch->code ?? $branch->id }})
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Supplier Selection -->
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                        <select id="supplier_id" name="supplier_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $payment->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Date -->
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                        <input type="date" id="payment_date" name="payment_date" required
                            value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('payment_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label for="method_type" class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                        <select id="method_type" name="method_type" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Method</option>
                            <option value="cash" {{ old('method_type', $payment->paymentDetails->method_type ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank_transfer" {{ old('method_type', $payment->paymentDetails->method_type ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="check" {{ old('method_type', $payment->paymentDetails->method_type ?? '') == 'check' ? 'selected' : '' }}>Check</option>
                            <option value="credit_card" {{ old('method_type', $payment->paymentDetails->method_type ?? '') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        </select>
                        @error('method_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Total Amount -->
                    <div>
                        <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-1">Total Amount *</label>
                        <input type="number" step="0.01" min="0" id="total_amount" name="total_amount" required
                            value="{{ old('total_amount', $payment->total_amount) }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('total_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Reference Number -->
                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number"
                            value="{{ old('reference_number', $payment->paymentDetails->reference_number ?? '') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('reference_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Allocated Documents -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Allocated Documents</h2>
                    @if($payment->grns->isEmpty())
                        <p class="text-sm text-gray-500">No documents allocated to this payment.</p>
                    @else
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN No.</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated Amount</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($payment->grns as $grn)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $grn->grn_number }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${{ number_format($grn->total_amount, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${{ number_format($grn->pivot->amount, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $grn->received_date }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $payment->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.payments.index') }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center">
                        <i class="fas fa-save mr-2"></i> Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection