@extends('layouts.admin')

@section('content')
<div class="p-4 rounded-lg">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">{{ $supplier->name }}</h2>
                    <p class="text-sm text-gray-500">
                        Supplier ID: {{ $supplier->supplier_id }} | 
                        Organization: {{ $supplier->organization->name ?? 'N/A' }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" 
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-edit mr-2"></i> Update Supplier
                    </a>
                    <a href="{{ route('admin.suppliers.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Total Orders</div>
                    <div class="text-2xl font-semibold">{{ $stats['total_orders'] }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Total Purchases</div>
                    <div class="text-2xl font-semibold">Rs. {{ number_format($stats['total_purchases'], 2) }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Total Paid</div>
                    <div class="text-2xl font-semibold">Rs. {{ number_format($stats['total_paid'], 2) }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-500">Pending Payment</div>
                    <div class="text-2xl font-semibold">Rs. {{ number_format($stats['pending_payment'], 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Supplier Details & Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Supplier Details -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden lg:col-span-1">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Supplier Details</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-500">Organization</div>
                        <div class="font-medium">{{ $supplier->organization->name ?? 'N/A' }}</div>
                        @if($supplier->organization)
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $supplier->organization->address ?? '' }}
                                @if($supplier->organization->phone)
                                    <br>Phone: {{ $supplier->organization->phone }}
                                @endif
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Supplier Status</div>
                        <div>
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Contact Person</div>
                        <div>{{ $supplier->contact_person ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Phone</div>
                        <div>{{ $supplier->phone }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Email</div>
                        <div>{{ $supplier->email ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Address</div>
                        <div>{{ $supplier->address ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">VAT Registration</div>
                        <div>
                            @if($supplier->has_vat_registration)
                                <span class="text-green-600">Yes</span>
                                <div class="text-sm text-gray-500 mt-1">
                                    VAT No: {{ $supplier->vat_registration_no }}
                                </div>
                            @else
                                <span class="text-red-600">No</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Created At</div>
                        <div>{{ $supplier->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Last Updated</div>
                        <div>{{ $supplier->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden lg:col-span-2">
                <!-- Purchase Orders Tab -->
                <div x-show="activeTab === 'purchaseOrders'">
                    <div class="p-6 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Recent Purchase Orders</h3>
                        <a href="{{ route('admin.suppliers.purchase-orders', $supplier) }}" 
                           class="text-indigo-600 hover:text-indigo-800">View All</a>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse($supplier->purchaseOrders as $po)
                            <div class="p-4 hover:bg-gray-50 cursor-pointer " onclick="window.location='{{ route('admin.purchase-orders.show', $po->po_id) }}'">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-sm font-medium">PO #{{ $po->po_number }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $po->order_date->format('M d, Y') }} | 
                                            Branch: {{ $po->branch->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium">Rs. {{ number_format($po->total_amount, 2) }}</div>
                                        <div class="text-xs text-gray-500">
                                            Paid: Rs. {{ number_format($po->paid_amount, 2) }} | 
                                            Due: Rs. {{ number_format($po->getBalanceAmount(), 2) }}
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $po->status === 'Completed' ? 'bg-green-100 text-green-800' : 
                                               ($po->status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $po->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-gray-500">
                                No purchase orders found
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Transactions Tab -->
                <div x-show="activeTab === 'transactions'">
                    <div class="p-6 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Recent Transactions</h3>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800">View All</a>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse($supplier->transactions as $transaction)
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-sm font-medium">
                                            {{ $transaction->transaction_type }} #{{ $transaction->transaction_number }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $transaction->created_at->format('M d, Y') }} | 
                                            Payment Method: {{ $transaction->payment_method ?? 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium">
                                            Rs. {{ number_format($transaction->amount, 2) }}
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-gray-500">
                                No transactions found
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('supplierShow', () => ({
            activeTab: 'purchaseOrders'
        }));
    });
</script>
@endpush
@endsection