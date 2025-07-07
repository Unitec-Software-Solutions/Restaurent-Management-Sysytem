<!DOCTYPE html>
<html>
<head>
    <title>Menu Items Test - Refined System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-4">Menu Items System Test</h1>
            
            <!-- System Explanation -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h2 class="font-semibold text-blue-900 mb-2">System Overview</h2>
                <div class="text-sm text-blue-800 space-y-1">
                    <div><i class="fas fa-boxes text-blue-600 mr-2"></i><strong>Buy & Sell Items:</strong> From Item Master (inventory tracked)</div>
                    <div><i class="fas fa-utensils text-orange-600 mr-2"></i><strong>KOT Recipes:</strong> Dishes made from ingredients (recipes)</div>
                </div>
            </div>

            <!-- Controls -->
            <div class="flex gap-4 mb-6">
                <button onclick="loadMenuItems()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    <i class="fas fa-refresh mr-2"></i>Load Menu Items
                </button>
                <select id="typeFilter" onchange="filterByType()" class="px-3 py-2 border rounded">
                    <option value="">All Types</option>
                    <option value="1">Buy & Sell Only</option>
                    <option value="2">KOT Recipes Only</option>
                </select>
            </div>

            <!-- Loading State -->
            <div id="loading" class="hidden text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-500"></i>
                <p class="mt-2 text-gray-600">Loading menu items...</p>
            </div>

            <!-- Results -->
            <div id="results" class="space-y-4"></div>
        </div>
    </div>

    <script>
        let allMenuItems = [];

        async function loadMenuItems() {
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            
            loading.classList.remove('hidden');
            results.innerHTML = '';

            try {
                const response = await fetch('/admin/menu-items/all-items');
                const data = await response.json();

                if (data.success) {
                    allMenuItems = data.items;
                    displayMenuItems(allMenuItems);
                    
                    // Show summary
                    const summary = document.createElement('div');
                    summary.className = 'bg-green-50 border border-green-200 rounded-lg p-4 mb-4';
                    summary.innerHTML = `
                        <h3 class="font-semibold text-green-900 mb-2">Summary</h3>
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div><span class="font-medium">Total:</span> ${data.total_count}</div>
                            <div><span class="font-medium">Buy & Sell:</span> ${data.buy_sell_count}</div>
                            <div><span class="font-medium">KOT Recipes:</span> ${data.kot_count}</div>
                        </div>
                    `;
                    results.insertBefore(summary, results.firstChild);
                } else {
                    results.innerHTML = `<div class="text-red-600">Error: ${data.message || 'Failed to load menu items'}</div>`;
                }
            } catch (error) {
                results.innerHTML = `<div class="text-red-600">Error: ${error.message}</div>`;
            } finally {
                loading.classList.add('hidden');
            }
        }

        function displayMenuItems(items) {
            const results = document.getElementById('results');
            const itemsContainer = document.createElement('div');
            itemsContainer.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4';

            if (items.length === 0) {
                itemsContainer.innerHTML = '<div class="col-span-full text-center text-gray-500 py-8">No menu items found</div>';
            } else {
                items.forEach(item => {
                    const typeClass = item.type === 1 ? 'border-blue-200 bg-blue-50' : 'border-orange-200 bg-orange-50';
                    const typeIcon = item.type === 1 ? 'fas fa-boxes text-blue-600' : 'fas fa-utensils text-orange-600';
                    const typeColor = item.type === 1 ? 'text-blue-800' : 'text-orange-800';

                    const itemElement = document.createElement('div');
                    itemElement.className = `border rounded-lg p-4 ${typeClass}`;
                    itemElement.innerHTML = `
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">${item.name}</h3>
                            <i class="${typeIcon}"></i>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">${item.description || 'No description'}</p>
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span>Type:</span>
                                <span class="${typeColor} font-medium">${item.type_name}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Source:</span>
                                <span class="text-gray-700">${item.source}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Price:</span>
                                <span class="font-medium">LKR ${parseFloat(item.price).toFixed(2)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Category:</span>
                                <span class="text-gray-700">${item.category}</span>
                            </div>
                            ${item.type === 1 ? `
                                <div class="flex justify-between">
                                    <span>Stock:</span>
                                    <span class="${item.current_stock > 0 ? 'text-green-600' : 'text-red-600'}">${item.current_stock}</span>
                                </div>
                            ` : `
                                <div class="flex justify-between">
                                    <span>Ingredients:</span>
                                    <span class="text-gray-700">${item.ingredient_count} items</span>
                                </div>
                            `}
                            <div class="flex justify-between">
                                <span>Available:</span>
                                <span class="${item.can_make ? 'text-green-600' : 'text-red-600'}">${item.can_make ? 'Yes' : 'No'}</span>
                            </div>
                        </div>
                    `;
                    itemsContainer.appendChild(itemElement);
                });
            }

            // Clear and append
            const existingContainer = results.querySelector('.grid');
            if (existingContainer) {
                existingContainer.remove();
            }
            results.appendChild(itemsContainer);
        }

        function filterByType() {
            const typeFilter = document.getElementById('typeFilter').value;
            let filteredItems = allMenuItems;

            if (typeFilter) {
                filteredItems = allMenuItems.filter(item => item.type == typeFilter);
            }

            displayMenuItems(filteredItems);
        }

        // Load on page load
        document.addEventListener('DOMContentLoaded', loadMenuItems);
    </script>
</body>
</html>
