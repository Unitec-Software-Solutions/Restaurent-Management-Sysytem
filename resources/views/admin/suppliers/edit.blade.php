@extends('layouts.admin')

@section('content')
    <div class="p-4 rounded-lg">
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ isset($supplier) ? 'Edit Supplier' : 'Add New Supplier' }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ isset($supplier) ? 'Update supplier information' : 'Create a new supplier record' }}
                </p>
            </div>

            <form
                action="{{ isset($supplier) ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}"
                method="POST" class="p-6 space-y-6">
                @csrf
                @if (isset($supplier))
                    @method('PUT')
                @endif

                <!-- Organization Selection for Super Admin -->
                @if (auth('admin')->user()->isSuperAdmin())
                    <div>
                        <label for="organization_id" class="block text-sm font-medium text-gray-700">
                            Organization <span class="text-red-500">*</span>
                        </label>
                        <select name="organization_id" id="organization_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Organization</option>
                            @foreach ($organizations as $org)
                                <option value="{{ $org->id }}"
                                    {{ old('organization_id', $supplier->organization_id ?? '') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('organization_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <!-- Display current organization for non-super admins -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Organization</label>
                        <div class="mt-1 p-3 bg-gray-50 rounded-md border border-gray-300">
                            <span class="text-sm text-gray-900">{{ $supplier->organization->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endif

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Company Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $supplier->name ?? '') }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Person -->
                <div>
                    <label for="contact_person" class="block text-sm font-medium text-gray-700">Contact Person</label>
                    <input type="text" name="contact_person" id="contact_person"
                        value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('contact_person')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $supplier->phone ?? '') }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $supplier->email ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $supplier->address ?? '') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- VAT Registration -->
                <div>
                    <div class="flex items-center">
                        <input type="checkbox" name="has_vat_registration" id="has_vat_registration" value="1"
                            {{ old('has_vat_registration', $supplier->has_vat_registration ?? false) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="has_vat_registration" class="ml-2 block text-sm text-gray-700">
                            Has VAT Registration
                        </label>
                    </div>

                    <div id="vat_number_container"
                        class="mt-3 {{ old('has_vat_registration', $supplier->has_vat_registration ?? false) ? '' : 'hidden' }}">
                        <label for="vat_registration_no" class="block text-sm font-medium text-gray-700">VAT Registration
                            Number</label>
                        <input type="text" name="vat_registration_no" id="vat_registration_no"
                            value="{{ old('vat_registration_no', $supplier->vat_registration_no ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('vat_registration_no')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if (isset($supplier))
                    <!-- Status -->
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Active Supplier
                            </label>
                        </div>
                    </div>
                @endif

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                    <a href="{{ route('admin.suppliers.index') }}"
                        class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ isset($supplier) ? 'Update Supplier' : 'Create Supplier' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('has_vat_registration').addEventListener('change', function() {
                const vatContainer = document.getElementById('vat_number_container');
                vatContainer.classList.toggle('hidden', !this.checked);

                const vatInput = document.getElementById('vat_registration_no');
                if (!this.checked) {
                    vatInput.value = '';
                }
            });
        </script>
    @endpush
@endsection
