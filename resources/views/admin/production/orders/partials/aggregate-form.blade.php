<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Aggregate Production Orders</h2>
        <p class="text-sm text-gray-600 mt-1">Create production orders from approved requests</p>
    </div>

    <div class="p-6">
        @if (isset($approvedRequests) && $approvedRequests->count() > 0)
            <form action="{{ route('admin.production.orders.store') }}" method="POST"
                id="aggregateProductionForm">
                @csrf

                <!-- Production Date -->
                <div class="mb-6">
                    <label for="production_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Production Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="production_date" name="production_date"
                        value="{{ old('production_date', now()->addDay()->format('Y-m-d')) }}"
                        min="{{ now()->format('Y-m-d') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <!-- Selected Requests -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selected Requests</label>
                    <div id="selectedRequestsList" class="border border-gray-200 rounded-lg p-4 min-h-24">
                        <p class="text-gray-500 text-sm">No requests selected. Please select requests from the "Approved
                            Requests" tab.</p>
                    </div>
                </div>

                <!-- Production Notes -->
                <div class="mb-6">
                    <label for="production_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Production Notes
                    </label>
                    <textarea id="production_notes" name="production_notes" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Add any special instructions for the production team...">{{ old('production_notes') }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3">
                    <button type="button" id="previewIngredientsBtn"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-eye mr-2"></i>Preview Ingredients
                    </button>
                    <button type="submit" id="createProductionOrderBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200"
                        disabled>
                        <i class="fas fa-plus mr-2"></i>Create Production Order
                    </button>
                </div>

                <!-- Hidden inputs for selected requests -->
                <div id="hiddenRequestInputs"></div>
            </form>

            <!-- Ingredient Preview Modal -->
            <div id="ingredientPreviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-lg max-w-4xl w-full max-h-96 overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Ingredient Requirements Preview</h3>
                                <button type="button" id="closePreviewModal" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-6 overflow-y-auto max-h-80">
                            <div id="ingredientPreviewContent">
                                <!-- Ingredient preview will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-box text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900">No Approved Requests</h3>
                <p class="text-gray-500">Approved production requests will appear here for aggregation.</p>
                <a href="{{ route('admin.production.requests.manage') }}"
                    class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-900">
                    <i class="fas fa-arrow-left mr-2"></i>Go to Request Management
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        // This script will handle the aggregate functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectedRequestsList = document.getElementById('selectedRequestsList');
            const hiddenRequestInputs = document.getElementById('hiddenRequestInputs');
            const createOrderBtn = document.getElementById('createProductionOrderBtn');

            // Function to update selected requests display
            window.updateSelectedRequests = function(selectedRequests) {
                if (selectedRequests.length === 0) {
                    selectedRequestsList.innerHTML =
                        '<p class="text-gray-500 text-sm">No requests selected. Please select requests from the "Approved Requests" tab.</p>';
                    hiddenRequestInputs.innerHTML = '';
                    createOrderBtn.disabled = true;
                    return;
                }

                // Update display
                let html = '';
                selectedRequests.forEach(requestId => {
                    html +=
                        `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2 mb-2">Request #${requestId}</span>`;
                });
                selectedRequestsList.innerHTML = html;

                // Update hidden inputs
                let hiddenHtml = '';
                selectedRequests.forEach(requestId => {
                    hiddenHtml +=
                        `<input type="hidden" name="selected_requests[]" value="${requestId}">`;
                });
                hiddenRequestInputs.innerHTML = hiddenHtml;

                createOrderBtn.disabled = false;
            };

            // Preview ingredients functionality
            document.getElementById('previewIngredientsBtn')?.addEventListener('click', function() {
                const selectedRequests = Array.from(document.querySelectorAll(
                    'input[name="selected_requests[]"]')).map(input => input.value);

                if (selectedRequests.length === 0) {
                    alert('Please select at least one request first.');
                    return;
                }

                // Show modal with loading
                document.getElementById('ingredientPreviewModal').classList.remove('hidden');
                document.getElementById('ingredientPreviewContent').innerHTML =
                    '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i><p class="text-gray-500 mt-2">Loading ingredients...</p></div>';

                // Here you would make an AJAX call to get ingredient requirements
                // For now, we'll just show a placeholder
                setTimeout(() => {
                    document.getElementById('ingredientPreviewContent').innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500">Ingredient calculation feature will be implemented here.</p>
                        <p class="text-sm text-gray-400 mt-2">This will show the calculated ingredient requirements based on recipes.</p>
                    </div>
                `;
                }, 1000);
            });

            // Close modal
            document.getElementById('closePreviewModal')?.addEventListener('click', function() {
                document.getElementById('ingredientPreviewModal').classList.add('hidden');
            });
        });
    </script>
@endpush
