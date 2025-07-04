@extends('layouts.admin')

@section('title', 'Organization Management')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Organization Management</h1>
                    <p class="text-gray-600 mt-2">Manage organizations, subscription plans, and system-wide settings</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('organizations.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Add Organization
                    </a>
                    <a href="{{ route('admin.subscription-plans.index') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                        <i class="fas fa-credit-card mr-2"></i>
                        Subscription Plans
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-building text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total_organizations'] }}</h3>
                        <p class="text-gray-600">Total Organizations</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['active_organizations'] }}</h3>
                        <p class="text-gray-600">Active Organizations</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-store text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total_branches'] }}</h3>
                        <p class="text-gray-600">Total Branches</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-utensils text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total_kitchen_stations'] }}</h3>
                        <p class="text-gray-600">Kitchen Stations</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organizations Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($organizations as $org)
            <div class="bg-white rounded-lg shadow-md overflow-hidden {{ $org->is_active ? '' : 'opacity-75' }}">
                <!-- Organization Header -->
                <div class="px-6 py-4 border-b border-gray-200 {{ $org->is_active ? 'bg-green-50' : 'bg-red-50' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 rounded-full {{ $org->is_active ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $org->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $org->subscriptionPlan?->name ?? 'No Plan' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $org->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $org->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Organization Stats -->
                <div class="px-6 py-4">
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $org->branches->count() }}</div>
                            <div class="text-xs text-gray-500">Branches</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $org->branches->sum(fn($b) => $b->kitchenStations->count()) }}</div>
                            <div class="text-xs text-gray-500">Kitchens</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-indigo-600">{{ $org->admins->count() }}</div>
                            <div class="text-xs text-gray-500">Admins</div>
                        </div>
                    </div>

                    <!-- Head Office Info -->
                    @php
                        $headOffice = $org->branches->where('is_head_office', true)->first();
                    @endphp
                    @if($headOffice)
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-home mr-2"></i>
                            Head Office
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="{{ $headOffice->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $headOffice->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kitchen Stations:</span>
                                <span class="text-gray-900">{{ $headOffice->kitchenStations->count() }}</span>
                            </div>
                            @if($headOffice->kitchenStations->count() > 0)
                            <div class="mt-2">
                                <div class="text-xs text-gray-500 mb-1">Stations:</div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($headOffice->kitchenStations->take(3) as $station)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-800">
                                        <i class="fas fa-fire mr-1"></i>
                                        {{ $station->name }}
                                    </span>
                                    @endforeach
                                    @if($headOffice->kitchenStations->count() > 3)
                                    <span class="text-xs text-gray-500">+{{ $headOffice->kitchenStations->count() - 3 }} more</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Organization Admin -->
                    @php
                        $orgAdmin = $org->admins->where('is_super_admin', false)->first();
                    @endphp
                    @if($orgAdmin)
                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-user-tie mr-2"></i>
                            Organization Admin
                        </h4>
                        <div class="space-y-1 text-sm">
                            <div class="font-medium text-gray-900">{{ $orgAdmin->name }}</div>
                            <div class="text-gray-600">{{ $orgAdmin->email }}</div>
                            <div class="text-gray-600">{{ $orgAdmin->job_title ?? 'Organization Administrator' }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex space-x-2">
                        <a href="{{ route('organizations.show', $org) }}" class="flex-1 bg-blue-600 text-white text-center py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            View Details
                        </a>
                        @if(!$org->is_active)
                        <form action="{{ route('organizations.activate', $org) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 text-white py-2 px-3 rounded-lg hover:bg-green-700 transition-colors text-sm" onclick="return confirm('Are you sure you want to activate this organization?')">
                                Activate
                            </button>
                        </form>
                        @else
                        <button onclick="showOrgManagement('{{ $org->id }}')" class="flex-1 bg-purple-600 text-white py-2 px-3 rounded-lg hover:bg-purple-700 transition-colors text-sm">
                            Manage
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($organizations->hasPages())
        <div class="mt-6">
            {{ $organizations->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Management Modal -->
<div id="orgManagementModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center h-full p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Organization Management</h2>
                    <button onclick="closeOrgManagement()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="orgManagementContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showOrgManagement(orgId) {
    document.getElementById('orgManagementModal').classList.remove('hidden');
    document.getElementById('orgManagementContent').innerHTML = '<div class="flex justify-center"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
    
    // Load organization details via AJAX
    fetch(`/organizations/${orgId}`)
        .then(response => response.text())
        .then(html => {
            // Extract content from response and show in modal
            document.getElementById('orgManagementContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('orgManagementContent').innerHTML = '<div class="text-red-600">Error loading organization details.</div>';
        });
}

function closeOrgManagement() {
    document.getElementById('orgManagementModal').classList.add('hidden');
}

function showBranchDetails(branchId) {
    alert('Branch details for ID: ' + branchId + ' (Feature coming soon)');
}

function showKitchenDetails(stationId) {
    alert('Kitchen station details for ID: ' + stationId + ' (Feature coming soon)');
}

function showAdminDetails(adminId) {
    alert('Admin details for ID: ' + adminId + ' (Feature coming soon)');
}

// Close modal when clicking outside
document.getElementById('orgManagementModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeOrgManagement();
    }
});
</script>
@endsection
