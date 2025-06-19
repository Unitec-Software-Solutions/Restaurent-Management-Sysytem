@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="bg-white rounded shadow p-6">
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        @if(auth('admin')->user()->isSuperAdmin())
            <div class="mb-4">
                <label for="organization_id" class="block text-sm font-medium text-gray-700">Organization</label>
                <select id="organization_id" name="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Organization</option>
                    @foreach($organizations as $organization)
                        <option value="{{ $organization->id }}" {{ old('organization_id', $user->organization_id) == $organization->id ? 'selected' : '' }}>
                            {{ $organization->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch (Optional)</label>
                <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Branch</option>
                    {{-- Branches will be populated by JS --}}
                </select>
            </div>
        @elseif(auth('admin')->user()->isAdmin())
            <input type="hidden" name="organization_id" value="{{ $organizations->first()->id }}">
            <div class="mb-4">
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch (Optional)</label>
                <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="organization_id" value="{{ $organizations->first()->id }}">
            <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Branch</label>
                <input type="text" value="{{ $branches->first()->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly>
            </div>
        @endif

        <div class="mb-4">
            <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
            <select id="role_id" name="role_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">Select Role</option>
                @foreach($roles as $role)
                    @if($role->name === 'Super Admin' && !auth('admin')->user()->isSuperAdmin())
                        @continue
                    @endif
                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @error('role_id')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('email')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-medium">Password <span class="text-gray-500 text-xs">(leave blank to keep current)</span></label>
            <input type="password" name="password" id="password" placeholder="Enter new password"
                   class="w-full border rounded px-3 py-2">
            @error('password')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-medium">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Re-enter new password"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="mt-6 flex justify-end">
            <a href="{{ route('admin.users.index') }}" class="mr-3 bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Cancel</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Update User</button>
        </div>
    </form>
</div>

@php
    $branchesArray = [];
    foreach($allBranches ?? $branches as $b) {
        $branchesArray[] = [
            'id' => $b->id,
            'name' => $b->name,
            'org_id' => $b->organization_id,
        ];
    }
@endphp

@if(auth('admin')->user()->isSuperAdmin())
<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchesData = @json($branchesArray);
    const orgSelect = document.getElementById('organization_id');
    const branchSelect = document.getElementById('branch_id');

    if (!orgSelect || !branchSelect) return;

    function populateBranches(orgId, selectedBranchId = null) {
        branchSelect.innerHTML = '<option value="">Select Branch</option>';
        if (orgId) {
            const orgBranches = branchesData.filter(b => b.org_id == orgId);
            orgBranches.forEach(branch => {
                const option = new Option(branch.name, branch.id);
                branchSelect.appendChild(option);
            });
            if (selectedBranchId) {
                branchSelect.value = selectedBranchId;
            }
        }
    }

    orgSelect.addEventListener('change', function() {
        populateBranches(this.value);
    });

    // On page load, preselect organization and branch if editing
    const oldOrgId = "{{ old('organization_id', $user->organization_id) }}";
    const oldBranchId = "{{ old('branch_id', $user->branch_id) }}";
    if (oldOrgId) {
        orgSelect.value = oldOrgId;
        populateBranches(oldOrgId, oldBranchId);
    }
});
</script>
@endif
@endsection