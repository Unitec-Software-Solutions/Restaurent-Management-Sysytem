@extends('layouts.admin')

@section('title', 'Organization Activation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Organization Activation</h1>
        
        @if(auth('admin')->user()->isSuperAdmin())
            <div class="flex gap-3">
                <a href="{{ route('admin.organizations.index') }}"
                   class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-building mr-2"></i>Manage Organizations
                </a>
            </div>
        @endif
    </div>

    <!-- Flash Messages -->
    <div id="flash-messages" class="mb-6">
        @if(session('error'))
            <div class="flash-message mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <div>{{ session('error') }}</div>
                <button onclick="this.parentElement.remove()" class="ml-auto text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
        
        @if(session('success'))
            <div class="flash-message mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <div>{{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="ml-auto text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div class="flash-message mb-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded flex items-center">
                <i class="fas fa-info-circle mr-3"></i>
                <div>{{ session('info') }}</div>
                <button onclick="this.parentElement.remove()" class="ml-auto text-blue-700 hover:text-blue-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
    </div>
    
    <!-- Organizations List -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">
                @if(auth('admin')->user()->isSuperAdmin())
                    All Organizations
                @else
                    My Organization
                @endif
            </h2>
            <p class="text-gray-600 mt-1">
                @if(auth('admin')->user()->isSuperAdmin())
                    Manage activation status for all organizations in the system
                @else
                    Activate your organization using the provided activation key
                @endif
            </p>
        </div>

        @if($organizations->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branches</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($organizations as $organization)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full {{ $organization->is_active ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                                <i class="fas fa-building {{ $organization->is_active ? 'text-green-600' : 'text-red-600' }}"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $organization->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $organization->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $organization->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas {{ $organization->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                        {{ $organization->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($organization->activated_at)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Activated: {{ $organization->activated_at->format('M d, Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <span class="font-medium">{{ $organization->branches->count() }}</span>
                                        @if($organization->branches->count() > 0)
                                            <span class="ml-2 text-gray-500">
                                                ({{ $organization->branches->where('is_active', true)->count() }} active)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <span class="font-medium">{{ $organization->users->count() }}</span>
                                        @if($organization->users->count() > 0)
                                            <span class="ml-2 text-gray-500">
                                                ({{ $organization->users->where('is_registered', true)->count() }} registered)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($organization->subscriptionPlan)
                                        <div class="flex items-center">
                                            <span class="font-medium">{{ $organization->subscriptionPlan->name }}</span>
                                            <span class="ml-2 text-gray-500">({{ $organization->subscriptionPlan->currency }} {{ number_format($organization->subscriptionPlan->price, 2) }})</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">No Plan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if(!$organization->is_active)
                                        <!-- Activation Form -->
                                        <button onclick="showActivationModal({{ $organization->id }}, '{{ $organization->name }}')"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <i class="fas fa-key mr-2"></i>
                                            Activate
                                        </button>
                                    @else
                                        <span class="text-green-600 font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Active
                                        </span>
                                        @if(auth('admin')->user()->isSuperAdmin())
                                            <button onclick="showDeactivationModal({{ $organization->id }}, '{{ $organization->name }}')"
                                                    class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                                <i class="fas fa-pause mr-2"></i>
                                                Deactivate
                                            </button>
                                        @endif
                                    @endif
                                    
                                    <!-- View Details Link -->
                                    <a href="{{ route('admin.organizations.show', $organization) }}"
                                       class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-eye mr-2"></i>
                                        Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                    <i class="fas fa-building text-gray-400 text-xl"></i>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No organizations found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(auth('admin')->user()->isSuperAdmin())
                        There are no organizations in the system yet.
                    @else
                        You don't have an organization assigned to your account.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Activation Modal -->
<div id="activationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center mx-auto mb-4">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <i class="fas fa-key text-green-600 text-xl"></i>
                </div>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-4">Activate Organization</h3>
            
            <form id="activationForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Organization</label>
                    <div id="modalOrgName" class="text-sm text-gray-900 font-medium"></div>
                </div>
                
                <div class="mb-4">
                    <label for="modal_activation_key" class="block text-sm font-medium text-gray-700 mb-2">
                        Activation Key <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="modal_activation_key" 
                           name="activation_key"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" 
                           placeholder="Enter activation key"
                           required>
                    <p class="text-xs text-gray-500 mt-1">
                        Enter the correct activation key to activate this organization.
                    </p>
                </div>
                
                <div class="flex gap-3 justify-end">
                    <button type="button" 
                            onclick="hideActivationModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        <i class="fas fa-check mr-2"></i>Activate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deactivation Modal (Super Admin Only) -->
@if(auth('admin')->user()->isSuperAdmin())
<div id="deactivationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center mx-auto mb-4">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
                    <i class="fas fa-pause text-orange-600 text-xl"></i>
                </div>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-4">Deactivate Organization</h3>
            
            <form id="deactivationForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="deactivate">
                
                <div class="mb-4">
                    <p class="text-sm text-gray-700">
                        Are you sure you want to deactivate <span id="deactivateOrgName" class="font-medium"></span>?
                    </p>
                    <p class="text-sm text-red-600 mt-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        This will also deactivate all branches belonging to this organization.
                    </p>
                </div>
                
                <div class="flex gap-3 justify-end">
                    <button type="button" 
                            onclick="hideDeactivationModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition">
                        <i class="fas fa-pause mr-2"></i>Deactivate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function showActivationModal(orgId, orgName) {
    document.getElementById('modalOrgName').textContent = orgName;
    document.getElementById('modal_activation_key').value = ''; // Don't pre-fill the key
    document.getElementById('activationForm').action = `/admin/organizations/${orgId}/activate-by-key`;
    document.getElementById('activationModal').classList.remove('hidden');
}

function hideActivationModal() {
    document.getElementById('activationModal').classList.add('hidden');
}

@if(auth('admin')->user()->isSuperAdmin())
function showDeactivationModal(orgId, orgName) {
    document.getElementById('deactivateOrgName').textContent = orgName;
    document.getElementById('deactivationForm').action = `/admin/organizations/${orgId}/activate`;
    document.getElementById('deactivationModal').classList.remove('hidden');
}

function hideDeactivationModal() {
    document.getElementById('deactivationModal').classList.add('hidden');
}
@endif

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.id === 'activationModal') {
        hideActivationModal();
    }
    @if(auth('admin')->user()->isSuperAdmin())
    if (event.target.id === 'deactivationModal') {
        hideDeactivationModal();
    }
    @endif
});

// Auto-hide flash messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(function(message) {
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 300);
        });
    }, 5000);
});
</script>
@endpush
@endsection
