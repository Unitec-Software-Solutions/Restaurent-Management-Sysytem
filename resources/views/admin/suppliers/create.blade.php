@extends('layouts.admin')
@section('header-title', 'Create Supplier')
@section('content')
<div class="p-4 sm:p-6">
    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden max-w-4xl mx-auto">
        <!-- Card Header -->
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold text-gray-900">
                {{ isset($supplier) ? 'Edit Supplier' : 'Create New Supplier' }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ isset($supplier) ? 'Update supplier information' : 'Add a new supplier to your system' }}
            </p>
        </div>

        <!-- Form Container -->
        <form id="supplierForm" action="{{ isset($supplier) ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}"
              method="POST" class="p-6">
            @csrf
            @if(isset($supplier))
                @method('PUT')
            @endif

            <!-- Tab Navigation -->
            <div class="mb-6 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px" id="supplierTabs" role="tablist">
                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 rounded-t-lg"
                                id="company-tab"
                                data-tabs-target="#company"
                                type="button"
                                role="tab"
                                aria-controls="company"
                                aria-selected="true">
                            Company Details
                        </button>
                    </li>
                    <li class="mr-2" role="presentation">
                        <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300"
                                id="contact-tab"
                                data-tabs-target="#contact"
                                type="button"
                                role="tab"
                                aria-controls="contact"
                                aria-selected="false"
                                disabled>
                            Contact Details
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Contents -->
            <div id="supplierTabsContent">
                <!-- Company Details Tab -->
                <div class="hidden p-4 rounded-lg bg-gray-50" id="company" role="tabpanel" aria-labelledby="company-tab">
                    <!-- Organization Selection for Super Admin -->
                    @if(auth('admin')->user()->isSuperAdmin())
                        <div class="mb-5">
                            <label for="organization_id" class="block mb-2 text-sm font-medium text-gray-900">
                                Organization <span class="text-red-500">*</span>
                            </label>
                            <select name="organization_id" id="organization_id" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Select Organization</option>
                                @foreach($organizations as $org)
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
                        <div class="mb-5">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Organization</label>
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-300">
                                <span class="text-sm text-gray-900">{{ auth('admin')->user()->organization->name }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Company Name -->
                    <div class="mb-5">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">
                            Company Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                               value="{{ old('name', $supplier->name ?? '') }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                               placeholder="Supplier Company Name" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- VAT Registration -->
                    <div class="mb-5">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" name="has_vat_registration" id="has_vat_registration" value="1"
                                   {{ old('has_vat_registration', $supplier->has_vat_registration ?? false) ? 'checked' : '' }}
                                   class="w-4 h-4 border border-gray-300 rounded-sm bg-gray-50 focus:ring-blue-500 focus:ring-2">
                            <label for="has_vat_registration" class="ms-2 text-sm font-medium text-gray-900">
                                Has VAT Registration
                            </label>
                        </div>

                        <div id="vat_number_container"
                             class="{{ old('has_vat_registration', $supplier->has_vat_registration ?? false) ? '' : 'hidden' }}">
                            <label for="vat_registration_no" class="block mb-2 text-sm font-medium text-gray-900">
                                VAT Registration Number
                            </label>
                            <input type="text" name="vat_registration_no" id="vat_registration_no"
                                   value="{{ old('vat_registration_no', $supplier->vat_registration_no ?? '') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                   placeholder="VAT123456789">
                            @error('vat_registration_no')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-5">
                        <label for="address" class="block mb-2 text-sm font-medium text-gray-900">
                            Company Address
                        </label>
                        <textarea name="address" id="address" rows="3"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            placeholder="123 Main St, City, Country">{{ old('address', $supplier->address ?? '') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Toggle -->
                    @if(isset($supplier))
                    <div class="mb-5">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 border border-gray-300 rounded-sm bg-gray-50 focus:ring-blue-500 focus:ring-2">
                            <label for="is_active" class="ms-2 text-sm font-medium text-gray-900">
                                Active Supplier
                            </label>
                        </div>
                    </div>
                    @endif

                    <!-- Next Button -->
                    <div class="flex justify-end pt-4">
                        <button type="button" id="nextToContactBtn"
                            class="px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Next: Contact Details <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Contact Details Tab -->
                <div class="hidden p-4 rounded-lg bg-gray-50" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                    <!-- Contact Person -->
                    <div class="mb-5">
                        <label for="contact_person" class="block mb-2 text-sm font-medium text-gray-900">
                            Contact Person <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="contact_person" id="contact_person"
                               value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                               placeholder="John Doe" required>
                        @error('contact_person')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone with icon -->
                    <div class="mb-5">
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">
                            Phone <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 19 18">
                                    <path d="M18 13.446a3.02 3.02 0 0 0-.946-1.985l-1.4-1.4a3.054 3.054 0 0 0-4.218 0l-.7.7a.983.983 0 0 1-1.39 0l-2.1-2.1a.983.983 0 0 1 0-1.389l.7-.7a2.98 2.98 0 0 0 0-4.217l-1.4-1.4a2.824 2.824 0 0 0-4.218 0c-3.619 3.619-3 8.229 1.752 12.979C6.785 16.639 9.45 18 11.912 18a7.175 7.175 0 0 0 5.139-2.325A2.9 2.9 0 0 0 18 13.446Z"/>
                                </svg>
                            </div>
                            <input type="tel" name="phone" id="phone"
                                   value="{{ old('phone', $supplier->phone ?? '') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                   placeholder="123-456-7890" required>
                        </div>
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email with icon -->
                    <div class="mb-5">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                                    <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z"/>
                                    <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z"/>
                                </svg>
                            </div>
                            <input type="email" name="email" id="email"
                                   value="{{ old('email', $supplier->email ?? '') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                   placeholder="supplier@example.com">
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between pt-4 border-t">
                        <button type="button" id="backToCompanyBtn"
                            class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <i class="fas fa-arrow-left mr-2"></i> Back
                        </button>
                        <div class="flex gap-3">
                            <button type="reset"
                                class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Reset
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                {{ isset($supplier) ? 'Update Supplier' : 'Create Supplier' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tabs
        const tabs = document.querySelectorAll('[data-tabs-target]');
        const tabContents = document.querySelectorAll('[role="tabpanel"]');

        // Show first tab by default
        document.getElementById('company').classList.remove('hidden');
        document.getElementById('company-tab').classList.add('border-blue-500', 'text-blue-600');

        // Tab switching
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = document.querySelector(tab.dataset.tabsTarget);

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Deactivate all tabs
                tabs.forEach(t => {
                    t.classList.remove('border-blue-500', 'text-blue-600');
                    t.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                });

                // Show selected tab content
                target.classList.remove('hidden');

                // Activate selected tab
                tab.classList.add('border-blue-500', 'text-blue-600');
                tab.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            });
        });

        // Next button (Company -> Contact)
        document.getElementById('nextToContactBtn').addEventListener('click', function() {
            // Validate required fields in company tab
            const companyName = document.getElementById('name').value;
            if (!companyName) {
                alert('Please fill in the company name');
                return;
            }

            // Enable contact tab
            document.getElementById('contact-tab').disabled = false;

            // Switch to contact tab
            document.getElementById('contact-tab').click();
        });

        // Back button (Contact -> Company)
        document.getElementById('backToCompanyBtn').addEventListener('click', function() {
            document.getElementById('company-tab').click();
        });

        // Toggle VAT number field visibility
        const vatCheckbox = document.getElementById('has_vat_registration');
        const vatContainer = document.getElementById('vat_number_container');

        vatCheckbox.addEventListener('change', function() {
            if(this.checked) {
                vatContainer.classList.remove('hidden');
            } else {
                vatContainer.classList.add('hidden');
                document.getElementById('vat_registration_no').value = '';
            }
        });
    });
</script>
@endpush
@endsection
