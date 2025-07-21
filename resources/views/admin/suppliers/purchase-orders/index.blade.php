@extends('layouts.admin')

@section('header-title', 'Purchase Orders Management')
@section('content')
    <div class="mx-auto px-4 py-8">
        <x-nav-buttons :items="[
            ['name' => 'Suppliers Management', 'link' => route('admin.suppliers.index')],
            ['name' => 'Purchase Orders', 'link' => route('admin.purchase-orders.index')],
            ['name' => 'Supplier GRNs', 'link' => route('admin.grn.index')],
            ['name' => 'Supplier Payments', 'link' => '#', 'disabled' => true],
        ]" active="Purchase Orders" />

        <!-- Filters with Export -->
        <x-module-filters
            :action="route('admin.purchase-orders.index')"
            :export-permission="'export_purchase_orders'"
            :export-filename="'purchase_orders_export.xlsx'">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <select name="supplier" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </x-module-filters>

        <!-- PO Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Purchase Orders</h2>
                    <p class="text-sm text-gray-500">Manage and track all purchase orders</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="#"
                        class="bg-indigo-600 hover:bg-indigo-700 opacity-50 cursor-not-allowed text-white px-4 py-2 rounded-lg flex items-center pointer-events-none">
                        <i class="fas fa-file-export mr-2"></i> Export
                    </a>
                    {{-- <a href="{{ route('admin.purchase-orders.create') }}" --}}
                    <a href="{{ route('admin.purchase-orders.create') }}"
                        class="bg-green-600 hover:bg-green-700  opacity-50 cursor-not-allowed text-white px-4 py-2 rounded-lg flex items-center pointer-events-none justify-center">
                        <i class="fas fa-plus mr-2"></i> New PO
                    </a>
                </div>
            </div>

            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold">PO Records</h2>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">{{ $purchaseOrders->total() }} records found</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                PO Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Branch
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dates
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($purchaseOrders as $po)
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                onclick="window.location='{{ route('admin.purchase-orders.show', $po->po_id) }}'">
                                <!-- PO Number -->
                                <td class="px-6 py-4">
                                    <div class="font-medium text-indigo-600">{{ $po->po_number }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $po->items->count() }} items
                                    </div>
                                </td>

                                <!-- Supplier -->
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $po->supplier->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $po->supplier->contact_person ?? 'No contact' }}
                                    </div>
                                </td>

                                <!-- Branch -->
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $po->branch->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $po->branch->code }}
                                    </div>
                                </td>

                                <!-- Dates -->
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $po->order_date->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">
                                        Due: {{ $po->expected_delivery_date->format('M d, Y') }}
                                    </div>
                                </td>

                                <!-- Amount -->
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        Rs. {{ number_format($po->total_amount, 2) }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Paid: Rs. {{ number_format($po->paid_amount, 2) }}
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    @if ($po->status === 'Pending')
                                        <x-partials.badges.status-badge status="warning" text="Pending" />
                                    @elseif($po->status === 'Approved')
                                        <x-partials.badges.status-badge status="info" text="Approved" />
                                    @elseif($po->status === 'Received')
                                        <x-partials.badges.status-badge status="success" text="Received" />
                                    @else
                                        <x-partials.badges.status-badge status="default" text="{{ $po->status }}" />
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.purchase-orders.show', $po->po_id) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- <a href="#"
                                            class="text-blue-600 hover:text-blue-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a> --}}
                                        <a href="{{ route('admin.purchase-orders.print', $po->po_id) }}"
                                            class="text-purple-600 hover:text-purple-800" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No purchase orders found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>
@endsection
