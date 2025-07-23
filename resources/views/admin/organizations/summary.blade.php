@extends('layouts.admin')

@section('title', 'Organization Summary')

@section('content')
<div class="mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Organization Summary</h1>

        <!-- Actions -->
        <div class="flex gap-3">
            @can('activate', $organization)
                <a href="{{ route('admin.organizations.activate.form', $organization) }}"
                   class="inline-block {{ $organization->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg transition font-semibold">
                    <i class="fas {{ $organization->is_active ? 'fa-cog' : 'fa-play' }} mr-2"></i>
                    {{ $organization->is_active ? 'Manage Status' : 'Activate' }}
                </a>
            @endcan
            @can('update', $organization)
                <a href="{{ route('admin.organizations.edit', $organization) }}"
                   class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
            @endcan
        </div>
    </div>

    <a href="{{ route('admin.organizations.index') }}"
       class="inline-block mb-6 bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 transition font-semibold">
        ← Back to Organizations
    </a>

    <!-- Status Alert -->
    @if(!$organization->is_active)
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                <div>
                    <p class="text-red-700 font-medium">This organization is currently inactive.</p>
                    @can('activate', $organization)
                        <p class="text-red-600 text-sm mt-1">
                            You can <a href="{{ route('admin.organizations.activate.form', $organization) }}" class="underline font-medium">activate this organization</a>.
                        </p>
                    @endcan
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <div>
                    <p class="text-green-700 font-medium">This organization is active and operational.</p>
                    @if($organization->activated_at)
                        <p class="text-green-600 text-sm mt-1">
                            Activated on {{ $organization->activated_at->format('M d, Y \a\t H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
        <!-- Organization Info -->
        <div class="bg-white rounded-2xl shadow p-8">
            <h2 class="text-xl font-semibold mb-4 text-indigo-700">Organization Info</h2>
            <ul class="space-y-2 text-gray-700">
                <li><span class="font-semibold">ID:</span> {{ $organization->id }}</li>
                <li><span class="font-semibold">Name:</span> {{ $organization->name }}</li>
                <li><span class="font-semibold">Email:</span> {{ $organization->email ?? '-' }}</li>
                <li><span class="font-semibold">Address:</span> {!! nl2br(e($organization->address)) !!}</li>
                <li><span class="font-semibold">Phone:</span> {{ $organization->phone ?? '-' }}</li>
                <li>
                    <span class="font-semibold">Status:</span>
                    <span class="inline-block px-2 py-1 rounded {{ $organization->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $organization->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </li>
                <li><span class="font-semibold">Created At:</span> {{ $organization->created_at }}</li>
                <li><span class="font-semibold">Updated At:</span> {{ $organization->updated_at }}</li>
                <li><span class="font-semibold">Activated At:</span> {{ $organization->activated_at ?? '-' }}</li>
            </ul>
        </div>
        <!-- Contact Person & Activation Key -->
        <div class="bg-white rounded-2xl shadow p-8">
            <h2 class="text-xl font-semibold mb-4 text-indigo-700">Contact Person</h2>
            <ul class="space-y-2 text-gray-700">
                <li><span class="font-semibold">Name:</span> {{ $organization->contact_person ?? '-' }}</li>
                <li><span class="font-semibold">Designation:</span> {{ $organization->contact_person_designation ?? '-' }}</li>
                <li><span class="font-semibold">Phone:</span> {{ $organization->contact_person_phone ?? '-' }}</li>
            </ul>

            @if(auth('admin')->user()->isSuperAdmin())
            <div class="mt-6">
                <label class="block font-medium mb-1">Activation Key</label>
                <div class="flex items-center gap-2">
                    <input type="password" id="activation-key" value="{{ $organization->activation_key ?? '-' }}" readonly class="w-full px-3 py-2 border rounded bg-gray-100 text-gray-700" />
                    <button type="button" onclick="toggleSummaryKeyVisibility()" class="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600">
                        <i id="summaryKeyIcon" class="fas fa-eye"></i>
                    </button>
                    <button type="button" onclick="copyActivationKey()" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Copy</button>
                    @can('regenerateKey', $organization)
                    <form action="{{ route('admin.organizations.regenerate-key', $organization) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 ml-2"
                                onclick="return confirm('Are you sure you want to regenerate the activation key? This will invalidate the current key.')">
                            Regenerate
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
            @endif
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

    function toggleSummaryKeyVisibility() {
        const input = document.getElementById('activation-key');
        const icon = document.getElementById('summaryKeyIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>

    <!-- Subscription Details -->
    <div class="bg-white rounded-2xl shadow p-8 mb-10">
        <h3 class="text-lg font-semibold mb-4 text-indigo-700">Subscription Details</h3>
        <ul class="space-y-2 text-gray-700">
            <li>
                <span class="font-semibold">Plan:</span>
                {{ optional($organization->plan)->name ?? 'N/A' }}
            </li>
            <li>
                <span class="font-semibold">Plan Price:</span>
                {{ optional($organization->plan) ? number_format($organization->plan->price, 2) . ' ' . $organization->plan->currency : 'N/A' }}
            </li>            <li>
                <span class="font-semibold">Modules:</span>
                <span>
                    @php
                        $plan = $organization->plan;
                        $modulesList = $plan ? $plan->getModulesWithNames() : [];
                    @endphp
                    @if(!empty($modulesList))
                        @foreach($modulesList as $module)
                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold mr-1 mb-1">{{ $module['name'] }}</span>
                        @endforeach
                    @else
                        N/A
                    @endif
                </span>
            </li>
            <li><span class="font-semibold">Created At:</span> {{ $organization->created_at }}</li>
            <li><span class="font-semibold">Updated At:</span> {{ $organization->updated_at }}</li>
            <li><span class="font-semibold">Activation Key Generated At:</span> {{ $organization->created_at }}</li>
            <li><span class="font-semibold">Activated At:</span> {{ $organization->activated_at ?? '-' }}</li>
            <li><span class="font-semibold">Terminating Date:</span>
                {{ $organization->activated_at ? \Carbon\Carbon::parse($organization->activated_at)->addYear()->toDateString() : '-' }}
            </li>
            <li>
                <span class="font-semibold">Trial:</span>
                {{ optional($organization->subscriptions->last())->is_trial ? 'Yes' : 'No' }}
            </li>
        </ul>
    </div>

    <!-- Payment Info: Super Admin Only, modules row removed -->
    @if(auth('admin')->user() && method_exists(auth('admin')->user(), 'isSuperAdmin') && auth('admin')->user()->isSuperAdmin())
    <div class="bg-white rounded-2xl shadow p-8 mb-10">
        <h3 class="text-lg font-semibold mb-4 text-indigo-700">Payment Info</h3>
        <ul class="space-y-2 text-gray-700">
            <li>
                <span class="font-semibold">Plan:</span>
                {{ optional($organization->plan)->name ?? 'N/A' }}
            </li>
            <li>
                <span class="font-semibold">Plan Price:</span>
                {{ optional($organization->plan) ? number_format($organization->plan->price, 2) . ' ' . $organization->plan->currency : 'N/A' }}
            </li>
            <li>
                <span class="font-semibold">Discount:</span>
                {{ $organization->discount_percentage ?? 0 }}%
            </li>
            <li>
                <span class="font-semibold">Final Price:</span>
                @php
                    $discount = (optional($organization->plan) && $organization->discount_percentage)
                        ? ($organization->plan->price * $organization->discount_percentage / 100)
                        : 0;
                    $final = optional($organization->plan) ? $organization->plan->price - $discount : 0;
                @endphp
                {{ optional($organization->plan) ? number_format($final, 2) . ' ' . $organization->plan->currency : 'N/A' }}
            </li>
        </ul>
    </div>
    @endif

    <!-- Branches Table -->
    <div class="bg-white rounded-2xl shadow p-8 mb-10">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-indigo-700">Branches</h3>
            <a href="{{ route('admin.branches.create', ['organization' => $organization->id]) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition font-semibold">
                + Add Branch
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">Branch Name</th>
                        <th class="px-4 py-2 text-left">Phone</th>
                        <th class="px-4 py-2 text-left">Address</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Created</th>
                        <th class="px-4 py-2 text-left">Updated</th>
                        <th class="px-4 py-2 text-left">Activated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organization->branches as $branch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $branch->name }}</td>
                            <td class="px-4 py-2">{{ $branch->phone ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $branch->address ?? '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 rounded {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $branch->created_at }}</td>
                            <td class="px-4 py-2">{{ $branch->updated_at }}</td>
                            <td class="px-4 py-2">{{ $branch->activated_at ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-2 text-center text-gray-500">No branches found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl shadow p-8 mb-10">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-indigo-700">Organization Users</h3>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">Total: {{ $organization->users->count() }} users</span>
                <a href="{{ route('admin.users.create', ['organization' => $organization->id]) }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition font-semibold">
                    + Create User
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spatie Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($organization->users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                            <span class="text-white text-xs font-medium">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->phone_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->roles && $user->roles->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">No roles</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->branch)
                                    <div class="text-sm text-gray-900">{{ $user->branch->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        <span class="{{ $user->branch->is_active ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $user->branch->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">No branch</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <i class="fas fa-users text-gray-300 text-3xl mb-2"></i>
                                    <p class="text-gray-500">No users found in this organization.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
