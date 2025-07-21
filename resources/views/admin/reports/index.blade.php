@extends('layouts.admin')
@section('header-title', 'Reports')
@section('content')
<div >
    <div class="mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Reports - Sample Page</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Sales Report Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Sales Report</h2>
                <div class="h-64 bg-gray-100 rounded flex items-center justify-center">
                    <!-- Placeholder for chart -->
                    <p class="text-gray-500">Sales chart will be displayed here</p>
                </div>
            </div>

            <!-- Inventory Report Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Inventory Report</h2>
                <div class="h-64 bg-gray-100 rounded flex items-center justify-center">
                    <!-- Placeholder for chart -->
                    <p class="text-gray-500">Inventory chart will be displayed here</p>
                </div>
            </div>
        </div>

        <!-- Report Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Generate Custom Report</h2>
            <form>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                        <select class="w-full rounded-md border-gray-300 shadow-sm">
                            <option>Sales Report</option>
                            <option>Inventory Report</option>
                            <option>Customer Report</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
                <button type="submit" class="mt-4 bg-[#515DEF] text-white px-4 py-2 rounded-lg hover:bg-[#6A71F0] transition">
                    Generate Report
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
