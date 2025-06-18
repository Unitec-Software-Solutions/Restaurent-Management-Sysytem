{{-- filepath: resources/views/admin/organizations/edit.blade.php --}}

@extends('layouts.admin')

@section('title', 'Edit Organization')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold mb-6">Edit Organization</h1>
        <a href="{{ route('admin.organizations.index') }}"
           class="inline-block mb-6 bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 transition font-semibold">
            ← Back to Organizations
        </a>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.organizations.update', $organization) }}" method="POST">
            @csrf
            @method('PUT')

            @if(auth('admin')->user()->isSuperAdmin())
                {{-- Super Admin: can edit all fields --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $organization->name) }}" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $organization->email) }}" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-600">*</span></label>
                    <select name="is_active" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="1" {{ old('is_active', $organization->is_active) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $organization->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subscription Plan <span class="text-red-600">*</span></label>
                    <select name="subscription_plan_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('subscription_plan_id', $organization->subscription_plan_id) == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} ({{ number_format($plan->price/100, 2) }} {{ $plan->currency }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                {{-- Non-super admin: show as readonly --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization Name</label>
                    <input type="text" value="{{ $organization->name }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="{{ $organization->email }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <input type="text" value="{{ $organization->is_active ? 'Active' : 'Inactive' }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subscription Plan</label>
                    <input type="text" value="{{ $organization->plan->name ?? 'N/A' }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500" readonly>
                </div>
            @endif

            {{-- Editable for all --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-600">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $organization->phone) }}" required pattern="\d{10,15}" maxlength="15"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-600">*</span></label>
                <textarea name="address" rows="4" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Line 1&#10;Line 2&#10;Line 3&#10;Line 4">{{ old('address', $organization->address ?? '') }}</textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person <span class="text-red-600">*</span></label>
                <input type="text" name="contact_person" value="{{ old('contact_person', $organization->contact_person) }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person Designation <span class="text-red-600">*</span></label>
                <input type="text" name="contact_person_designation" value="{{ old('contact_person_designation', $organization->contact_person_designation) }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person Phone <span class="text-red-600">*</span></label>
                <input type="text" name="contact_person_phone" value="{{ old('contact_person_phone', $organization->contact_person_phone) }}" required pattern="\d{10,15}" maxlength="15"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Discount field --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Discount (%) <span class="text-red-600">*</span>
                </label>
                <input type="number" name="discount_percentage" value="{{ old('discount_percentage', $organization->discount_percentage ?? 0) }}"
                    min="0" max="100" step="0.01" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="0.00">
            </div>

            {{-- Password fields --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Old Password
                </label>
                <input type="password" value="********" disabled
                    class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-400">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    New Password
                </label>
                <div class="relative">
                    <input type="password" name="password" id="password" autocomplete="new-password"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-10">
                    <button type="button" onclick="togglePassword('password')" tabindex="-1"
                        class="absolute right-2 top-2 text-gray-500 focus:outline-none">
                        <svg id="password-eye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <small class="text-gray-500">Leave blank to keep current password.</small>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm New Password
                </label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-10">
                    <button type="button" onclick="togglePassword('password_confirmation')" tabindex="-1"
                        class="absolute right-2 top-2 text-gray-500 focus:outline-none">
                        <svg id="password_confirmation-eye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('admin.organizations.index') }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Organization</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const eye = document.getElementById(id + '-eye');
    if (input.type === "password") {
        input.type = "text";
        eye.classList.add('text-indigo-600');
    } else {
        input.type = "password";
        eye.classList.remove('text-indigo-600');
    }
}
</script>
@endsection