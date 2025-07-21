@extends('layouts.app')
@section('content')
    <div class="mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h1 class="text-2xl font-bold mb-4">All Orders</h1>
            <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="phone" value="{{ request('phone') }}" placeholder="Phone"
                    class="form-input rounded border-gray-300" />
                <select name="status" class="form-select rounded border-gray-300">
                    <option value="">All Status</option>
                    <option value="draft" @if (request('status') == 'draft') selected @endif>Draft</option>
                    <option value="active" @if (request('status') == 'active') selected @endif>Active</option>
                    <option value="submitted" @if (request('status') == 'submitted') selected @endif>Submitted</option>
                    <option value="completed" @if (request('status') == 'completed') selected @endif>Completed</option>
                    <option value="cancelled" @if (request('status') == 'cancelled') selected @endif>Cancelled</option>
                </select>
                <select name="branch_id" class="form-select rounded border-gray-300">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @if (request('branch_id') == $branch->id) selected @endif>
                            {{ $branch->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="form-input rounded border-gray-300" />
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="form-input rounded border-gray-300" />
                <button type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 col-span-1 md:col-span-5">Filter</button>
            </form>
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Order #</th>
                            <th class="px-4 py-2 text-left">Customer</th>
                            <th class="px-4 py-2 text-left">Phone</th>
                            <th class="px-4 py-2 text-left">Branch</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Total</th>
                            <th class="px-4 py-2 text-left">Created</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $order->id }}</td>
                                <td class="px-4 py-3">{{ $order->customer_name }}</td>
                                <td class="px-4 py-3">{{ $order->customer_phone }}</td>
                                <td class="px-4 py-3">{{ $order->branch->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ ucfirst($order->status) }}</td>
                                <td class="px-4 py-3">LKR {{ number_format($order->total, 2) }}</td>
                                <td class="px-4 py-3">{{ $order->created_at->format('M j, Y H:i') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('orders.summary', $order->id) }}"
                                        class="text-blue-500 hover:text-blue-700" title="View Order">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
