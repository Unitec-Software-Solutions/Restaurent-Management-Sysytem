<div class="bg-white rounded-xl shadow p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">Profit Calculator</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Price ($)</label>
                <input type="number" id="productPrice" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g. 29.99">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Cost ($)</label>
                <input type="number" id="productCost" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g. 12.50">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost ($)</label>
                <input type="number" id="shippingCost" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g. 3.99">
            </div>
        </div>
        <div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Amazon Fees (%)</label>
                <input type="number" id="amazonFees" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g. 15" value="15">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Sold</label>
                <input type="number" id="quantitySold" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g. 100">
            </div>
            <button id="calculateBtn" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                Calculate Profit
            </button>
        </div>
    </div>
    <div id="results" class="mt-6 hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-blue-800">Gross Revenue</p>
                <h3 id="grossRevenue" class="text-xl font-bold text-blue-900">$0.00</h3>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-green-800">Net Profit</p>
                <h3 id="netProfit" class="text-xl font-bold text-green-900">$0.00</h3>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-purple-800">Profit Margin</p>
                <h3 id="profitMargin" class="text-xl font-bold text-purple-900">0%</h3>
            </div>
        </div>
    </div>
</div>