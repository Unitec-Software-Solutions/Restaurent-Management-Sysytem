@extends('layouts.admin')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Menu Safety Dashboard</h1>
                <p class="text-gray-600">Monitor menu health, safety issues, and system integrity</p>
            </div>
            <div class="flex gap-3">
                <button id="refresh-safety-data" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh Data
                </button>
                <form method="POST" action="{{ route('admin.orders.archive-old-menus') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center" 
                            onclick="return confirm('This will archive old inactive menus. Continue?')">
                        <i class="fas fa-archive mr-2"></i> Archive Old Menus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Branch Selector -->
    <div class="mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Branch</label>
            <select id="branch-safety-select" class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Safety Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-shield-alt text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Active Menus</h3>
                    <p class="text-2xl font-bold text-gray-900" id="active-menus-count">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Safety Issues</h3>
                    <p class="text-2xl font-bold text-gray-900" id="safety-issues-count">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Unavailable Items</h3>
                    <p class="text-2xl font-bold text-gray-900" id="unavailable-items-count">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100">
                    <i class="fas fa-boxes text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Low Stock Items</h3>
                    <p class="text-2xl font-bold text-gray-900" id="low-stock-items-count">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Safety Issues Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Active Issues -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    Active Safety Issues
                </h2>
            </div>
            <div class="p-6">
                <div id="safety-issues-list" class="space-y-3">
                    <div class="text-center py-4">
                        <i class="fas fa-shield-check text-green-300 text-3xl mb-2"></i>
                        <p class="text-gray-500">Loading safety data...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Health Summary -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-heartbeat text-red-500 mr-2"></i>
                    Menu Health Summary
                </h2>
            </div>
            <div class="p-6">
                <div id="menu-health-chart" class="h-64">
                    <!-- Chart will be rendered here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Menu Actions -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-history text-blue-500 mr-2"></i>
                Recent Menu Actions
            </h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Menu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody id="recent-actions-table" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>
                                Loading recent actions...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Safety Issue Detail Modal -->
<div id="safety-issue-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Safety Issue Details</h3>
            <button id="close-safety-modal" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="safety-issue-content">
            <!-- Issue details will be loaded here -->
        </div>
        
        <div class="flex justify-end gap-3 mt-6">
            <button id="cancel-safety-action" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
            <button id="resolve-safety-issue" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Resolve Issue</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchSelect = document.getElementById('branch-safety-select');
    const refreshBtn = document.getElementById('refresh-safety-data');
    
    // Initial load
    loadSafetyData();
    
    // Event listeners
    branchSelect.addEventListener('change', loadSafetyData);
    refreshBtn.addEventListener('click', loadSafetyData);
    
    // Auto-refresh every 30 seconds
    setInterval(loadSafetyData, 30000);
    
    function loadSafetyData() {
        const branchId = branchSelect.value;
        const url = branchId 
            ? `{{ route('admin.orders.menu-safety-status') }}?branch_id=${branchId}`
            : `{{ route('admin.orders.menu-safety-status') }}?branch_id=all`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                updateSafetyStats(data);
                updateSafetyIssues(data);
                updateMenuHealthChart(data);
            })
            .catch(error => {
                console.error('Failed to load safety data:', error);
                showErrorMessage('Failed to load safety data');
            });
            
        loadRecentActions();
    }
    
    function updateSafetyStats(data) {
        document.getElementById('active-menus-count').textContent = data.active_menus_count || 0;
        document.getElementById('safety-issues-count').textContent = data.conflicts?.length || 0;
        document.getElementById('unavailable-items-count').textContent = data.unavailable_items_count || 0;
        document.getElementById('low-stock-items-count').textContent = data.low_stock_items_count || 0;
    }
    
    function updateSafetyIssues(data) {
        const issuesList = document.getElementById('safety-issues-list');
        
        if (!data.conflicts || data.conflicts.length === 0) {
            issuesList.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-shield-check text-green-300 text-3xl mb-2"></i>
                    <p class="text-gray-500">No active safety issues</p>
                </div>`;
            return;
        }
        
        let issuesHtml = '';
        data.conflicts.forEach(issue => {
            issuesHtml += `
                <div class="flex items-start p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-yellow-800">${issue}</p>
                        <div class="mt-2">
                            <button class="text-xs text-yellow-700 hover:text-yellow-900 underline" 
                                    onclick="showIssueDetails('${encodeURIComponent(issue)}')">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>`;
        });
        
        issuesList.innerHTML = issuesHtml;
    }
    
    function updateMenuHealthChart(data) {
        // Simple bar chart representation
        const chartContainer = document.getElementById('menu-health-chart');
        const total = data.total_items_count || 1;
        const available = total - (data.unavailable_items_count || 0);
        const lowStock = data.low_stock_items_count || 0;
        
        const availablePercent = (available / total * 100).toFixed(1);
        const unavailablePercent = ((data.unavailable_items_count || 0) / total * 100).toFixed(1);
        const lowStockPercent = (lowStock / total * 100).toFixed(1);
        
        chartContainer.innerHTML = `
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>Available Items</span>
                        <span>${availablePercent}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: ${availablePercent}%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>Unavailable Items</span>
                        <span>${unavailablePercent}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: ${unavailablePercent}%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>Low Stock Items</span>
                        <span>${lowStockPercent}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-orange-500 h-2 rounded-full" style="width: ${lowStockPercent}%"></div>
                    </div>
                </div>
                
                <div class="pt-2 border-t">
                    <p class="text-sm text-gray-600">Total Items: ${total}</p>
                </div>
            </div>`;
    }
    
    function loadRecentActions() {
        // Mock recent actions for now - replace with actual endpoint
        const tableBody = document.getElementById('recent-actions-table');
        
        // Simulate loading recent menu actions
        setTimeout(() => {
            tableBody.innerHTML = `
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">2 minutes ago</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activated</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">Lunch Menu</td>
                    <td class="px-4 py-3 text-sm text-gray-900">Main Branch</td>
                    <td class="px-4 py-3 text-sm text-gray-500">Automatic schedule activation</td>
                </tr>
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">15 minutes ago</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Item Disabled</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">Breakfast Menu</td>
                    <td class="px-4 py-3 text-sm text-gray-900">Downtown Branch</td>
                    <td class="px-4 py-3 text-sm text-gray-500">Pancakes - Out of stock</td>
                </tr>`;
        }, 1000);
    }
    
    function showIssueDetails(issue) {
        const modal = document.getElementById('safety-issue-modal');
        const content = document.getElementById('safety-issue-content');
        
        content.innerHTML = `
            <div class="space-y-4">
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <h4 class="font-medium text-yellow-800">Issue Description</h4>
                    <p class="text-sm text-yellow-700 mt-1">${decodeURIComponent(issue)}</p>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">Recommended Actions</h4>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Review menu configuration and validity dates</li>
                        <li>Check item availability and stock levels</li>
                        <li>Verify menu activation schedule</li>
                        <li>Contact system administrator if issue persists</li>
                    </ul>
                </div>
            </div>`;
        
        modal.classList.remove('hidden');
    }
    
    // Modal event handlers
    document.getElementById('close-safety-modal').addEventListener('click', () => {
        document.getElementById('safety-issue-modal').classList.add('hidden');
    });
    
    document.getElementById('cancel-safety-action').addEventListener('click', () => {
        document.getElementById('safety-issue-modal').classList.add('hidden');
    });
    
    function showErrorMessage(message) {
        // Create error toast
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50';
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                ${message}
                <button class="ml-4 text-red-500 hover:text-red-700" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>`;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 5000);
    }
    
    // Real-time updates if available
    if (window.menuRealtimeManager) {
        window.menuRealtimeManager.onMenuUpdate((action, menu) => {
            loadSafetyData(); // Refresh data when menus change
        });
    }
});
</script>
@endsection
