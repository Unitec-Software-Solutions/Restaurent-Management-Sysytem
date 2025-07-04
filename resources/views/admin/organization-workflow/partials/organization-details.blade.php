<div class="organization-details">
    <!-- Organization Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1">{{ $organization->name }}</h4>
                    <p class="text-muted mb-2">{{ $organization->trading_name ?? 'No trading name' }}</p>
                    <div class="d-flex gap-2 mb-2">
                        <span class="badge badge-{{ $organization->is_active ? 'success' : 'warning' }}">
                            {{ $organization->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="badge badge-info">
                            {{ $organization->subscriptionPlan->name ?? 'No Plan' }}
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    @if(!$organization->is_active && auth('admin')->user()->isSuperAdmin())
                    <button class="btn btn-success btn-sm" onclick="activateOrganization({{ $organization->id }})">
                        <i class="fas fa-play"></i> Activate
                    </button>
                    @endif
                    @if(auth('admin')->user()->isSuperAdmin())
                    <button class="btn btn-primary btn-sm" onclick="loginAsOrgAdmin({{ $organization->id }})">
                        <i class="fas fa-sign-in-alt"></i> Login as Admin
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Organization Info Tabs -->
    <ul class="nav nav-tabs" id="orgDetailTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">
                <i class="fas fa-info-circle"></i> Information
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="branches-tab" data-toggle="tab" href="#branches" role="tab">
                <i class="fas fa-store"></i> Branches ({{ $organization->branches->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="admins-tab" data-toggle="tab" href="#admins" role="tab">
                <i class="fas fa-users"></i> Admins ({{ $organization->admins->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="subscription-tab" data-toggle="tab" href="#subscription" role="tab">
                <i class="fas fa-credit-card"></i> Subscription
            </a>
        </li>
    </ul>

    <div class="tab-content mt-3" id="orgDetailTabContent">
        <!-- Information Tab -->
        <div class="tab-pane fade show active" id="info" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Contact Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $organization->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $organization->phone }}</td>
                        </tr>
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td>{{ $organization->address }}</td>
                        </tr>
                        <tr>
                            <td><strong>Contact Person:</strong></td>
                            <td>{{ $organization->contact_person ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Designation:</strong></td>
                            <td>{{ $organization->contact_person_designation ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Contact Phone:</strong></td>
                            <td>{{ $organization->contact_person_phone ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Business Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Registration:</strong></td>
                            <td>{{ $organization->registration_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Business Type:</strong></td>
                            <td>{{ ucfirst($organization->business_type ?? 'restaurant') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Discount:</strong></td>
                            <td>{{ $organization->discount_percentage ?? 0 }}%</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $organization->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Activated:</strong></td>
                            <td>{{ $organization->activated_at ? $organization->activated_at->format('M d, Y') : 'Not activated' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Branches Tab -->
        <div class="tab-pane fade" id="branches" role="tabpanel">
            <div class="row">
                @forelse($organization->branches as $branch)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">{{ $branch->name }}</h6>
                                    <p class="text-muted small mb-1">{{ $branch->address }}</p>
                                    <div class="d-flex gap-1">
                                        @if($branch->is_head_office)
                                        <span class="badge badge-primary badge-sm">Head Office</span>
                                        @endif
                                        <span class="badge badge-{{ $branch->is_active ? 'success' : 'warning' }} badge-sm">
                                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                <button class="btn btn-outline-info btn-sm" onclick="viewBranchDetails({{ $branch->id }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            
                            <!-- Kitchen Stations -->
                            @if($branch->kitchenStations->count() > 0)
                            <div class="mt-2">
                                <small class="text-muted">Kitchen Stations:</small>
                                @foreach($branch->kitchenStations as $kitchen)
                                <div class="ml-2">
                                    <i class="fas fa-utensils text-info"></i>
                                    <span class="small">{{ $kitchen->name }}</span>
                                    <span class="badge badge-secondary badge-sm">{{ ucfirst($kitchen->type) }}</span>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="mt-2">
                                <small class="text-muted">No kitchen stations created</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <p class="text-muted text-center">No branches found</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Admins Tab -->
        <div class="tab-pane fade" id="admins" role="tabpanel">
            <div class="row">
                @forelse($organization->admins as $admin)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">{{ $admin->name }}</h6>
                                    <p class="text-muted small mb-1">{{ $admin->email }}</p>
                                    <p class="text-muted small mb-1">{{ $admin->job_title ?? 'Administrator' }}</p>
                                    <div class="d-flex gap-1">
                                        <span class="badge badge-{{ $admin->is_active ? 'success' : 'warning' }} badge-sm">
                                            {{ $admin->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        @if($admin->is_super_admin)
                                        <span class="badge badge-danger badge-sm">Super Admin</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <button class="btn btn-outline-info btn-sm" onclick="viewAdminDetails({{ $admin->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(auth('admin')->user()->isSuperAdmin() && !$admin->is_super_admin)
                                    <button class="btn btn-outline-primary btn-sm" onclick="loginAsThisAdmin({{ $admin->id }})">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Roles -->
                            @if($admin->roles->count() > 0)
                            <div class="mt-2">
                                <small class="text-muted">Roles:</small>
                                @foreach($admin->roles as $role)
                                <span class="badge badge-secondary badge-sm ml-1">{{ $role->name }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <p class="text-muted text-center">No admins found</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Subscription Tab -->
        <div class="tab-pane fade" id="subscription" role="tabpanel">
            @if($organization->subscriptionPlan)
            <div class="row">
                <div class="col-md-8">
                    <h6 class="text-muted">Current Subscription Plan</h6>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $organization->subscriptionPlan->name }}</h5>
                            <p class="card-text">{{ $organization->subscriptionPlan->description }}</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Price:</strong> {{ $organization->subscriptionPlan->currency }} {{ number_format($organization->subscriptionPlan->price) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Max Branches:</strong> {{ $organization->subscriptionPlan->max_branches ?? 'Unlimited' }}
                                </div>
                            </div>

                            <!-- Modules -->
                            <div class="mt-3">
                                <h6 class="text-muted">Included Modules</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($organization->subscriptionPlan->getModulesWithNames() as $module)
                                    <span class="badge badge-info">{{ $module['name'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Quick Actions</h6>
                    <div class="list-group">
                        <button class="list-group-item list-group-item-action" onclick="viewInventory({{ $organization->id }})">
                            <i class="fas fa-boxes text-primary"></i> View Inventory
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="viewMenus({{ $organization->id }})">
                            <i class="fas fa-utensils text-success"></i> View Menus
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="viewOrders({{ $organization->id }})">
                            <i class="fas fa-receipt text-warning"></i> View Orders
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="viewReservations({{ $organization->id }})">
                            <i class="fas fa-calendar text-info"></i> View Reservations
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="viewReports({{ $organization->id }})">
                            <i class="fas fa-chart-bar text-secondary"></i> View Reports
                        </button>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>No Subscription Plan</h5>
                <p class="text-muted">This organization does not have an active subscription plan.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Quick action functions
function viewInventory(orgId) {
    window.open(`/admin/organizations/${orgId}/inventory`, '_blank');
}

function viewMenus(orgId) {
    window.open(`/admin/organizations/${orgId}/menus`, '_blank');
}

function viewOrders(orgId) {
    window.open(`/admin/organizations/${orgId}/orders`, '_blank');
}

function viewReservations(orgId) {
    window.open(`/admin/organizations/${orgId}/reservations`, '_blank');
}

function viewReports(orgId) {
    window.open(`/admin/organizations/${orgId}/reports`, '_blank');
}

function loginAsThisAdmin(adminId) {
    if (confirm('Login as this specific admin?')) {
        // Implementation for logging in as specific admin
        window.location.href = `/admin/admins/${adminId}/login-as`;
    }
}
</script>
