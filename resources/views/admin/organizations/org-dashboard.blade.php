@extends('layouts.admin')

@section('title', 'Organization Dashboard - ' . $organization->name)

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Organization Header -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="p-3 rounded-full {{ $organization->is_active ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                        <i class="fas fa-building text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $organization->name }}</h1>
                        <p class="text-gray-600">{{ $organization->subscriptionPlan?->name ?? 'No Subscription Plan' }}</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $organization->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $organization->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @if(!$organization->is_active && auth('admin')->user()->isSuperAdmin())
                    <form action="{{ route('organizations.activate', $organization) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors" onclick="return confirm('Are you sure you want to activate this organization?')">
                            <i class="fas fa-power-off mr-2"></i>
                            Activate Organization
                        </button>
                    </form>
                    @endif
                    @if($organization->is_active)
                    <a href="{{ route('admin.organizations.inventory.index', $organization) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-boxes mr-2"></i>
                        Manage Inventory
                    </a>
                    <a href="{{ route('admin.organizations.menus.index', $organization) }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-utensils mr-2"></i>
                        Manage Menus
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-store text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total_branches'] }}</h3>
                        <p class="text-gray-600">Branches</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['active_branches'] }}</h3>
                        <p class="text-gray-600">Active Branches</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-utensils text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $organization->branches->sum(fn($b) => $b->kitchenStations->count()) }}</h3>
                        <p class="text-gray-600">Kitchen Stations</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total_users'] }}</h3>
                        <p class="text-gray-600">Users</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-calendar text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['subscription_days_left'] ?? 'N/A' }}</h3>
                        <p class="text-gray-600">Days Left</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Head Office Details -->
            @php
                $headOffice = $organization->branches->where('is_head_office', true)->first();
            @endphp
            @if($headOffice)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-home mr-2 text-blue-600"></i>
                        Head Office
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $headOffice->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $headOffice->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Contact Person:</span>
                            <span class="text-gray-900">{{ $headOffice->contact_person ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Kitchen Stations:</span>
                            <span class="text-gray-900">{{ $headOffice->kitchenStations->count() }}</span>
                        </div>

                        @if($headOffice->kitchenStations->count() > 0)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Kitchen Stations:</h4>
                            <div class="space-y-2">
                                @foreach($headOffice->kitchenStations as $station)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-fire text-orange-500"></i>
                                        <span class="font-medium">{{ $station->name }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded">{{ ucfirst($station->type) }}</span>
                                        <span class="text-xs {{ $station->is_active ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $station->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Organization Admin Details -->
            @php
                $orgAdmin = $organization->admins->where('is_super_admin', false)->first();
            @endphp
            @if($orgAdmin)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user-tie mr-2 text-green-600"></i>
                        Organization Administrator
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-xl font-bold mx-auto mb-3">
                                {{ substr($orgAdmin->name, 0, 2) }}
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">{{ $orgAdmin->name }}</h4>
                            <p class="text-gray-600">{{ $orgAdmin->job_title ?? 'Organization Administrator' }}</p>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-envelope text-gray-400"></i>
                                <span class="text-gray-900">{{ $orgAdmin->email }}</span>
                            </div>
                            @if($orgAdmin->phone)
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-phone text-gray-400"></i>
                                <span class="text-gray-900">{{ $orgAdmin->phone }}</span>
                            </div>
                            @endif
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-calendar text-gray-400"></i>
                                <span class="text-gray-600">Joined {{ $orgAdmin->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-circle text-{{ $orgAdmin->is_active ? 'green' : 'red' }}-500"></i>
                                <span class="text-gray-900">{{ $orgAdmin->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </div>

                        @if(auth('admin')->user()->isSuperAdmin())
                        <div class="pt-4 border-t border-gray-200">
                            <button onclick="loginAsOrgAdmin('{{ $orgAdmin->id }}')" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login as Organization Admin
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Branches List -->
        @if($organization->branches->where('is_head_office', false)->count() > 0)
        <div class="mt-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-purple-600"></i>
                        Other Branches
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kitchen Stations</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($organization->branches->where('is_head_office', false) as $branch)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $branch->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $branch->address ?? 'No address set' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $branch->kitchenStations->count() }} stations
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $branch->contact_person ?? 'Not set' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="manageBranch('{{ $branch->id }}')" class="text-indigo-600 hover:text-indigo-900">
                                        Manage
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="mt-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.orders.index', ['organization' => $organization->id]) }}" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg transition-colors">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                            <div>
                                <div class="font-medium text-gray-900">View Orders</div>
                                <div class="text-sm text-gray-600">Manage all orders</div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.reservations.index', ['organization' => $organization->id]) }}" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg transition-colors">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                            <div>
                                <div class="font-medium text-gray-900">Reservations</div>
                                <div class="text-sm text-gray-600">Table bookings</div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.reports.index', ['organization' => $organization->id]) }}" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg transition-colors">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
                            <div>
                                <div class="font-medium text-gray-900">Reports</div>
                                <div class="text-sm text-gray-600">Analytics & insights</div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('organizations.edit', $organization) }}" class="bg-orange-50 hover:bg-orange-100 p-4 rounded-lg transition-colors">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-cog text-orange-600 text-xl"></i>
                            <div>
                                <div class="font-medium text-gray-900">Settings</div>
                                <div class="text-sm text-gray-600">Organization config</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loginAsOrgAdmin(adminId) {
    if (confirm('Are you sure you want to login as this organization admin? This will switch your current session.')) {
        // Create a form and submit to login as org admin
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/login-as-org-admin';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const adminInput = document.createElement('input');
        adminInput.type = 'hidden';
        adminInput.name = 'admin_id';
        adminInput.value = adminId;
        
        form.appendChild(csrf);
        form.appendChild(adminInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function manageBranch(branchId) {
    alert('Manage branch: ' + branchId + ' (Feature coming soon)');
}
</script>
@endsection
