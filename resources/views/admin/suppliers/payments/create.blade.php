@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-slate-700">Supplier Payment Dashboard</h1>
    </header>
    
    <main>
        <div class="bg-white shadow-lg rounded-xl p-8 mb-10">
            <h2 class="text-2xl font-semibold text-slate-600 mb-6">Create New Payment</h2>
            <form id="paymentForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="paymentNo">Payment No.</label>
                        <input class="w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm bg-slate-50 cursor-not-allowed focus:outline-none" 
                               id="paymentNo" name="paymentNo" readonly type="text" value="Loading..."/>
                        <p class="text-xs text-slate-500 mt-1">Auto-generated</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="paymentDate">Payment Date</label>
                        <input class="w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-150" 
                               id="paymentDate" name="paymentDate" type="date" value="{{ date('Y-m-d') }}"/>
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-1">
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="supplier">Select Supplier</label>
                        <div class="relative">
                            <select class="w-full appearance-none px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-150 bg-white" 
                                    id="supplier" name="supplier">
                                <option disabled selected value="">Choose a supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-700">
                                <span class="material-icons">arrow_drop_down</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="paymentDetails">Payment Details / Notes</label>
                        <textarea class="w-full px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-150" 
                                  id="paymentDetails" name="paymentDetails" placeholder="Enter payment details, reference numbers, or any notes" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Supplier Details Section (hidden initially) -->
        <div class="bg-white shadow-lg rounded-xl p-8 mb-10 hidden" id="supplierDetailsSection">
            <h2 class="text-2xl font-semibold text-slate-600 mb-2">Supplier Overview: <span class="text-sky-600" id="selectedSupplierName"></span></h2>
            <p class="text-sm text-slate-500 mb-6">Supplier ID: <span id="selectedSupplierId"></span></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-amber-100 border-l-4 border-amber-500 p-4 rounded-md shadow">
                    <h3 class="text-sm font-medium text-amber-700">Total Overdue Amount</h3>
                    <p class="text-2xl font-semibold text-amber-800" id="supplierOverdueAmount">$0.00</p>
                </div>
                <div class="bg-sky-100 border-l-4 border-sky-500 p-4 rounded-md shadow">
                    <h3 class="text-sm font-medium text-sky-700">Pending GRNs Value</h3>
                    <p class="text-2xl font-semibold text-sky-800" id="supplierPendingGrns">$0.00</p>
                </div>
                <div class="bg-indigo-100 border-l-4 border-indigo-500 p-4 rounded-md shadow">
                    <h3 class="text-sm font-medium text-indigo-700">Pending PO Payments</h3>
                    <p class="text-2xl font-semibold text-indigo-800" id="supplierPendingPo">$0.00</p>
                </div>
            </div>
            
            <div>
                <div class="border-b border-slate-200 mb-4">
                    <nav aria-label="Tabs" class="-mb-px flex space-x-8">
                        <button class="tab-button active hs-tab-active:font-semibold hs-tab-active:border-sky-600 hs-tab-active:text-sky-600 py-4 px-1 border-b-2 border-transparent text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 focus:outline-none" 
                                data-tab="pendingGrnsContent">Pending GRNs</button>
                        <button class="tab-button hs-tab-active:font-semibold hs-tab-active:border-sky-600 hs-tab-active:text-sky-600 py-4 px-1 border-b-2 border-transparent text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 focus:outline-none" 
                                data-tab="pendingPoContent">Pending POs</button>
                        <button class="tab-button hs-tab-active:font-semibold hs-tab-active:border-sky-600 hs-tab-active:text-sky-600 py-4 px-1 border-b-2 border-transparent text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 focus:outline-none" 
                                data-tab="paymentHistoryContent">Payment History</button>
                    </nav>
                </div>
                
                <!-- Pending GRNs Tab -->
                <div class="tab-content active" id="pendingGrnsContent">
                    <h3 class="text-xl font-semibold text-slate-600 mb-4">Pending GRNs for Payment</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-left">
                            <thead class="border-b border-slate-300">
                                <tr>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">
                                        <input class="form-checkbox h-4 w-4 text-sky-600 border-slate-300 rounded focus:ring-sky-500" id="selectAllGrns" type="checkbox"/>
                                    </th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">GRN No.</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">GRN Date</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">PO No.</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">GRN Value</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">Due Balance</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">Due Date</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">Pay Amount</th>
                                </tr>
                            </thead>
                            <tbody id="pendingGrnsTableBody"></tbody>
                        </table>
                    </div>
                    <p class="text-slate-500 mt-4" id="noGrnsMessage">No pending GRNs for this supplier.</p>
                </div>
                
                <!-- Pending POs Tab -->
                <div class="tab-content" id="pendingPoContent">
                    <h3 class="text-xl font-semibold text-slate-600 mb-4">Pending Purchase Orders</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-left">
                            <thead class="border-b border-slate-300">
                                <tr>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">PO No.</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">PO Date</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">PO Value</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">Amount Paid</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">Balance Due</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody id="pendingPoTableBody"></tbody>
                        </table>
                    </div>
                    <p class="text-slate-500 mt-4" id="noPosMessage">No pending POs for this supplier.</p>
                </div>
                
                <!-- Payment History Tab -->
                <div class="tab-content" id="paymentHistoryContent">
                    <h3 class="text-xl font-semibold text-slate-600 mb-4">Supplier Payment History</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-left">
                            <thead class="border-b border-slate-300">
                                <tr>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">Payment ID</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">Payment Date</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">Amount Paid</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">Method</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody id="paymentHistoryTableBody"></tbody>
                        </table>
                    </div>
                    <p class="text-slate-500 mt-4" id="noPaymentHistoryMessage">No payment history available for this supplier.</p>
                </div>
            </div>
            
            <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <button class="w-full sm:w-auto flex items-center justify-center px-6 py-3 bg-sky-500 text-white rounded-lg shadow-md hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition duration-150" 
                        id="addSelectedGrnsButton" type="button">
                    <span class="material-icons mr-2">add_circle_outline</span>
                    Add Selected GRNs to Payment
                </button>
                <div class="text-right">
                    <p class="text-lg font-semibold text-slate-700">Total Selected for Payment: <span class="text-sky-600" id="totalSelectedForPayment">$0.00</span></p>
                </div>
            </div>
        </div>
        
        <!-- Payment Line Items -->
        <div class="bg-white shadow-lg rounded-xl p-8 mb-10">
            <h2 class="text-2xl font-semibold text-slate-600 mb-6">Payment Line Items</h2>
            <div class="overflow-x-auto">
                <table class="w-full min-w-max text-left">
                    <thead class="border-b border-slate-300">
                        <tr>
                            <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">GRN No.</th>
                            <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider">PO No.</th>
                            <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">Original Due</th>
                            <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">Paid Amount</th>
                            <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-right">Remaining Due</th>
                            <th class="px-4 py-3 text-sm font-semibold text-slate-600 uppercase tracking-wider text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentItemsTableBody">
                        <tr id="noPaymentItemsRow">
                            <td class="px-4 py-10 text-center text-slate-500" colspan="6">No items added to this payment yet. Select a supplier and add GRNs.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="w-full sm:w-auto">
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="paymentMethod">Payment Method</label>
                    <select class="w-full sm:w-auto appearance-none px-4 py-2 border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-150 bg-white" 
                            id="paymentMethod" name="paymentMethod">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="cash">Cash</option>
                        <option value="online">Online Payment</option>
                    </select>
                </div>
                <div class="text-right w-full sm:w-auto">
                    <p class="text-xl font-bold text-slate-700">Total Payment: <span class="text-emerald-600" id="totalPaymentAmount">$0.00</span></p>
                </div>
            </div>
        </div>
        
        <div class="mt-10 flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
            <button class="px-6 py-3 bg-slate-200 text-slate-700 rounded-lg shadow-md hover:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition duration-150" 
                    type="button" id="cancelButton">
                Cancel
            </button>
            <button class="px-6 py-3 bg-emerald-500 text-white rounded-lg shadow-md hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition duration-150 flex items-center justify-center" 
                    disabled form="paymentForm" id="processPaymentButton" type="submit">
                <span class="material-icons mr-2 align-middle">payment</span>
                Process Payment
            </button>
        </div>
    </main>
</div>
@endsection