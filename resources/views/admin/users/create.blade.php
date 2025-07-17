{{-- filepath: resources/views/admin/users/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
    <div class="container mx-auto px-4 py-8">
<div class="bg-white rounded shadow p-6">
    @if($organizations->isEmpty())
        <div class="mb-4 bg-yellow-100 text-yellow-800 p-3 rounded flex items-center justify-between">
            <span>No organizations found. Please create an organization before adding users.</span>
            <a href="{{ route('admin.organizations.create') }}"
               class="ml-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Create Organization
            </a>
        </div>
    @elseif($branches->isEmpty())
        <div class="mb-4 bg-yellow-100 text-yellow-800 p-3 rounded">
            No branches found for your organization. Please create a branch before adding users.
        </div>
    @elseif($roles->isEmpty())
        <div class="mb-4 bg-yellow-100 text-yellow-800 p-3 rounded flex items-center justify-between">
            <span>No roles found. Please create a role before adding users.</span>
            <a href="{{ route('admin.roles.create') }}"
               class="ml-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Create Role
            </a>
        </div>
    @else
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            @if(auth('admin')->user()->isSuperAdmin())
                <div class="mb-4">
                    <label for="organization_id" class="block text-sm font-medium text-gray-700">Organization</label>
                    <select id="organization_id" name="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Organization</option>
                        @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>
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
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
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
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('email')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter password"
                       class="w-full border rounded px-3 py-2" required>
                @error('password')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Re-enter password"
                       class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <strong>Created By:</strong>
                <span>
                    {{ auth('admin')->user()->name }}
                    @if(auth('admin')->user()->isSuperAdmin())
                        (Super Admin)
                    @endif
                </span>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('admin.users.index') }}" class="mr-3 bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Create User</button>
            </div>
        </form>
    @endif
</div>
</div>

@php
    $branchesArray = [];
    foreach($allBranches as $b) {
        $branchesArray[] = [
            'id' => $b->id,
            'name' => $b->name,
            'org_id' => $b->organization_id,
        ];
    }
@endphp

@if(auth('admin')->user()->isSuperAdmin())
<script>
// @ts-nocheck
document.addEventListener('DOMContentLoaded', function() {
    const branchesData = @json($branchesArray);
    const orgSelect = document.getElementById('organization_id');
    const branchSelect = document.getElementById('branch_id');

    // Exit if elements not found
    if (!orgSelect || !branchSelect) return;

    orgSelect.addEventListener('change', function() {
        const orgId = this.value;
        branchSelect.innerHTML = '<option value="">Select Branch</option>';

        if (orgId) {
            const orgBranches = branchesData.filter(b => b.org_id == orgId);
            orgBranches.forEach(branch => {
                const option = new Option(branch.name, branch.id);
                branchSelect.appendChild(option);
            });

            // Preselect if there was a previous selection
            const oldBranchId = "{{ old('branch_id') }}";
            if (oldBranchId) {
                branchSelect.value = oldBranchId;
            }
        }
    });

    // Trigger on page load if organization is pre-selected
    const oldOrgId = "{{ old('organization_id') }}";
    if (oldOrgId) {
        orgSelect.value = oldOrgId;
        orgSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endif
@endsection
