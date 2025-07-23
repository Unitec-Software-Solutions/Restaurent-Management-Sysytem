@extends('layouts.admin')

@section('content')
<div class="mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-800 px-6 py-4">
                <div class="flex items-center">
                    <div class="bg-white/20 p-3 rounded-xl mr-4">
                        <i class="fas fa-shopping-bag text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Select Takeaway Order Type</h1>
                        <p class="text-green-100 mt-1">Choose how you want to create the takeaway order</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="p-8">
                <div class="space-y-6">
                    <!-- Scheduled Takeaway Option -->
                    <div class="border border-gray-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer"
                         onclick="selectOrderType('scheduled')">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-4 rounded-xl mr-4">
                                <i class="fas fa-clock text-blue-600 text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800">Scheduled Takeaway</h3>
                                <p class="text-gray-600 mt-1">Customer orders in advance with pickup time</p>
                                <ul class="text-sm text-gray-500 mt-2 list-disc pl-5">
                                    <li>Customer selects future pickup time</li>
                                    <li>Order prepared just before pickup</li>
                                    <li>Reduced wait time for customer</li>
                                    <li>Better kitchen planning</li>
                                </ul>
                            </div>
                            <div class="text-blue-600">
                                <i class="fas fa-chevron-right text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Demand Takeaway Option -->
                    <div class="border border-gray-200 rounded-xl p-6 hover:border-orange-300 hover:shadow-md transition-all cursor-pointer"
                         onclick="selectOrderType('demand')">
                        <div class="flex items-center">
                            <div class="bg-orange-100 p-4 rounded-xl mr-4">
                                <i class="fas fa-bolt text-orange-600 text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800">On-Demand Takeaway</h3>
                                <p class="text-gray-600 mt-1">Immediate order, prepare and pickup now</p>
                                <ul class="text-sm text-gray-500 mt-2 list-disc pl-5">
                                    <li>Order placed and prepared immediately</li>
                                    <li>Customer waits or comes back later</li>
                                    <li>Good for walk-in customers</li>
                                    <li>Quick decision making</li>
                                </ul>
                            </div>
                            <div class="text-orange-600">
                                <i class="fas fa-chevron-right text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Pre-order Option -->
                    <div class="border border-gray-200 rounded-xl p-6 hover:border-purple-300 hover:shadow-md transition-all cursor-pointer"
                         onclick="selectOrderType('preorder')">
                        <div class="flex items-center">
                            <div class="bg-purple-100 p-4 rounded-xl mr-4">
                                <i class="fas fa-calendar-plus text-purple-600 text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800">Pre-Order Takeaway</h3>
                                <p class="text-gray-600 mt-1">Order for future date with advance payment</p>
                                <ul class="text-sm text-gray-500 mt-2 list-disc pl-5">
                                    <li>Order placed days in advance</li>
                                    <li>Payment collected upfront</li>
                                    <li>Guaranteed availability</li>
                                    <li>Special event catering</li>
                                </ul>
                            </div>
                            <div class="text-purple-600">
                                <i class="fas fa-chevron-right text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Features Notice -->
                <div class="mt-8 bg-gray-50 rounded-xl p-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-user-shield mr-2 text-blue-600"></i>
                        Admin Features Available
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span class="text-gray-600">Pre-fill customer details</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span class="text-gray-600">Branch-specific inventory</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span class="text-gray-600">Real-time stock validation</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span class="text-gray-600">Advanced order tracking</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span class="text-gray-600">Payment processing</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span class="text-gray-600">KOT generation</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between items-center mt-8">
                    <a href="{{ route('admin.orders.dashboard') }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                    </a>

                    <div id="selected-type-info" class="text-center hidden">
                        <p class="text-gray-600 text-sm">Selected: <span id="selected-type-name" class="font-semibold"></span></p>
                        <button onclick="proceedWithSelection()"
                                class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-arrow-right mr-2"></i> Proceed
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedOrderType = null;

const orderTypes = {
    'scheduled': {
        name: 'Scheduled Takeaway',
        route: '{{ route("admin.orders.takeaway.create") }}?type=scheduled',
        description: 'Customer orders in advance with pickup time'
    },
    'demand': {
        name: 'On-Demand Takeaway',
        route: '{{ route("admin.orders.takeaway.create") }}?type=demand',
        description: 'Immediate order, prepare and pickup now'
    },
    'preorder': {
        name: 'Pre-Order Takeaway',
        route: '{{ route("admin.orders.takeaway.create") }}?type=preorder',
        description: 'Order for future date with advance payment'
    }
};

function selectOrderType(type) {
    selectedOrderType = type;

    // Remove previous selections
    document.querySelectorAll('.border-gray-200').forEach(el => {
        el.classList.remove('border-blue-400', 'bg-blue-50', 'border-orange-400', 'bg-orange-50', 'border-purple-400', 'bg-purple-50');
        el.classList.add('border-gray-200');
    });

    // Highlight selected option
    const selectedElement = event.currentTarget;
    if (type === 'scheduled') {
        selectedElement.classList.remove('border-gray-200');
        selectedElement.classList.add('border-blue-400', 'bg-blue-50');
    } else if (type === 'demand') {
        selectedElement.classList.remove('border-gray-200');
        selectedElement.classList.add('border-orange-400', 'bg-orange-50');
    } else if (type === 'preorder') {
        selectedElement.classList.remove('border-gray-200');
        selectedElement.classList.add('border-purple-400', 'bg-purple-50');
    }

    // Show selection info
    document.getElementById('selected-type-info').classList.remove('hidden');
    document.getElementById('selected-type-name').textContent = orderTypes[type].name;
}

function proceedWithSelection() {
    if (selectedOrderType && orderTypes[selectedOrderType]) {
        window.location.href = orderTypes[selectedOrderType].route;
    }
}

// Auto-proceed on double click
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[onclick*="selectOrderType"]').forEach(el => {
        el.addEventListener('dblclick', function() {
            if (selectedOrderType) {
                proceedWithSelection();
            }
        });
    });
});
</script>
@endpush
