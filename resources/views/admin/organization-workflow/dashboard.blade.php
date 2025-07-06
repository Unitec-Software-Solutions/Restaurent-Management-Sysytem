@extends('layouts.admin')

@section('title', 'Organization Management Dashboard')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-gray-900 mb-0">Restaurant Management Dashboard</h1>
                    <p class="text-muted">Complete workflow management for restaurant operations</p>
                </div>
                <div class="d-flex gap-2">
                    @if(auth('admin')->user()->isSuperAdmin())
                        <a href="{{ route('admin.subscription-plans.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Create Subscription Plan
                        </a>
                        <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary">
                            <i class="fas fa-building"></i> Create Organization
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Organizations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['organizations'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Branches</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['branches'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-store fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Kitchen Stations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['kitchen_stations'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-utensils fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['orders'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workflow Section -->
    <div class="row">
        <!-- Module & Subscription Management -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cubes"></i> Module & Subscription Management
                    </h6>
                    @if(auth('admin')->user()->isSuperAdmin())
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">
                            Actions
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('admin.modules.index') }}">
                                <i class="fas fa-puzzle-piece"></i> Manage Modules
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.subscription-plans.index') }}">
                                <i class="fas fa-credit-card"></i> Subscription Plans
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Available Modules</h6>
                            <div class="list-group list-group-flush">
                                @forelse($modules as $module)
                                <div class="list-group-item d-flex justify-content-between align-items-center p-2">
                                    <div>
                                        <i class="fas fa-cube text-primary"></i>
                                        <span class="ml-2">{{ $module->name }}</span>
                                    </div>
                                    <span class="badge badge-{{ $module->is_active ? 'success' : 'secondary' }}">
                                        {{ $module->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                @empty
                                <p class="text-muted text-center">No modules found</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Subscription Plans</h6>
                            <div class="list-group list-group-flush">
                                @forelse($subscriptionPlans as $plan)
                                <div class="list-group-item d-flex justify-content-between align-items-center p-2">
                                    <div>
                                        <i class="fas fa-credit-card text-success"></i>
                                        <span class="ml-2">{{ $plan->name }}</span>
                                    </div>
                                    <span class="badge badge-info">{{ $plan->currency }} {{ number_format($plan->price, 2) }}</span>
                                </div>
                                @empty
                                <p class="text-muted text-center">No plans found</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organization Structure -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-sitemap"></i> Organization Structure
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($organizations as $organization)
                    <div class="organization-item mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-building text-primary"></i>
                                    {{ $organization->name }}
                                </h6>
                                <p class="text-muted small mb-1">{{ $organization->subscriptionPlan->name ?? 'No Plan' }}</p>
                                <span class="badge badge-{{ $organization->is_active ? 'success' : 'warning' }}">
                                    {{ $organization->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="btn-group-vertical btn-group-sm">
                                <button class="btn btn-outline-info btn-sm" onclick="viewOrganizationDetails({{ $organization->id }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if(!$organization->is_active && auth('admin')->user()->isSuperAdmin())
                                <button class="btn btn-outline-success btn-sm" onclick="activateOrganization({{ $organization->id }})">
                                    <i class="fas fa-play"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Branches -->
                        @if($organization->branches->count() > 0)
                        <div class="mt-2">
                            <small class="text-muted">Branches:</small>
                            @foreach($organization->branches as $branch)
                            <div class="ml-3 mt-1">
                                <i class="fas fa-store text-success"></i>
                                <span class="small">{{ $branch->name }}</span>
                                @if($branch->is_head_office)
                                    <span class="badge badge-primary badge-sm">HQ</span>
                                @endif
                                <button class="btn btn-outline-secondary btn-xs ml-2" onclick="viewBranchDetails({{ $branch->id }})">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Organization Admins -->
                        @if($organization->admins->count() > 0)
                        <div class="mt-2">
                            <small class="text-muted">Admins:</small>
                            @foreach($organization->admins as $admin)
                            <div class="ml-3 mt-1">
                                <i class="fas fa-user-shield text-warning"></i>
                                <span class="small">{{ $admin->name }} ({{ $admin->email }})</span>
                                <button class="btn btn-outline-secondary btn-xs ml-2" onclick="viewAdminDetails({{ $admin->id }})">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted text-center">No organizations found</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs"></i> Quick Workflow Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <button class="btn btn-outline-primary btn-block" onclick="createModule()">
                                    <i class="fas fa-cube fa-2x mb-2"></i><br>
                                    <small>Create Module</small>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <button class="btn btn-outline-success btn-block" onclick="createSubscriptionPlan()">
                                    <i class="fas fa-credit-card fa-2x mb-2"></i><br>
                                    <small>Create Subscription Plan</small>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <button class="btn btn-outline-info btn-block" onclick="createOrganization()">
                                    <i class="fas fa-building fa-2x mb-2"></i><br>
                                    <small>Create Organization</small>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <button class="btn btn-outline-warning btn-block" onclick="viewSystemReports()">
                                    <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                    <small>System Reports</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.organization-workflow.modals.organization-details')
@include('admin.organization-workflow.modals.branch-details')
@include('admin.organization-workflow.modals.admin-details')
@include('admin.organization-workflow.modals.kitchen-details')

@endsection

@push('scripts')
<script>
// Organization Details Modal
function viewOrganizationDetails(organizationId) {
    fetch(`/admin/organizations/${organizationId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('orgDetailsContent').innerHTML = data.html;
                $('#organizationDetailsModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load organization details');
        });
}

// Branch Details Modal
function viewBranchDetails(branchId) {
    fetch(`/admin/branches/${branchId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('branchDetailsContent').innerHTML = data.html;
                $('#branchDetailsModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load branch details');
        });
}

// Admin Details Modal
function viewAdminDetails(adminId) {
    fetch(`/admin/admins/${adminId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('adminDetailsContent').innerHTML = data.html;
                $('#adminDetailsModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load admin details');
        });
}

// Activate Organization
function activateOrganization(organizationId) {
    if (confirm('Are you sure you want to activate this organization?')) {
        fetch(`/admin/organizations/${organizationId}/activate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to activate organization: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to activate organization');
        });
    }
}

// Quick Actions
function createModule() {
    window.location.href = '/admin/modules/create';
}

function createSubscriptionPlan() {
    window.location.href = '/admin/subscription-plans/create';
}

function createOrganization() {
    window.location.href = '/admin/organizations/create';
}

function viewSystemReports() {
    window.location.href = '/admin/reports';
}

// Login as Organization Admin
function loginAsOrgAdmin(organizationId) {
    if (confirm('Login as this organization\'s admin?')) {
        fetch(`/admin/organizations/${organizationId}/login-as-admin`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                alert('Failed to login as organization admin: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to login as organization admin');
        });
    }
}

// Auto-refresh every 30 seconds for real-time updates
setInterval(() => {
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
</script>
@endpush

@push('styles')
<style>
.organization-item {
    transition: all 0.3s ease;
}

.organization-item:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-xs {
    padding: 0.1rem 0.3rem;
    font-size: 0.675rem;
    line-height: 1.2;
    border-radius: 0.2rem;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.badge-sm {
    font-size: 0.6em;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endpush
