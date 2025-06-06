@extends('layouts.admin')

@section('header-title', 'Create Supplier Payment')
@section('content')
    <div class="p-4 rounded-lg">
        <x-nav-buttons :items="[
            ['name' => 'Suppliers Management', 'link' => route('admin.suppliers.index')],
            ['name' => 'Purchase Orders', 'link' => route('admin.purchase-orders.index')],
            ['name' => 'Supplier GRNs', 'link' => route('admin.grn.index')],
            ['name' => 'Supplier Payments', 'link' => route('admin.payments.index')],
        ]" active="Supplier Payments" />

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form id="paymentForm" action="{{ route('admin.payments.store') }}" method="POST">
                @csrf

                <!-- Header Section -->
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-xl font-semibold text-gray-900">New Supplier Payment</h2>
                    <div class="flex space-x-2">
                        <button type="button" id="saveDraftBtn"
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                            Save Draft
                        </button>
                        <button type="submit" id="submitBtn"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Submit Payment
                        </button>
                    </div>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
                        <h3 class="font-medium mb-2">Validation Errors</h3>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Basic Information Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Payment Number -->
                    <div>
                        <label for="payment_number" class="block text-sm font-medium text-gray-700 mb-1">Payment Number</label>
                        <input type="text" id="payment_number" name="payment_number"
                            value="PAY-{{ strtoupper(uniqid()) }}" class="w-full px-4 py-2 border rounded-lg bg-gray-100"
                            readonly>
                    </div>

                    <!-- Payment Date -->
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                        <input type="date" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}"
                            class="w-full px-4 py-2 border rounded-lg" required>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="payment_status" name="payment_status" class="w-full px-4 py-2 border rounded-lg"
                            required>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>

                <!-- Supplier Selection Section -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Supplier Information</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Supplier Dropdown -->
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                            <select id="supplier_id" name="supplier_id" class="w-full px-4 py-2 border rounded-lg" required>
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" data-contact="{{ $supplier->contact_person }}"
                                        data-phone="{{ $supplier->phone }}" data-email="{{ $supplier->email }}"
                                        data-due="{{ $supplier->pending_payment ?? 0 }}">
                                        {{ $supplier->name }} ({{ $supplier->supplier_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Supplier Details -->
                        <div id="supplierDetails" class="hidden bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Contact Person</p>
                                    <p id="supplierContact" class="text-gray-900">-</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Phone</p>
                                    <p id="supplierPhone" class="text-gray-900">-</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Email</p>
                                    <p id="supplierEmail" class="text-gray-900">-</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Total Due</p>
                                    <p id="supplierTotalDue" class="text-red-600 font-medium">$0.00</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                            <select id="branch_id" name="branch_id" class="w-full px-4 py-2 border rounded-lg" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Document Selection Tabs -->
                <div class="mb-8">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button type="button" id="grnTab"
                                class="border-b-2 border-indigo-500 text-indigo-600 px-4 py-3 text-sm font-medium">Pending
                                GRNs</button>
                            <button type="button" id="poTab"
                                class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-4 py-3 text-sm font-medium">Pending
                                POs</button>
                        </nav>
                    </div>

                    <!-- GRNs Tab Content -->
                    <div id="grnTabContent" class="pt-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Pending GRNs</h3>
                            <button type="button" id="loadGrnsBtn"
                                class="text-sm text-indigo-600 hover:text-indigo-800 hidden">
                                <i class="fas fa-sync-alt mr-1"></i> Load GRNs
                            </button>
                        </div>

                        <div id="grnsContainer" class="hidden">
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="selectAllGrns" class="rounded">
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                GRN No.</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                PO No.</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total Amount</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Paid Amount</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Due Amount</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Allocate Amount</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Due Date</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="grnsTableBody" class="bg-white divide-y divide-gray-200">
                                        <!-- GRNs will be loaded here via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="noGrnsMessage" class="text-center py-8 text-gray-500">
                            <i class="fas fa-box-open text-3xl mb-2"></i>
                            <p>Select a supplier to view pending GRNs</p>
                        </div>
                    </div>

                    <!-- POs Tab Content -->
                    <div id="poTabContent" class="hidden pt-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Pending Purchase Orders</h3>
                            <button type="button" id="loadPosBtn"
                                class="text-sm text-indigo-600 hover:text-indigo-800 hidden">
                                <i class="fas fa-sync-alt mr-1"></i> Load POs
                            </button>
                        </div>

                        <div id="posContainer" class="hidden">
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="selectAllPos" class="rounded">
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                PO No.</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total Amount</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Paid Amount</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Due Amount</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Allocate Amount</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Due Date</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="posTableBody" class="bg-white divide-y divide-gray-200">
                                        <!-- POs will be loaded here via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="noPosMessage" class="text-center py-8 text-gray-500">
                            <i class="fas fa-file-invoice text-3xl mb-2"></i>
                            <p>Select a supplier to view pending POs</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Allocation Section -->
                <div class="mb-8 hidden" id="paymentAllocationSection">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Payment Allocation</h3>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <!-- Selected Documents -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Selected Documents</label>
                                <div id="selectedDocumentsList"
                                    class="bg-white p-3 border rounded-lg h-32 overflow-y-auto">
                                    <!-- Selected documents will appear here -->
                                </div>
                            </div>

                            <!-- Payment Amount -->
                            <div>
                                <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-1">Payment
                                    Amount *</label>
                                <input type="number" step="0.01" id="total_amount" name="total_amount"
                                    class="w-full px-4 py-2 border rounded-lg" required>
                            </div>

                            <!-- Allocated Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Allocated Amount</label>
                                <input type="text" id="allocated_amount"
                                    class="w-full px-4 py-2 border rounded-lg bg-gray-100" readonly>
                            </div>
                        </div>

                        <!-- Payment Method Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="method_type" class="block text-sm font-medium text-gray-700 mb-1">Payment
                                    Method *</label>
                                <select id="method_type" name="method_type" class="w-full px-4 py-2 border rounded-lg"
                                    required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="credit_card">Credit Card</option>
                                </select>
                            </div>

                            <div>
                                <label for="reference_number"
                                    class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                                <input type="text" id="reference_number" name="reference_number"
                                    class="w-full px-4 py-2 border rounded-lg">
                            </div>

                            <div>
                                <label for="value_date" class="block text-sm font-medium text-gray-700 mb-1">Value
                                    Date</label>
                                <input type="date" id="value_date" name="value_date" value="{{ date('Y-m-d') }}"
                                    class="w-full px-4 py-2 border rounded-lg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
                </div>

                <!-- Hidden fields for selected documents and allocations -->
                <div id="selectedDocumentsFields"></div>
                <div id="allocationsFields"></div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM Elements
            const supplierSelect = document.getElementById('supplier_id');
            const supplierDetails = document.getElementById('supplierDetails');
            const loadGrnsBtn = document.getElementById('loadGrnsBtn');
            const loadPosBtn = document.getElementById('loadPosBtn');
            const grnTab = document.getElementById('grnTab');
            const poTab = document.getElementById('poTab');
            const grnTabContent = document.getElementById('grnTabContent');
            const poTabContent = document.getElementById('poTabContent');
            const grnsContainer = document.getElementById('grnsContainer');
            const posContainer = document.getElementById('posContainer');
            const noGrnsMessage = document.getElementById('noGrnsMessage');
            const noPosMessage = document.getElementById('noPosMessage');
            const paymentAllocationSection = document.getElementById('paymentAllocationSection');
            const selectedDocumentsList = document.getElementById('selectedDocumentsList');
            const selectedDocumentsFields = document.getElementById('selectedDocumentsFields');
            const allocationsFields = document.getElementById('allocationsFields');
            const totalAmountInput = document.getElementById('total_amount');
            const allocatedAmountInput = document.getElementById('allocated_amount');
            const selectAllGrns = document.getElementById('selectAllGrns');
            const selectAllPos = document.getElementById('selectAllPos');
            const paymentForm = document.getElementById('paymentForm');
            const saveDraftBtn = document.getElementById('saveDraftBtn');
            const submitBtn = document.getElementById('submitBtn');

            // Store selected documents
            let selectedDocuments = [];

            // Tab switching
            grnTab.addEventListener('click', () => {
                grnTab.classList.add('border-indigo-500', 'text-indigo-600');
                grnTab.classList.remove('border-transparent', 'text-gray-500');
                poTab.classList.add('border-transparent', 'text-gray-500');
                poTab.classList.remove('border-indigo-500', 'text-indigo-600');
                grnTabContent.classList.remove('hidden');
                poTabContent.classList.add('hidden');
            });

            poTab.addEventListener('click', () => {
                poTab.classList.add('border-indigo-500', 'text-indigo-600');
                poTab.classList.remove('border-transparent', 'text-gray-500');
                grnTab.classList.add('border-transparent', 'text-gray-500');
                grnTab.classList.remove('border-indigo-500', 'text-indigo-600');
                poTabContent.classList.remove('hidden');
                grnTabContent.classList.add('hidden');
            });

            // Supplier selection handler
            supplierSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                if (this.value) {
                    // Show supplier details
                    document.getElementById('supplierContact').textContent = selectedOption.getAttribute('data-contact') || '-';
                    document.getElementById('supplierPhone').textContent = selectedOption.getAttribute('data-phone') || '-';
                    document.getElementById('supplierEmail').textContent = selectedOption.getAttribute('data-email') || '-';
                    document.getElementById('supplierTotalDue').textContent = '$' + parseFloat(selectedOption.getAttribute('data-due')).toFixed(2);
                    supplierDetails.classList.remove('hidden');

                    // Show load buttons
                    loadGrnsBtn.classList.remove('hidden');
                    loadPosBtn.classList.remove('hidden');

                    // Hide no documents messages
                    noGrnsMessage.classList.add('hidden');
                    noPosMessage.classList.add('hidden');
                } else {
                    // Reset state
                    supplierDetails.classList.add('hidden');
                    loadGrnsBtn.classList.add('hidden');
                    loadPosBtn.classList.add('hidden');
                    noGrnsMessage.classList.remove('hidden');
                    noPosMessage.classList.remove('hidden');
                    grnsContainer.classList.add('hidden');
                    posContainer.classList.add('hidden');
                    paymentAllocationSection.classList.add('hidden');
                    selectedDocuments = [];
                    updateSelectedDocumentsList();
                    updatePaymentAllocation();
                }
            });

            // Load GRNs button handler
            loadGrnsBtn.addEventListener('click', function() {
                const supplierId = supplierSelect.value;
                if (!supplierId) {
                    alert('Please select a supplier first');
                    return;
                }

                // Show loading state
                const grnsTableBody = document.getElementById('grnsTableBody');
                grnsTableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading GRNs...
                        </td>
                    </tr>
                `;
                grnsContainer.classList.remove('hidden');

                // AJAX call to fetch GRNs
                fetch(`/admin/suppliers/${supplierId}/pending-grns`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    grnsTableBody.innerHTML = '';
                    if (data.length === 0) {
                        grnsTableBody.innerHTML = `
                            <tr>
                                <td colspan="10" class="px-4 py-4 text-center text-gray-500">
                                    No pending GRNs found for this supplier
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    // Populate GRNs table
                    data.forEach(grn => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50';
                        row.innerHTML = `
                            <td class="px-4 py-3 whitespace-nowrap">
                                <input type="checkbox" class="document-checkbox rounded" 
                                       data-type="grn" data-id="${grn.grn_id}" 
                                       data-number="${grn.grn_number}" data-due-amount="${grn.due_amount}">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${grn.grn_number}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${grn.po_number || 'N/A'}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${grn.received_date}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">$${parseFloat(grn.total_amount).toFixed(2)}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">$${parseFloat(grn.paid_amount).toFixed(2)}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">$${parseFloat(grn.due_amount).toFixed(2)}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <input type="number" step="0.01" class="allocate-amount w-24 px-2 py-1 border rounded-lg" 
                                       data-type="grn" data-id="${grn.grn_id}" 
                                       value="${parseFloat(grn.due_amount).toFixed(2)}" min="0" max="${grn.due_amount}">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm ${isOverdue(grn.due_date) ? 'text-red-500' : 'text-gray-500'}">
                                ${grn.due_date}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadgeClass(grn.status)}">
                                    ${grn.status.charAt(0).toUpperCase() + grn.status.slice(1)}
                                </span>
                            </td>
                        `;
                        grnsTableBody.appendChild(row);
                    });

                    addDocumentCheckboxListeners();
                    paymentAllocationSection.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error loading GRNs:', error);
                    grnsTableBody.innerHTML = `
                        <tr>
                            <td colspan="10" class="px-4 py-4 text-center text-red-500">
                            Error loading GRNs: ${error.message}. Please try again.
                        </td>
                        </tr>
                    `;
                });
            });

            // Load POs button handler
            loadPosBtn.addEventListener('click', function() {
                const supplierId = supplierSelect.value;
                if (!supplierId) {
                    alert('Please select a supplier first');
                    return;
                }

                // Show loading state
                const posTableBody = document.getElementById('posTableBody');
                posTableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-4 py-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading POs...
                        </td>
                    </tr>
                `;
                posContainer.classList.remove('hidden');

                // AJAX call to fetch POs
                fetch(`/admin/suppliers/${supplierId}/pending-pos`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    posTableBody.innerHTML = '';
                    if (data.length === 0) {
                        posTableBody.innerHTML = `
                            <tr>
                                <td colspan="9" class="px-4 py-4 text-center text-gray-500">
                                    No pending POs found for this supplier
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    // Populate POs table
                    data.forEach(po => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50';
                        row.innerHTML = `
                            <td class="px-4 py-3 whitespace-nowrap">
                                <input type="checkbox" class="document-checkbox rounded" 
                                       data-type="po" data-id="${po.po_id}" 
                                       data-number="${po.po_number}" data-due-amount="${po.due_amount}">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${po.po_number}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${po.order_date}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">$${parseFloat(po.total_amount).toFixed(2)}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">$${parseFloat(po.paid_amount).toFixed(2)}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">$${parseFloat(po.due_amount).toFixed(2)}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <input type="number" step="0.01" class="allocate-amount w-24 px-2 py-1 border rounded-lg" 
                                       data-type="po" data-id="${po.po_id}" 
                                       value="${parseFloat(po.due_amount).toFixed(2)}" min="0" max="${po.due_amount}">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm ${isOverdue(po.due_date) ? 'text-red-500' : 'text-gray-500'}">
                                ${po.due_date}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadgeClass(po.status)}">
                                    ${po.status.charAt(0).toUpperCase() + po.status.slice(1)}
                                </span>
                            </td>
                        `;
                        posTableBody.appendChild(row);
                    });

                    addDocumentCheckboxListeners();
                    paymentAllocationSection.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error loading POs:', error);
                    posTableBody.innerHTML = `
                        <tr>
                            <td colspan="9" class="px-4 py-4 text-center text-red-500">
                                Error loading POs: ${error.message}. Please try again.
                            </td>
                        </tr>
                    `;
                });
            });

            // Add event listeners to document checkboxes
            function addDocumentCheckboxListeners() {
                document.querySelectorAll('.document-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const type = this.getAttribute('data-type');
                        const id = this.getAttribute('data-id');
                        const number = this.getAttribute('data-number');
                        const dueAmount = parseFloat(this.getAttribute('data-due-amount'));
                        const allocateInput = this.closest('tr').querySelector('.allocate-amount');
                        const allocatedAmount = parseFloat(allocateInput.value) || dueAmount;

                        if (this.checked) {
                            selectedDocuments.push({
                                type: type,
                                id: id,
                                number: number,
                                due_amount: dueAmount,
                                allocated_amount: allocatedAmount
                            });
                            allocateInput.disabled = false;
                        } else {
                            selectedDocuments = selectedDocuments.filter(doc => !(doc.type === type && doc.id === id));
                            allocateInput.disabled = true;
                            allocateInput.value = dueAmount.toFixed(2);
                        }

                        updateSelectedDocumentsList();
                        updatePaymentAllocation();
                    });
                });

                // Add event listeners to allocation inputs
                document.querySelectorAll('.allocate-amount').forEach(input => {
                    input.addEventListener('input', function() {
                        const type = this.getAttribute('data-type');
                        const id = this.getAttribute('data-id');
                        const max = parseFloat(this.getAttribute('max'));
                        let value = parseFloat(this.value) || 0;

                        // Cap the allocated amount at the due amount
                        if (value > max) {
                            value = max;
                            this.value = value.toFixed(2);
                        }

                        // Update selected document
                        const doc = selectedDocuments.find(doc => doc.type === type && doc.id === id);
                        if (doc) {
                            doc.allocated_amount = value;
                            updateSelectedDocumentsList();
                            updatePaymentAllocation();
                        }
                    });
                });
            }

            // Check if a due date is overdue
            function isOverdue(dueDate) {
                if (!dueDate) return false;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const due = new Date(dueDate);
                return due < today;
            }

            // Get status badge class
            function getStatusBadgeClass(status) {
                switch (status.toLowerCase()) {
                    case 'paid':
                        return 'bg-green-100 text-green-800';
                    case 'pending':
                        return 'bg-yellow-100 text-yellow-800';
                    case 'partial':
                        return 'bg-blue-100 text-blue-800';
                    case 'overdue':
                        return 'bg-red-100 text-red-800';
                    case 'approved':
                        return 'bg-purple-100 text-purple-800';
                    case 'received':
                        return 'bg-green-100 text-green-800';
                    default:
                        return 'bg-gray-100 text-gray-800';
                }
            }

            // Update selected documents list
            function updateSelectedDocumentsList() {
                selectedDocumentsList.innerHTML = '';
                selectedDocumentsFields.innerHTML = '';
                allocationsFields.innerHTML = '';

                if (selectedDocuments.length === 0) {
                    selectedDocumentsList.innerHTML = '<p class="text-gray-500 text-sm">No documents selected</p>';
                    return;
                }

                const list = document.createElement('ul');
                list.className = 'space-y-2';

                selectedDocuments.forEach((doc, index) => {
                    const listItem = document.createElement('li');
                    listItem.className = 'flex justify-between items-center';
                    listItem.innerHTML = `
                        <span class="text-sm text-blue-600">${doc.type.toUpperCase()}: ${doc.number}</span>
                        <span class="text-sm font-medium text-gray-700">$${doc.allocated_amount.toFixed(2)} / $${doc.due_amount.toFixed(2)}</span>
                    `;
                    list.appendChild(listItem);

                    // Add hidden inputs for document IDs
                    const hiddenDocInput = document.createElement('input');
                    hiddenDocInput.type = 'hidden';
                    hiddenDocInput.name = `document_ids[${index}]`;
                    hiddenDocInput.value = `${doc.type}_${doc.id}`;
                    selectedDocumentsFields.appendChild(hiddenDocInput);

                    // Add hidden inputs for allocations
                    const hiddenAllocIdInput = document.createElement('input');
                    hiddenAllocIdInput.type = 'hidden';
                    hiddenAllocIdInput.name = `allocations[${index}][document_id]`;
                    hiddenAllocIdInput.value = `${doc.type}_${doc.id}`; // Correct format: grn_123 or po_123
                    allocationsFields.appendChild(hiddenAllocIdInput);

                    const hiddenAllocAmountInput = document.createElement('input');
                    hiddenAllocAmountInput.type = 'hidden';
                    hiddenAllocAmountInput.name = `allocations[${index}][amount]`;
                    hiddenAllocAmountInput.value = doc.allocated_amount.toFixed(2);
                    allocationsFields.appendChild(hiddenAllocAmountInput);
                });

                selectedDocumentsList.appendChild(list);
            }

            // Update payment allocation summary
            function updatePaymentAllocation() {
                const totalAllocated = selectedDocuments.reduce((sum, doc) => sum + doc.allocated_amount, 0);
                allocatedAmountInput.value = totalAllocated.toFixed(2);
                totalAmountInput.value = totalAllocated.toFixed(2);
            }

            // Select all GRNs checkbox
            selectAllGrns.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('#grnsTableBody .document-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });

            // Select all POs checkbox
            selectAllPos.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('#posTableBody .document-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });

            // Save as draft button
            saveDraftBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('payment_status').value = 'draft';
                // Remove document_ids and allocations validation for drafts
                selectedDocumentsFields.innerHTML = '';
                allocationsFields.innerHTML = '';
                totalAmountInput.value = totalAmountInput.value || '0';
                paymentForm.submit();
            });

            // Form submission validation
            paymentForm.addEventListener('submit', function(e) {
                const status = document.getElementById('payment_status').value;
                if (status !== 'draft' && selectedDocuments.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one document to allocate payment.');
                } else if (status !== 'draft' && parseFloat(totalAmountInput.value) <= 0) {
                    e.preventDefault();
                    alert('Payment amount must be greater than zero.');
                }
            });
        });
    </script>
@endpush