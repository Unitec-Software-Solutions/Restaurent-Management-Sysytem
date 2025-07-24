@extends('layouts.admin')

@php
    use App\Models\Employee;
    $now = now();
    $start_time = $now->format('H:i');
    $end_time = $now->copy()->addHours(2)->format('H:i');
    $defaultDate = $defaultDate ?? $now->format('Y-m-d');
    $user = auth()->user();
@endphp

@section('content')
<div class="mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800">Create Reservation</h1>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.reservations.store') }}" method="POST">
                    @csrf

                    <!-- Organization & Branch Selection -->
                    <div class="grid grid-cols-2 gap-4">
                        {{-- SUPER ADMIN --}}
                        @if($user->isSuperAdmin())
                            <div class="mb-4">
                                <label>Organization</label>
                                <select id="organization_id" name="organization_id" class="w-full" required>
                                    <option value="">Select Organization</option>
                                    @foreach($organizations ?? [] as $org)
                                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label>Branch</label>
                                <select id="branch_id" name="branch_id" class="w-full" required>
                                    <option value="">Select Branch First</option>
                                </select>
                            </div>
                        {{-- ORG ADMIN (NO BRANCH ASSIGNED) --}}
                        @elseif($user->organization_id && !$user->branch_id)
                            <div class="mb-4">
                                <label>Branch</label>
                                <select name="branch_id" class="w-full" required>
                                    <option value="">Select Branch</option>
                                    @isset($branches)
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        {{-- ORG ADMIN (WITH BRANCH ASSIGNED) --}}
                        @elseif($user->organization_id && $user->branch_id)
                            <input type="hidden" name="branch_id" value="{{ $user->branch_id }}">
                            <div class="mb-4">
                                <strong>Branch:</strong> {{ $user->branch->name ?? '' }}
                            </div>
                        @endif
                    </div>

                    {{-- Phone Number Display --}}
                    <div class="mt-4">
                        <label>Branch Phone</label>
                        <input type="text" id="branch_phone" class="form-input" readonly>
                    </div>

                    <script>
                    function loadBranches(orgId) {
                        fetch(`/api/organizations/${orgId}/branches`)
                            .then(res => res.json())
                            .then(branches => {
                                const branchSelect = document.getElementById("branch_id");
                                branchSelect.innerHTML = `<option value="">Select Branch</option>`;
                                branches.forEach(branch => {
                                    const opt = document.createElement("option");
                                    opt.value = branch.id;
                                    opt.text = branch.name;
                                    opt.setAttribute("data-phone", branch.phone);
                                    branchSelect.appendChild(opt);
                                });
                            });
                    }

                    function updatePhone(select) {
                        const phone = select.options[select.selectedIndex].getAttribute('data-phone');
                        document.getElementById('branch_phone').value = phone || '';
                    }
                    </script>

                    <!-- Customer Details -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Customer Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name', 'Default Customer') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                                <input type="tel"
                                       name="phone"
                                       id="phone"
                                       value="{{ old('phone', $defaultPhone ?? '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       value="{{ old('email') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Reservation Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                <input type="date"
                                       name="date"
                                       id="date"
                                       min="{{ now()->format('Y-m-d') }}"
                                       value="{{ old('date', $defaultDate) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                                <input type="time"
                                       name="start_time"
                                       id="start_time"
                                       value="{{ old('start_time', $start_time) }}"
                                       step="900"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
                                <input type="time"
                                       name="end_time"
                                       id="end_time"
                                       value="{{ old('end_time', $end_time) }}"
                                       step="900"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div>
                                <label for="number_of_people" class="block text-sm font-medium text-gray-700 mb-1">Number of People *</label>
                                <input type="number"
                                       name="number_of_people"
                                       id="number_of_people"
                                       min="1"
                                       value="{{ old('number_of_people', 1) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                        </div>
                    </div>

                    <!-- Assign Tables -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-700">Assign Tables</h2>
                            <span id="capacity-info" class="text-sm text-gray-500">
                                Total Capacity: <span id="selected-capacity">0</span>/<span id="required-capacity">{{ old('number_of_people', 1) }}</span>
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach (isset($tables) && $tables ? $tables : [] as $table)
                                <label class="cursor-pointer">
                                    <input type="checkbox"
                                           name="assigned_table_ids[]"
                                           value="{{ $table->id }}"
                                           class="hidden peer table-checkbox"
                                           data-capacity="{{ $table->capacity }}"
                                           {{ in_array($table->id, $availableTableIds ?? []) ? '' : 'disabled' }}>
                                    <div data-table-id="{{ $table->id }}"
                                         class="table-selection w-20 h-20 flex flex-col items-center justify-center border rounded-md text-xs p-2
                                                peer-checked:bg-blue-500 peer-checked:text-white
                                                {{ in_array($table->id, $availableTableIds ?? [])
                                                    ? 'bg-white hover:bg-blue-100 cursor-pointer border-gray-300'
                                                    : 'bg-gray-200 text-gray-700 border-gray-400 cursor-not-allowed opacity-70' }}">
                                        <span>Table {{ $table->id }}</span>
                                        <span>Cap: {{ $table->capacity }}</span>
                                        <span class="availability-text text-xs mt-1">
                                            {{ in_array($table->id, $availableTableIds ?? []) ? '' : 'Unavailable' }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Unavailable tables are grayed out and cannot be selected.</p>
                    </div>

                    <!-- Steward Assignment -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Steward Assignment</h2>
                        <div>
                            <label for="steward_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Steward</label>
                            <select name="steward_id" id="steward_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Steward</option>
                                @foreach(Employee::where('position', 'steward')->get() as $steward)
                                    <option value="{{ $steward->id }}" {{ old('steward_id') == $steward->id ? 'selected' : '' }}>
                                        {{ $steward->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Submit Section -->
                    <div class="flex justify-between items-center pt-6 border-t">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            Create Reservation
                        </button>
                        <a href="{{ route('admin.reservations.index') }}" class="text-gray-600 hover:text-gray-800 font-medium">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const dateInput = document.getElementById('date');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const branchSelect = document.getElementById('branch_id');
    const phoneInput = document.getElementById('phone');
    const peopleInput = document.getElementById('number_of_people');
    const capacityInfo = document.getElementById('selected-capacity');
    const requiredCapacity = document.getElementById('required-capacity');
    const tableCheckboxes = document.querySelectorAll('.table-checkbox');

    // Update capacity display
    function updateCapacityDisplay() {
        let totalCapacity = 0;
        tableCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                totalCapacity += parseInt(checkbox.dataset.capacity);
            }
        });
        capacityInfo.textContent = totalCapacity;
        requiredCapacity.textContent = peopleInput.value;

        // Highlight if insufficient capacity
        if (totalCapacity < parseInt(peopleInput.value)) {
            capacityInfo.parentElement.classList.add('text-red-600');
        } else {
            capacityInfo.parentElement.classList.remove('text-red-600');
        }
    }

    // Update phone number from branch selection
    function updatePhoneFromBranch() {
        if (branchSelect && phoneInput) {
            const selectedOption = branchSelect.options[branchSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.phone) {
                phoneInput.value = selectedOption.dataset.phone;
            }
        }
    }

    // Check table availability
    async function updateTableAvailability() {
        const date = dateInput.value;
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        // Get branch ID - either from select or hidden input
        let branchId;
        if (branchSelect) {
            branchId = branchSelect.value;
        } else {
            const hiddenBranchInput = document.querySelector('input[name="branch_id"]');
            branchId = hiddenBranchInput ? hiddenBranchInput.value : null;
        }

        if (!date || !startTime || !endTime || !branchId) return;

        try {
            const response = await fetch(
                `{{ route('admin.check-table-availability') }}?date=${date}&start_time=${startTime}&end_time=${endTime}&branch_id=${branchId}`
            );
            const data = await response.json();

            document.querySelectorAll('.table-selection').forEach(tableDiv => {
                const tableId = parseInt(tableDiv.dataset.tableId);
                const isAvailable = data.available_table_ids.includes(tableId);
                const tableCheckbox = tableDiv.parentElement.querySelector('input[type="checkbox"]');

                // Reset classes
                tableDiv.classList.remove(
                    'bg-red-200', 'text-red-700', 'border-red-500',
                    'bg-white', 'hover:bg-blue-100', 'border-gray-300'
                );

                if (isAvailable) {
                    tableDiv.classList.add('bg-white', 'hover:bg-blue-100', 'border-gray-300');
                    if (tableCheckbox) tableCheckbox.disabled = false;
                } else {
                    tableDiv.classList.add('bg-gray-200', 'text-gray-700', 'border-gray-400');
                    if (tableCheckbox) {
                        tableCheckbox.disabled = true;
                        tableCheckbox.checked = false;
                    }
                }

                // Update availability text
                const textElement = tableDiv.querySelector('.availability-text');
                if (textElement) {
                    textElement.textContent = isAvailable ? '' : 'Unavailable';
                }
            });

            updateCapacityDisplay();
        } catch (error) {
            console.error('Error checking table availability:', error);
        }
    }

    // Initialize
    updatePhoneFromBranch();
    updateCapacityDisplay();

    // Event Listeners
    if (startTimeInput) {
        startTimeInput.addEventListener('change', updateTableAvailability);
    }

    if (endTimeInput) endTimeInput.addEventListener('change', updateTableAvailability);
    if (dateInput) dateInput.addEventListener('change', updateTableAvailability);
    if (branchSelect) {
        branchSelect.addEventListener('change', function() {
            updatePhoneFromBranch();
            updateTableAvailability();
        });
    }
    if (peopleInput) peopleInput.addEventListener('input', updateCapacityDisplay);

    document.querySelectorAll('.table-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateCapacityDisplay);
    });

    // Initial availability check
    updateTableAvailability();

    @if(auth()->user()->isSuperAdmin())
    document.getElementById('organization_id').addEventListener('change', function() {
        const orgId = this.value;
        loadBranches(orgId);
    });
    @endif
});
</script>

@endsection
