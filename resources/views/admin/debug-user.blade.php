@extends('layouts.admin')

@section('title', 'User Debug Info')

@section('content')
    <div class="mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">Current User Debug Information</h1>

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Authentication Status</h2>

                @php
                    $admin = auth('admin')->user();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Authenticated (admin guard)</label>
                        <p class="text-sm text-gray-900">{{ auth('admin')->check() ? 'Yes' : 'No' }}</p>
                    </div>

                    @if ($admin)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">User ID</label>
                            <p class="text-sm text-gray-900">{{ $admin->id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <p class="text-sm text-gray-900">{{ $admin->name ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="text-sm text-gray-900">{{ $admin->email ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Is Super Admin</label>
                            <p class="text-sm text-gray-900">{{ $admin->is_super_admin ? 'Yes' : 'No' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Organization ID</label>
                            <p class="text-sm text-gray-900">{{ $admin->organization_id ?? 'NULL' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Branch ID</label>
                            <p class="text-sm text-gray-900">{{ $admin->branch_id ?? 'NULL (HQ User)' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">HQ Access (branch_id is null)</label>
                            <p class="text-sm text-gray-900">
                                {{ $admin->branch_id === null ? 'Yes - Can access manage' : 'No - Branch user' }}</p>
                        </div>
                    @else
                        <div class="col-span-2">
                            <p class="text-red-600">No authenticated admin user found</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Access Test</h2>

                @if ($admin)
                    @if (!$admin->is_super_admin && $admin->branch_id !== null)
                        <div class="bg-red-100 text-red-800 p-4 rounded">
                            <strong>Access Denied:</strong> You are a branch user (branch_id: {{ $admin->branch_id }}).
                            HQ access is required to manage production requests.
                        </div>
                    @else
                        <div class="bg-green-100 text-green-800 p-4 rounded">
                            <strong>Access Granted:</strong> You have HQ access and can manage production requests.
                            <br>
                            @if ($admin->is_super_admin)
                                Reason: Super Admin
                            @else
                                Reason: Organization Admin (no branch_id)
                            @endif
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('admin.production.requests.manage') }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                Go to Manage Production Requests
                            </a>
                        </div>
                    @endif
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.dashboard') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
@endsection
