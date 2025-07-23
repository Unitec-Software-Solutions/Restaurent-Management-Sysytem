@props([
    'searchValue' => '',
    'statusOptions' => [],
    'selectedStatus' => '',
    'branches' => [],
    'selectedBranch' => '',
    'showBranchFilter' => true,
    'showStatusFilter' => true,
    'showDateRange' => true,
    'customFilters' => []
])

<div class="bg-white shadow-sm rounded-lg p-4 mb-6">
    <form method="GET" class="space-y-4">
        <!-- Preserve existing query parameters -->
        @foreach(request()->except(['search', 'status', 'branch_id', 'start_date', 'end_date']) as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $searchValue }}"
                    placeholder="Search records..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                >
            </div>

            <!-- Status Filter -->
            @if($showStatusFilter && !empty($statusOptions))
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select
                        id="status"
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                        <option value="">All Statuses</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedStatus === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <!-- Date Range -->
            @if($showDateRange)
                <div class="grid grid-cols-2 gap-2">
                    <div id="date-range-picker" date-rangepicker >
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input
                            datepicker datepicker-buttons datepicker-autoselect-today datepicker-format="yyyy-mm-dd"
                            type="text"
                            id="start_date"
                            name="start_date"
                            value="{{ request('start_date', now()->subDays(30)->toDateString()) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            autocomplete="off"
                            >
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input
                            datepicker datepicker-buttons datepicker-autoselect-today datepicker-format="yyyy-mm-dd"
                            type="text"
                            id="end_date"
                            name="end_date"
                            value="{{ request('end_date', now()->toDateString()) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            autocomplete="off"
                            >
                    </div>
                </div>
            @endif


            <!-- Branch Filter -->
            @if($showBranchFilter && !empty($branches))
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select
                        id="branch_id"
                        name="branch_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($selectedBranch == $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Custom Filters -->
            @foreach($customFilters as $filter)
                <div>
                    <label for="{{ $filter['name'] }}" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $filter['label'] }}
                    </label>
                    @if($filter['type'] === 'select')
                        <select
                            id="{{ $filter['name'] }}"
                            name="{{ $filter['name'] }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                            <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                            @foreach($filter['options'] as $value => $label)
                                <option value="{{ $value }}" @selected(request($filter['name']) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input
                            type="{{ $filter['type'] ?? 'text' }}"
                            id="{{ $filter['name'] }}"
                            name="{{ $filter['name'] }}"
                            value="{{ request($filter['name']) }}"
                            placeholder="{{ $filter['placeholder'] ?? '' }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2 justify-between items-center">
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
                >
                    <i class="fa-solid fa-filter mr-2"></i>
                    Filter
                </button>

                <a
                    href="{{ request()->url() }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center transition-colors"
                >
                    <i class="fas fa-undo mr-2"></i>
                    Reset
                </a>
            </div>

            <!-- Export Buttons -->
            <div class="flex gap-2">
                @can('export_data')
                    <!-- Excel Export -->
                    <button
                        type="submit"
                        name="export"
                        value="excel"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
                        title="Export to Excel"
                    >
                        <i class="fas fa-file-excel mr-2"></i>
                        Excel
                    </button>

                    <!-- CSV Export -->
                    <button
                        type="submit"
                        name="export"
                        value="csv"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
                        title="Export to CSV"
                    >
                        <i class="fas fa-file-csv mr-2"></i>
                        CSV
                    </button>
                @endcan
            </div>
        </div>
    </form>

    <!-- Progress Indicator (hidden by default) -->
    <div id="export-progress" class="hidden mt-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-3"></div>
                <div>
                    <p class="text-blue-800 font-medium">Preparing export...</p>
                    <p class="text-blue-600 text-sm">This may take a few moments for large datasets.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show progress indicator for exports
    const exportButtons = document.querySelectorAll('button[name="export"]');
    const progressDiv = document.getElementById('export-progress');

    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            progressDiv.classList.remove('hidden');
        });
    });
});
</script>
