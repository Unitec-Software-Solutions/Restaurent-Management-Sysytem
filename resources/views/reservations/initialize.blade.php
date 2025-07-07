@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Start New Reservation</h2>
        
        <form action="{{ route('reservation.create') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Reservation Type Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reservation Type</label>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="radio" name="type" value="online" class="mr-3" checked>
                        <span class="text-gray-700">Online Reservation</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="type" value="in_call" class="mr-3">
                        <span class="text-gray-700">Phone Call Reservation</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="type" value="walk_in" class="mr-3">
                        <span class="text-gray-700">Walk-in Reservation</span>
                    </label>
                </div>
            </div>

            @if($isAdmin && $organizations->isNotEmpty())
            <!-- Organization Selection (Admin only) -->
            <div>
                <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-2">Organization</label>
                <select name="organization_id" id="organization_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Organization</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ ($defaults['organization_id'] ?? '') == $org->id ? 'selected' : '' }}>
                            {{ $org->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Branch Selection -->
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select name="branch_id" id="branch_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ ($defaults['branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} 
                            @if(!$isAdmin)
                                ({{ $branch->organization->name }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Customer Phone (Optional for lookup) -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Customer Phone (Optional)</label>
                <input 
                    type="tel" 
                    name="phone" 
                    id="phone" 
                    value="{{ $defaults['phone'] ?? '' }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter phone number to lookup existing customer"
                >
                <p class="text-xs text-gray-500 mt-1">If provided, we'll pre-fill customer details if they exist</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between pt-4">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    ← Back to Dashboard
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Continue to Reservation Details →
                </button>
            </div>
        </form>
    </div>
</div>

@if($isAdmin)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const organizationSelect = document.getElementById('organization_id');
    const branchSelect = document.getElementById('branch_id');
    
    if (organizationSelect) {
        organizationSelect.addEventListener('change', function() {
            const orgId = this.value;
            
            // Clear branch options
            branchSelect.innerHTML = '<option value="">Loading branches...</option>';
            
            if (orgId) {
                fetch(`/api/organizations/${orgId}/branches`)
                    .then(response => response.json())
                    .then(data => {
                        branchSelect.innerHTML = '<option value="">Select Branch</option>';
                        data.branches.forEach(branch => {
                            const option = document.createElement('option');
                            option.value = branch.id;
                            option.textContent = branch.name;
                            branchSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching branches:', error);
                        branchSelect.innerHTML = '<option value="">Error loading branches</option>';
                    });
            } else {
                branchSelect.innerHTML = '<option value="">Select Branch</option>';
            }
        });
    }
});
</script>
@endif
@endsection
