@extends('layouts.admin')

@section('title', 'Branch Summary')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Branch Summary</h1>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">{{ $branch->name }}</h2>
        <ul class="mb-4">
            <li><strong>ID:</strong> {{ $branch->id }}</li>
            <li><strong>Name:</strong> {{ $branch->name }}</li>
            <li><strong>Organization:</strong> {{ $branch->organization->name ?? '-' }}</li>
            <li><strong>Phone:</strong> {{ $branch->phone }}</li>
            <li><strong>Address:</strong> {{ $branch->address }}</li>
            <li><strong>Status:</strong> {{ $branch->is_active ? 'Active' : 'Inactive' }}</li>
        </ul>
        @can('update', $branch)
            <a href="{{ route('admin.branches.edit', ['organization' => $branch->organization_id, 'branch' => $branch->id]) }}"
               class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                Edit Branch
            </a>
        @endcan
    </div>

    {{-- Show Activation Key --}}
    <div class="mb-4">
        <label class="block font-medium mb-1">Activation Key</label>
        <div class="flex items-center gap-2">
            <input type="text" id="activation-key" value="{{ $branch->activation_key }}" readonly class="w-full px-3 py-2 border rounded bg-gray-100 text-gray-700" />
            <button type="button" onclick="copyActivationKey()" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Copy</button>
            @can('update', $branch)
                <form action="{{ route('branches.regenerate-key', $branch->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 ml-2">Regenerate</button>
                </form>
            @endcan
        </div>
    </div>
    <script>
    function copyActivationKey() {
        const input = document.getElementById('activation-key');
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand('copy');
        alert('Activation key copied!');
    }
    </script>

    {{-- Branch Subscription Details --}}
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Branch Subscription Details</h3>
        @if($branch->subscriptions->count())
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Plan</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Start</th>
                        <th class="px-4 py-2">End</th>
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Currency</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branch->subscriptions as $subscription)
                        <tr>
                            <td class="px-4 py-2">{{ $subscription->plan_id }}</td>
                            <td class="px-4 py-2">{{ ucfirst($subscription->status) }}</td>
                            <td class="px-4 py-2">{{ $subscription->start_date }}</td>
                            <td class="px-4 py-2">{{ $subscription->end_date }}</td>
                            <td class="px-4 py-2">{{ number_format($subscription->amount / 100, 2) }}</td>
                            <td class="px-4 py-2">{{ $subscription->currency }}</td>
                            <td class="px-4 py-2">
                                @if(auth('admin')->user()->isSuperAdmin())
                                    <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                @else
                                    <span class="text-gray-400">Read Only</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-gray-500">No subscriptions found.</div>
        @endif
    </div>
</div>
@endsection