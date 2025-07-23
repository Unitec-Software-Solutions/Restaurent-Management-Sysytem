@extends('layouts.admin')
@section('header-title', 'Supplier Details - ' . $supplier->name)
@section('content')
    <div class="mx-auto px-4 py-8">
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
                {{-- <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
                    <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400">
                        <div class="text-sm text-blue-600 font-medium">Total Orders</div>
                        <div class="text-2xl font-semibold text-blue-900">{{ $stats['total_orders'] }}</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-400">
                        <div class="text-sm text-green-600 font-medium">Total Purchases</div>
                        <div class="text-2xl font-semibold text-green-900">Rs.
                            {{ number_format($stats['total_purchases'], 2) }}</div>
                    </div>
                    <div class="bg-emerald-50 p-4 rounded-lg border-l-4 border-emerald-400">
                        <div class="text-sm text-emerald-600 font-medium">Total Paid</div>
                        <div class="text-2xl font-semibold text-emerald-900">Rs.
                            {{ number_format($stats['total_paid'], 2) }}</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-400">
                        <div class="text-sm text-red-600 font-medium">Pending Payment</div>
                        <div class="text-2xl font-semibold text-red-900">Rs.
                            {{ number_format($stats['pending_payment'], 2) }}</div>
                    </div>
                </div> --}}
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
                            @if ($supplier->organization)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $supplier->organization->address ?? '' }}
                                    @if ($supplier->organization->phone)
                                        <br>Phone: {{ $supplier->organization->phone }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Supplier Status</div>
                            <div>
                                <span
                                    class="px-2 py-1 text-xs rounded-full
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
                                @if ($supplier->has_vat_registration)
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

                <!-- Recent Activities with Tabs -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden lg:col-span-2" x-data="{ activeTab: 'grns' }">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-8 px-6" aria-label="Tabs">
                            {{-- recent tab GRN button --}}
                            <button @click="activeTab = 'grns'" class="py-4 px-1 border-b-2 font-medium text-sm"
                                :class="activeTab === 'grns' ? 'border-indigo-500 text-indigo-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                Recent GRNs
                            </button>
                            {{-- recent tab PO button --}}
                            {{-- <button @click="activeTab = 'purchaseOrders'" class="py-4 px-1 border-b-2 font-medium text-sm"
                                :class="activeTab === 'purchaseOrders' ? 'border-indigo-500 text-indigo-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                Purchase Orders
                            </button> --}}
                        </nav>
                    </div>
                    <!-- Recent GRNs Tab -->
                    <div x-show="activeTab === 'grns'">
                        <div class="p-6 border-b flex justify-between items-center">
                            <h3 class="text-lg font-semibold">Recent Goods Received Notes</h3>
                            <a href="{{ route('admin.grn.index', $supplier) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View All</a>
                        </div>
                        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                            @forelse($recentGrnTransactions as $grn)
                                <div class="p-4 hover:bg-gray-50 cursor-pointer transition-colors"
                                    onclick="window.location='{{ route('admin.grn.show', $grn->grn_id) }}'">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">GRN #{{ $grn->grn_number }}
                                            </div>
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ $grn->received_date->format('M d, Y') }}
                                                @if ($grn->purchaseOrder)
                                                    | PO: {{ $grn->purchaseOrder->po_number }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                Received by: {{ $grn->receivedByUser->name ?? 'N/A' }}
                                                @if ($grn->verifiedByUser)
                                                    | Verified by: {{ $grn->verifiedByUser->name }}
                                                @endif
                                            </div>
                                            @if ($grn->invoice_number)
                                                <div class="text-xs text-gray-400 mt-1">
                                                    Invoice: {{ $grn->invoice_number }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right ml-4">
                                            <div class="text-sm font-medium text-gray-900">Rs.
                                                {{ number_format($grn->total_amount, 2) }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Items: {{ $grn->items->count() ?? 0 }}
                                            </div>
                                            @if ($grn->paid_amount)
                                                <div class="text-xs text-gray-500">
                                                    Paid: <span class="text-green-600">Rs.
                                                        {{ number_format($grn->paid_amount, 2) }}</span>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Balance: <span class="text-red-600">Rs.
                                                        {{ number_format($grn->total_amount - $grn->paid_amount, 2) }}</span>
                                                </div>
                                            @else
                                                <div class="text-xs text-red-600">
                                                    Unpaid: Rs. {{ number_format($grn->total_amount, 2) }}
                                                </div>
                                            @endif
                                            <div class="mt-2">
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full
                                                {{ $grn->status === 'Verified'
                                                    ? 'bg-green-100 text-green-800'
                                                    : ($grn->status === 'Pending'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : ($grn->status === 'Rejected'
                                                            ? 'bg-red-100 text-red-800'
                                                            : 'bg-gray-100 text-gray-800')) }}">
                                                    {{ $grn->status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-500">
                                    <i class="fas fa-box-open text-3xl mb-2 text-gray-300"></i>
                                    <p>No goods received notes found</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- <!-- Purchase Orders Tab -->
                    <div x-show="activeTab === 'purchaseOrders'">
                        <div class="p-6 border-b flex justify-between items-center">
                            <h3 class="text-lg font-semibold">Recent Purchase Orders</h3>
                            <a href="{{ route('admin.suppliers.purchase-orders', $supplier) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View All</a>
                        </div>
                        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                            @forelse($supplier->purchaseOrders as $po)
                                <div class="p-4 hover:bg-gray-50 cursor-pointer transition-colors"
                                    onclick="window.location='{{ route('admin.purchase-orders.show', $po->po_id) }}'">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">PO #{{ $po->po_number }}</div>
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ $po->order_date->format('M d, Y') }} |
                                                Branch: {{ $po->branch->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                Created by: {{ $po->user->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-right ml-4">
                                            <div class="text-sm font-medium text-gray-900">Rs.
                                                {{ number_format($po->total_amount, 2) }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Paid: <span class="text-green-600">Rs.
                                                    {{ number_format($po->paid_amount, 2) }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Due: <span class="text-red-600">Rs.
                                                    {{ number_format($po->getBalanceAmount(), 2) }}</span>
                                            </div>
                                            <div class="mt-2">
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full
                                                {{ $po->status === 'Completed'
                                                    ? 'bg-green-100 text-green-800'
                                                    : ($po->status === 'Pending'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : ($po->status === 'Approved'
                                                            ? 'bg-blue-100 text-blue-800'
                                                            : 'bg-gray-100 text-gray-800')) }}">
                                                    {{ $po->status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-500">
                                    <i class="fas fa-file-invoice text-3xl mb-2 text-gray-300"></i>
                                    <p>No purchase orders found</p>
                                </div>
                            @endforelse
                        </div>
                    </div> --}}


                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Quick Actions</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="javascript:void(0);"
                            class="flex items-center justify-center px-4 py-3 border cursor-not-allowed border-gray-300 rounded-lg bg-gray-100 text-gray-400 "
                            tabindex="-1" aria-disabled="true">
                            <i class="fas fa-plus-circle mr-2 text-gray-400"></i>
                            <span class="text-sm font-medium">Create Purchase Order</span>
                        </a>
                        <a href="{{ route('admin.grn.create') }}?supplier={{ $supplier->id }}"
                            class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-box mr-2 text-green-500"></i>
                            <span class="text-sm font-medium">Create GRN</span>
                        </a>
                        <a href="javascript:void(0);"
                            class="flex items-center justify-center px-4 py-3 border cursor-not-allowed border-gray-300 rounded-lg bg-gray-100 text-gray-400 "
                            tabindex="-1" aria-disabled="true">
                            <i class="fas fa-credit-card mr-2 text-gray-400"></i>
                            <span class="text-sm font-medium">Make Payment</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Add any additional JavaScript if needed
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize any tooltips or interactive elements
                console.log('Supplier show page loaded');
            });
        </script>
    @endpush
@endsection
