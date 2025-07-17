@extends('layouts.admin')

@section('header-title', 'Supplier Payments Management')
@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-nav-buttons :items="[
            ['name' => 'Purchase Orders', 'link' => route('admin.purchase-orders.index')],
            ['name' => 'Supplier GRNs', 'link' => route('admin.grn.index')],
            ['name' => 'Supplier Payments', 'link' => '#', 'disabled' => true],
        ]" active="Supplier Payments" />

        {{-- <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-partials.cards.stats-card title="Total Payments" value="Rs. {{ number_format($summary['total_payments']) }}"
                trend="+{{ $summary['increase_percentage'] }}% from last month" icon="fas fa-money-bill-wave" color="blue" />

            <x-partials.cards.stats-card title="Pending Payments"
                value="Rs. {{ number_format($summary['pending_payments']) }}"
                trend="{{ $summary['pending_count'] }} payments pending" icon="fas fa-clock" color="yellow" />

            <x-partials.cards.stats-card title="Overdue Payments"
                value="Rs. {{ number_format($summary['overdue_payments']) }}"
                trend="{{ $summary['overdue_count'] }} overdue payments" icon="fas fa-exclamation-circle" color="red" />

            <x-partials.cards.stats-card title="Active Suppliers" value="{{ $summary['suppliers_count'] }}"
                trend="Managing payments" icon="fas fa-truck" color="green" />
        </div> --}}

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Payment ID, Supplier"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <!-- Supplier Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Suppliers</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}"
                                {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    @if (request()->anyFilled(['search', 'status', 'supplier']))
                        <a href="{{ route('admin.payments.index') }}"
                            class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Supplier Payments</h2>
                    <p class="text-sm text-gray-500">Monitor and manage supplier payments</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.payments.create') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i> New Payment
                    </a>
                    <a href="#"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center" disabled>
                        <i class="fas fa-file-export mr-2"></i> Export
                    </a>
                </div>
            </div>

            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold">Payment Records</h2>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">{{ $payments->total() }} payment records</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Payment ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Payment Method
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <!-- Payment ID -->
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $payment->payment_number }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $payment->purchaseOrder->po_number ?? 'N/A' }}
                                    </div>
                                </td>

                                <!-- Supplier -->
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $payment->supplier->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $payment->supplier->contact_person ?? 'No contact' }}
                                    </div>
                                </td>

                                <!-- Date -->
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $payment->payment_date->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">
                                        Due: {{ $payment->payment_date->addDays(30)->format('M d, Y') }}
                                    </div>
                                </td>

                                <!-- Amount -->
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        Rs. {{ number_format($payment->total_amount) }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Allocated: Rs. {{ number_format($payment->allocated_amount) }}
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    @if ($payment->payment_status === 'paid')
                                        <x-partials.badges.status-badge status="success" text="Paid" />
                                    @elseif($payment->payment_status === 'pending')
                                        <x-partials.badges.status-badge status="warning" text="Pending" />
                                    @elseif($payment->payment_status === 'overdue')
                                        <x-partials.badges.status-badge status="danger" text="Overdue" />
                                    @elseif($payment->payment_status === 'partial')
                                        <x-partials.badges.status-badge status="info" text="Partial" />
                                    @else
                                        <x-partials.badges.status-badge status="default" text="Draft" />
                                    @endif
                                </td>

                                <!-- Payment Method -->
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">
                                        {{ $payment->paymentDetails->first()->method_type ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if ($payment->paymentDetails->first())
                                            Ref: {{ $payment->paymentDetails->first()->reference_number }}
                                        @endif
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.payments.show', $payment->id) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.payments.edit', $payment->id) }}"
                                            class="text-blue-600 hover:text-blue-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.payments.print', $payment->id) }}"
                                            class="text-purple-600 hover:text-purple-800" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        {{-- <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Delete"
                                                onclick="return confirm(' For testing only ')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form> --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No payments found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endsection
