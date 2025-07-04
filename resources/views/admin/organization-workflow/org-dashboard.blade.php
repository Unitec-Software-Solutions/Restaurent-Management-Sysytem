@extends('layouts.admin')

@section('title', 'Organization Admin Dashboard')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-gray-900 mb-0">
                        {{ $organization->name }} - Management Dashboard
                    </h1>
                    <p class="text-muted">
                        Organization Admin Panel - Complete restaurant operations management
                    </p>
                    <div class="d-flex gap-2 mt-2">
                        <span class="badge badge-{{ $organization->is_active ? 'success' : 'warning' }}">
                            {{ $organization->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="badge badge-info">
                            {{ $organization->subscriptionPlan->name ?? 'No Plan' }}
                        </span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @if(!$organization->is_active)
                        <button class="btn btn-success" onclick="activateOrganization()">
                            <i class="fas fa-play"></i> Activate Organization
                        </button>
                    @endif
                    @if(session('original_super_admin_id'))
                        <a href="{{ route('switch-back-to-super-admin') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Super Admin
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Organization Status Alert -->
    @if(!$organization->is_active)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Organization Not Activated:</strong> Please activate your organization to enable full functionality.
                <button class="btn btn-sm btn-warning ml-3" onclick="activateOrganization()">
                    Activate Now
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Dashboard -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Branches</div>
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Inventory Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inventory_items'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Menu Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['menu_items'] ?? 0 }}</div>
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
                                Today's Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['todays_orders'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Management Sections -->
    <div class="row">
        <!-- Branch Management -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-store"></i> Branch Management
                    </h6>
                    <a href="{{ route('admin.branches.index', $organization) }}" class="btn btn-sm btn-primary">
                        Manage All
                    </a>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @forelse($organization->branches as $branch)
                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                        <div>
                            <h6 class="mb-1">{{ $branch->name }}</h6>
                            <small class="text-muted">{{ $branch->address }}</small>
                            <div>
                                @if($branch->is_head_office)
                                    <span class="badge badge-primary badge-sm">Head Office</span>
                                @endif
                                <span class="badge badge-{{ $branch->is_active ? 'success' : 'warning' }} badge-sm">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="btn-group-vertical btn-group-sm">
                            <button class="btn btn-outline-info btn-sm" onclick="viewBranchDetails({{ $branch->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ route('admin.branches.edit', [$organization, $branch]) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">No branches found</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kitchen Stations -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-utensils"></i> Kitchen Stations
                    </h6>
                    <a href="{{ route('admin.kitchen-stations.index', $organization) }}" class="btn btn-sm btn-primary">
                        Manage All
                    </a>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @php
                        $kitchenStations = $organization->branches->flatMap->kitchenStations;
                    @endphp
                    @forelse($kitchenStations as $kitchen)
                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                        <div>
                            <h6 class="mb-1">{{ $kitchen->name }}</h6>
                            <small class="text-muted">{{ $kitchen->branch->name }}</small>
                            <div>
                                <span class="badge badge-info badge-sm">{{ ucfirst($kitchen->type) }}</span>
                                <span class="badge badge-{{ $kitchen->is_active ? 'success' : 'warning' }} badge-sm">
                                    {{ $kitchen->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="btn-group-vertical btn-group-sm">
                            <button class="btn btn-outline-info btn-sm" onclick="viewKitchenDetails({{ $kitchen->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ route('admin.kitchen-stations.edit', $kitchen) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">No kitchen stations found</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Operations Management -->
    <div class="row">
        <!-- Inventory Management -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-boxes"></i> Inventory Management
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="addInventoryItem()">
                            <i class="fas fa-plus"></i> Add Inventory Item
                        </button>
                        <a href="{{ route('admin.inventory.index', $organization) }}" class="btn btn-outline-info">
                            <i class="fas fa-list"></i> View All Inventory
                        </a>
                        <button class="btn btn-outline-warning" onclick="viewLowStockAlerts()">
                            <i class="fas fa-exclamation-triangle"></i> Stock Alerts
                        </button>
                        <a href="{{ route('admin.suppliers.index', $organization) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-truck"></i> Manage Suppliers
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Management -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-utensils"></i> Menu Management
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="addMenuItem()">
                            <i class="fas fa-plus"></i> Add Menu Item
                        </button>
                        <button class="btn btn-outline-success" onclick="createMenu()">
                            <i class="fas fa-list-alt"></i> Create Menu
                        </button>
                        <a href="{{ route('admin.menus.index', $organization) }}" class="btn btn-outline-info">
                            <i class="fas fa-eye"></i> View All Menus
                        </a>
                        <a href="{{ route('admin.menu-categories.index', $organization) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-tags"></i> Manage Categories
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Management -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-receipt"></i> Order Management
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-success" onclick="createTakeawayOrder()">
                            <i class="fas fa-shopping-bag"></i> Create Takeaway Order
                        </button>
                        <button class="btn btn-outline-info" onclick="createReservation()">
                            <i class="fas fa-calendar"></i> Create Reservation
                        </button>
                        <a href="{{ route('admin.orders.index', $organization) }}" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> View All Orders
                        </a>
                        <button class="btn btn-outline-warning" onclick="viewKOTs()">
                            <i class="fas fa-print"></i> View KOTs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Reports -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Quick Reports & Analytics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-primary btn-block" onclick="viewSalesReport()">
                                <i class="fas fa-chart-line fa-2x mb-2"></i><br>
                                <small>Sales Report</small>
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-success btn-block" onclick="viewInventoryReport()">
                                <i class="fas fa-boxes fa-2x mb-2"></i><br>
                                <small>Inventory Report</small>
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-info btn-block" onclick="viewReservationReport()">
                                <i class="fas fa-calendar-check fa-2x mb-2"></i><br>
                                <small>Reservations Report</small>
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-warning btn-block" onclick="viewFinancialReport()">
                                <i class="fas fa-dollar-sign fa-2x mb-2"></i><br>
                                <small>Financial Report</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.organization-workflow.modals.add-inventory-item')
@include('admin.organization-workflow.modals.add-menu-item')
@include('admin.organization-workflow.modals.create-menu')
@include('admin.organization-workflow.modals.create-takeaway-order')
@include('admin.organization-workflow.modals.create-reservation')

@endsection

@push('scripts')
<script>
// Organization activation
function activateOrganization() {
    if (confirm('Are you sure you want to activate this organization? This will enable all features and make the organization operational.')) {
        fetch('/admin/organizations/{{ $organization->id }}/activate', {
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

// Inventory management functions
function addInventoryItem() {
    $('#addInventoryModal').modal('show');
}

function viewLowStockAlerts() {
    window.location.href = '/admin/organizations/{{ $organization->id }}/inventory/alerts';
}

// Menu management functions
function addMenuItem() {
    $('#addMenuItemModal').modal('show');
}

function createMenu() {
    $('#createMenuModal').modal('show');
}

// Order management functions
function createTakeawayOrder() {
    $('#createTakeawayOrderModal').modal('show');
}

function createReservation() {
    $('#createReservationModal').modal('show');
}

function viewKOTs() {
    window.location.href = '/admin/organizations/{{ $organization->id }}/kots';
}

// Report functions
function viewSalesReport() {
    window.open('/admin/organizations/{{ $organization->id }}/reports/sales', '_blank');
}

function viewInventoryReport() {
    window.open('/admin/organizations/{{ $organization->id }}/reports/inventory', '_blank');
}

function viewReservationReport() {
    window.open('/admin/organizations/{{ $organization->id }}/reports/reservations', '_blank');
}

function viewFinancialReport() {
    window.open('/admin/organizations/{{ $organization->id }}/reports/financial', '_blank');
}

// Detail view functions
function viewBranchDetails(branchId) {
    // Implementation for branch details modal
    window.location.href = `/admin/branches/${branchId}`;
}

function viewKitchenDetails(kitchenId) {
    // Implementation for kitchen details modal
    window.location.href = `/admin/kitchen-stations/${kitchenId}`;
}
</script>
@endpush

@push('styles')
<style>
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

.d-grid {
    display: grid;
    gap: 0.5rem;
}

.btn-block {
    width: 100%;
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
</style>
@endpush
