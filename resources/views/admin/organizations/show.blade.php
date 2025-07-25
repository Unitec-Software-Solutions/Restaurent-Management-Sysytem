@extends('layouts.admin')

@section('title', 'Organization Details - ' . $organization->name)
@section('header-title', 'Organization Details - ' . $organization->name)
@section('content')

    <div class="container-fluid py-8 p-4">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $organization->name }}</h1>
                <p class="text-gray-600 mt-1">Organization Management & Overview</p>
            </div>
            <div class="flex gap-3">
                @can('activate', $organization)
                    <a href="{{ route('admin.organizations.activate.form', $organization) }}"
                       class="inline-block {{ $organization->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg transition font-semibold flex items-center">
                        <i class="fas {{ $organization->is_active ? 'fa-cog' : 'fa-play' }} mr-2"></i>
                        {{ $organization->is_active ? 'Manage Status' : 'Activate' }}
                    </a>
                @endcan
                @can('update', $organization)
                    <a href="{{ route('admin.organizations.edit', $organization) }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Organization
                    </a>
                @endcan
                <a href="{{ route('admin.organizations.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Organization Status & Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full {{ $organization->is_active ? 'bg-green-100' : 'bg-red-100' }}">
                    <i class="fas fa-building {{ $organization->is_active ? 'text-green-600' : 'text-red-600' }}"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Organization Status</p>
                    <p class="text-lg font-semibold {{ $organization->is_active ? 'text-green-600' : 'text-red-600' }}">
                        {{ $organization->is_active ? 'Active' : 'Inactive' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-store-alt text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Branches</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['total_branches'] }}</p>
                    <p class="text-xs text-gray-500">{{ $stats['active_branches'] }} active</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-users text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Admins</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['total_admins'] }}</p>
                    <p class="text-xs text-gray-500">{{ $stats['active_admins'] }} active</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100">
                    <i class="fas fa-utensils text-orange-600"></i>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">Kitchen Stations</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['kitchen_stations'] }}</p>
                    <div class="text-xs text-gray-500 mt-1">
                        @php
                            $stationTypes = [];
                            foreach($organization->branches as $branch) {
                                foreach($branch->kitchenStations as $station) {
                                    $stationTypes[$station->type] = ($stationTypes[$station->type] ?? 0) + 1;
                                }
                            }
                        @endphp
                        @if(count($stationTypes) > 0)
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($stationTypes as $type => $count)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $type == 'cooking' ? 'bg-red-100 text-red-700' :
                                           ($type == 'prep' ? 'bg-yellow-100 text-yellow-700' :
                                           ($type == 'service' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700')) }}">
                                        {{ $count }} {{ ucfirst($type) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span>across all branches</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('overview')" id="tab-overview" class="tab-button active py-4 px-1 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600">
                    Overview
                </button>
                <button onclick="showTab('subscription')" id="tab-subscription" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Subscription
                </button>
                <button onclick="showTab('branches')" id="tab-branches" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Branches
                </button>
                <button onclick="showTab('users')" id="tab-users" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Users
                </button>
                <button onclick="showTab('admins')" id="tab-admins" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Admins
                </button>
                <button onclick="showTab('kitchen-stations')" id="tab-kitchen-stations" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Kitchen Stations
                </button>
                <button onclick="showTab('modules')" id="tab-modules" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Modules
                </button>
                @if(auth('admin')->user()->isSuperAdmin() || (auth('admin')->user()?->admin_type === 'organization' && auth('admin')->user()?->organization?->id == $organization->id))
                <button onclick="showTab('activation')" id="tab-activation" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Activation
                </button>
                @endif
            </nav>
        </div>

        <div class="p-6">
            <!-- Overview Tab -->
            <div id="content-overview" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Organization Name</dt>
                                <dd class="text-sm text-gray-900">{{ $organization->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="text-sm text-gray-900">{{ $organization->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="text-sm text-gray-900">{{ $organization->phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="text-sm text-gray-900">{{ $organization->address }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                                <dd class="text-sm text-gray-900">{{ $organization->contact_person }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Designation</dt>
                                <dd class="text-sm text-gray-900">{{ $organization->contact_person_designation }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Contact Phone</dt>
                                <dd class="text-sm text-gray-900">{{ $organization->contact_person_phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="text-sm text-gray-900">{{ $organization->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Head Office Details</h3>
                        @if($stats['head_office'])
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Branch Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $stats['head_office']->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="text-sm">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $stats['head_office']->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $stats['head_office']->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Capacity</dt>
                                    <dd class="text-sm text-gray-900">{{ $stats['head_office']->total_capacity ?? 'Not set' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Operating Hours</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $stats['head_office']->opening_time }} - {{ $stats['head_office']->closing_time }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Kitchen Stations</dt>
                                    <dd class="text-sm text-gray-900">{{ $stats['head_office']->kitchen_stations_count ?? 0 }}</dd>
                                </div>
                                @if($stats['head_office']->kitchenStations && $stats['head_office']->kitchenStations->count() > 0)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Station Types</dt>
                                    <dd class="text-sm text-gray-900">
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($stats['head_office']->kitchenStations->take(3) as $station)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    {{ $station->type == 'cooking' ? 'bg-red-100 text-red-800' :
                                                       ($station->type == 'prep' ? 'bg-yellow-100 text-yellow-800' :
                                                       ($station->type == 'service' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                                    <i class="fas fa-fire mr-1"></i>
                                                    {{ $station->name }}
                                                </span>
                                            @endforeach
                                            @if($stats['head_office']->kitchenStations->count() > 3)
                                                <span class="text-xs text-gray-500 px-2 py-1">+{{ $stats['head_office']->kitchenStations->count() - 3 }} more</span>
                                            @endif
                                        </div>
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        @else
                            <p class="text-gray-500">No head office found</p>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Login Details</h3>
                        <div class="space-y-4">
                            @php
                                $orgAdmin = $organization->admins->where('branch_id', null)->first();
                                $branchAdmin = $organization->admins->where('branch_id', '!=', null)->first();
                            @endphp

                            @if($orgAdmin)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-blue-800 mb-2 flex items-center">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    Organization Administrator
                                </h4>
                                <dl class="space-y-2 text-sm">
                                    <div>
                                        <dt class="font-medium text-gray-600">Name:</dt>
                                        <dd class="text-gray-900">{{ $orgAdmin->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Email:</dt>
                                        <dd class="text-gray-900">{{ $orgAdmin->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Password:</dt>
                                        <dd class="text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded inline-flex items-center">
                                            <span id="org-password">{{ config('auto_system_settings.default_org_admin_password', 'AdminPassword123!') }}</span>
                                            <button onclick="copyToClipboard('org-password')" class="ml-2 text-gray-500 hover:text-gray-700">
                                                <i class="fas fa-copy text-xs"></i>
                                            </button>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Job Title:</dt>
                                        <dd class="text-gray-900">{{ $orgAdmin->job_title ?? 'Organization Administrator' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Phone:</dt>
                                        <dd class="text-gray-900">{{ $orgAdmin->phone ?? 'Not provided' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Roles:</dt>
                                        <dd class="text-gray-900">
                                            @if($orgAdmin->roles->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($orgAdmin->roles as $role)
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                            {{ $role->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-500">No roles assigned</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Created:</dt>
                                        <dd class="text-gray-900">{{ $orgAdmin->created_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Status:</dt>
                                        <dd>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $orgAdmin->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $orgAdmin->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            @endif

                            @if($branchAdmin)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-green-800 mb-2 flex items-center">
                                    <i class="fas fa-user-cog mr-2"></i>
                                    Branch Administrator
                                </h4>
                                <dl class="space-y-2 text-sm">
                                    <div>
                                        <dt class="font-medium text-gray-600">Name:</dt>
                                        <dd class="text-gray-900">{{ $branchAdmin->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Email:</dt>
                                        <dd class="text-gray-900">{{ $branchAdmin->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Password:</dt>
                                        <dd class="text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded inline-flex items-center">
                                            <span id="branch-password">{{ config('auto_system_settings.default_branch_admin_password', 'BranchAdmin123!') }}</span>
                                            <button onclick="copyToClipboard('branch-password')" class="ml-2 text-gray-500 hover:text-gray-700">
                                                <i class="fas fa-copy text-xs"></i>
                                            </button>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Job Title:</dt>
                                        <dd class="text-gray-900">{{ $branchAdmin->job_title ?? 'Branch Administrator' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Phone:</dt>
                                        <dd class="text-gray-900">{{ $branchAdmin->phone ?? 'Not provided' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Branch:</dt>
                                        <dd class="text-gray-900">
                                            <span class="inline-flex items-center">
                                                {{ $branchAdmin->branch->name ?? 'Unknown' }}
                                                @if($branchAdmin->branch && $branchAdmin->branch->is_head_office)
                                                    <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Head Office
                                                    </span>
                                                @endif
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Roles:</dt>
                                        <dd class="text-gray-900">
                                            @if($branchAdmin->roles->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($branchAdmin->roles as $role)
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                            {{ $role->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-500">No roles assigned</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Created:</dt>
                                        <dd class="text-gray-900">{{ $branchAdmin->created_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-600">Status:</dt>
                                        <dd>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $branchAdmin->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $branchAdmin->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            @endif

                            @if(!$orgAdmin && !$branchAdmin)
                            <p class="text-gray-500 text-center">No admin accounts found</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Tab -->
            <div id="content-subscription" class="tab-content hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Subscription</h3>
                        @if($organization->subscriptionPlan)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-semibold text-gray-900">{{ $organization->subscriptionPlan->name }}</h4>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $stats['subscription_status']['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $stats['subscription_status']['is_active'] ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Price</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ number_format($organization->subscriptionPlan->price, 2) }} {{ $organization->subscriptionPlan->currency }}
                                            @if($organization->subscriptionPlan->is_trial)
                                                <span class="text-xs text-blue-600">(Trial)</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Max Branches</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->subscriptionPlan->max_branches ?? 'Unlimited' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Max Employees</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->subscriptionPlan->max_employees ?? 'Unlimited' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->subscriptionPlan->description ?? 'No description' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        @else
                            <p class="text-gray-500">No subscription plan assigned</p>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Plan Features</h3>
                        @if($organization->subscriptionPlan && $organization->subscriptionPlan->features)
                            <div class="space-y-2">
                                @foreach($organization->subscriptionPlan->features as $feature)
                                    <div class="flex items-center">
                                        <i class="fas fa-check text-green-500 mr-2"></i>
                                        <span class="text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $feature)) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No features defined</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Users Tab -->
            <div id="content-users" class="tab-content hidden">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Organization Users</h3>
                        <p class="text-sm text-gray-500 mt-1">Total: {{ $stats['total_users'] }} users</p>
                    </div>
                    <a href="{{ route('admin.users.create', ['organization' => $organization->id]) }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                        <i class="fas fa-plus mr-2"></i>Create User
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
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
                                        @if($user->userRole)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $user->userRole->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">No role</span>
                                        @endif
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

            <!-- Branches Tab -->
            <div id="content-branches" class="tab-content hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kitchen Stations</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($organization->branches as $branch)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $branch->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $branch->address }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $branch->is_head_office ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $branch->is_head_office ? 'Head Office' : 'Branch' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $branch->kitchen_stations_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $branch->total_capacity ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.branches.show', [$organization, $branch]) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No branches found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Admins Tab -->
            <div id="content-admins" class="tab-content hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($organization->admins as $admin)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $admin->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $admin->email }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($admin->roles->count() > 0)
                                            @foreach($admin->roles as $role)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 mr-1">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-sm text-gray-500">No roles assigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $admin->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $admin->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $admin->branch_id ? $admin->branch->name : 'Organization Level' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $admin->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No admins found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kitchen Stations Tab -->
            <div id="content-kitchen-stations" class="tab-content hidden">
                <div class="space-y-6">
                    @foreach($organization->branches as $branch)
                        <div class="border border-gray-200 rounded-lg">
                            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-store mr-2"></i>
                                    {{ $branch->name }}
                                    @if($branch->is_head_office)
                                        <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Head Office
                                        </span>
                                    @endif
                                    <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </h3>
                            </div>

                            <div class="p-6">
                                @if($branch->kitchenStations->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($branch->kitchenStations as $station)
                                            <div class="border border-gray-200 rounded-lg p-4 {{ $station->is_active ? 'bg-white' : 'bg-gray-50' }}">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h4 class="font-semibold text-gray-900 flex items-center">
                                                        <i class="fas fa-fire mr-2 {{ $station->is_active ? 'text-orange-500' : 'text-gray-400' }}"></i>
                                                        {{ $station->name }}
                                                    </h4>
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $station->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $station->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>

                                                <dl class="space-y-2 text-sm">
                                                    <div>
                                                        <dt class="font-medium text-gray-600">Code:</dt>
                                                        <dd class="text-gray-900 font-mono">{{ $station->code }}</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="font-medium text-gray-600">Type:</dt>
                                                        <dd class="text-gray-900">
                                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                                {{ $station->type == 'cooking' ? 'bg-red-100 text-red-800' :
                                                                   ($station->type == 'prep' ? 'bg-yellow-100 text-yellow-800' :
                                                                   ($station->type == 'service' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                                                {{ ucfirst($station->type) }}
                                                            </span>
                                                        </dd>
                                                    </div>
                                                    <div>
                                                        <dt class="font-medium text-gray-600">Max Capacity:</dt>
                                                        <dd class="text-gray-900">{{ $station->max_capacity ?? 'Not set' }}</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="font-medium text-gray-600">Priority:</dt>
                                                        <dd class="text-gray-900">{{ $station->order_priority ?? 'Not set' }}</dd>
                                                    </div>
                                                    @if($station->description)
                                                    <div>
                                                        <dt class="font-medium text-gray-600">Description:</dt>
                                                        <dd class="text-gray-900">{{ $station->description }}</dd>
                                                    </div>
                                                    @endif
                                                    <div>
                                                        <dt class="font-medium text-gray-600">Created:</dt>
                                                        <dd class="text-gray-900">{{ $station->created_at->format('M d, Y H:i') }}</dd>
                                                    </div>
                                                </dl>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <i class="fas fa-utensils text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">No kitchen stations found for this branch</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if($organization->branches->count() == 0)
                        <div class="text-center py-8">
                            <i class="fas fa-store text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No branches found for this organization</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modules Tab -->
            <div id="content-modules" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($stats['available_modules'] as $module)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-gray-900">{{ $module['name'] }}</h4>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ ucfirst($module['tier'] ?? 'basic') }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">Access level: {{ ucfirst($module['tier'] ?? 'basic') }}</p>
                        </div>
                    @empty
                        <div class="col-span-full text-center text-gray-500">
                            No modules available in current subscription plan
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Activation Tab (Super Admin and Organization Admin) -->
            @if(auth('admin')->user()->isSuperAdmin() || (auth('admin')->user()?->admin_type === 'organization' && auth('admin')->user()?->organization?->id == $organization->id))
            <div id="content-activation" class="tab-content hidden">
                <div class="max-w-4xl mx-auto space-y-6">
                    <!-- Organization Status Card -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Organization Status</h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $organization->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas {{ $organization->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                {{ $organization->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->created_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    @if($organization->activated_at)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Activated</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->activated_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    @endif
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->updated_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="flex items-center justify-center">
                                @if(!$organization->is_active)
                                    <a href="{{ route('admin.organizations.activate.form', $organization) }}"
                                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center">
                                        <i class="fas fa-play mr-2"></i>
                                        Activate Organization
                                    </a>
                                @else
                                    <a href="{{ route('admin.organizations.activate.form', $organization) }}"
                                       class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center">
                                        <i class="fas fa-cog mr-2"></i>
                                        Manage Status
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Activation Key Management -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Activation Key Management</h3>

                        {{-- Debug Info --}}
                        @if(config('app.debug'))
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-sm text-blue-700">
                                <strong>Debug:</strong> Is Super Admin = {{ auth('admin')->user()->isSuperAdmin() ? 'YES' : 'NO' }}
                                | Admin ID = {{ auth('admin')->user()->id }}
                                | is_super_admin = {{ auth('admin')->user()->is_super_admin ? 'true' : 'false' }}
                            </p>
                        </div>
                        @endif

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                                <div class="text-sm text-yellow-700">
                                    <p class="font-medium">Security Notice</p>
                                    <p>The activation key is sensitive information. Only share it with authorized organization administrators.</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Activation Key</label>
                                <div class="flex items-center gap-3">
                                    <input type="password"
                                           id="activation-key-show"
                                           value="{{ $organization->activation_key ?? '-' }}"
                                           readonly
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-mono">
                                    <button type="button"
                                            onclick="toggleShowKeyVisibility()"
                                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors"
                                            title="Show/Hide Key">
                                        <i id="show-key-icon" class="fas fa-eye"></i>
                                    </button>
                                    <button type="button"
                                            onclick="copyShowActivationKey()"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                                            title="Copy Key">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    @if(auth('admin')->user()->isSuperAdmin())
                                    <form action="{{ route('admin.organizations.regenerate-key', $organization) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit"
                                                onclick="return confirm('Are you sure you want to regenerate the activation key? This will invalidate the current key and require organizations to use the new key for activation.')"
                                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold transition-colors"
                                                title="Regenerate Key">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </form>
                                    @else
                                    {{-- Debug: Show why regenerate button is hidden --}}
                                    @if(config('app.debug'))
                                    <span class="text-xs text-red-500 px-2">Regenerate hidden: Not super admin</span>
                                    @endif
                                    @endif
                                </div>
                                <div class="mt-2">
                                    <span class="text-sm text-gray-500">
                                        Last regenerated: {{ $organization->updated_at->format('M d, Y H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <a href="{{ route('admin.organizations.edit', $organization) }}"
                               class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                                <div class="text-center">
                                    <i class="fas fa-edit text-blue-500 text-2xl mb-2"></i>
                                    <p class="text-sm font-medium text-gray-900">Edit Organization</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.branches.create', ['organization' => $organization->id]) }}"
                               class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                                <div class="text-center">
                                    <i class="fas fa-plus text-green-500 text-2xl mb-2"></i>
                                    <p class="text-sm font-medium text-gray-900">Add Branch</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.users.create', ['organization' => $organization->id]) }}"
                               class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
                                <div class="text-center">
                                    <i class="fas fa-user-plus text-purple-500 text-2xl mb-2"></i>
                                    <p class="text-sm font-medium text-gray-900">Add User</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active', 'border-indigo-500', 'text-indigo-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showCopySuccess(element);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            fallbackCopyTextToClipboard(text, element);
        });
    } else {
        fallbackCopyTextToClipboard(text, element);
    }
}

function fallbackCopyTextToClipboard(text, element) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess(element);
        }
    } catch (err) {
        console.error('Fallback: Could not copy text: ', err);
    }

    document.body.removeChild(textArea);
}

function showCopySuccess(element) {
    const button = element.nextElementSibling;
    const originalIcon = button.innerHTML;

    button.innerHTML = '<i class="fas fa-check text-xs text-green-600"></i>';
    button.classList.add('text-green-600');

    setTimeout(() => {
        button.innerHTML = originalIcon;
        button.classList.remove('text-green-600');
    }, 2000);
}

// Activation key specific functions
function toggleShowKeyVisibility() {
    const input = document.getElementById('activation-key-show');
    const icon = document.getElementById('show-key-icon');

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

function copyShowActivationKey() {
    const input = document.getElementById('activation-key-show');
    const text = input.value;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showActivationKeyCopySuccess();
        }).catch(err => {
            console.error('Failed to copy: ', err);
            fallbackCopyActivationKey(text);
        });
    } else {
        fallbackCopyActivationKey(text);
    }
}

function fallbackCopyActivationKey(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showActivationKeyCopySuccess();
        }
    } catch (err) {
        console.error('Fallback: Could not copy text: ', err);
    }

    document.body.removeChild(textArea);
}

function showActivationKeyCopySuccess() {
    const copyButton = document.querySelector('button[onclick="copyShowActivationKey()"]');
    const originalIcon = copyButton.innerHTML;

    copyButton.innerHTML = '<i class="fas fa-check text-green-600"></i>';
    copyButton.classList.add('bg-green-500');
    copyButton.classList.remove('bg-blue-500');

    setTimeout(() => {
        copyButton.innerHTML = originalIcon;
        copyButton.classList.remove('bg-green-500');
        copyButton.classList.add('bg-blue-500');
    }, 2000);
}
</script>

<style>
.tab-button.active {
    border-color: #4F46E5;
    color: #4F46E5;
}
</style>
@endsection
