@extends('layouts.admin')

@section('content')
<div>
    <div class="p-4 rounded-lg">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Customer Management - Sample Page</h1>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Sample data rows -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Michael Johnson</td>
                            <td class="px-6 py-4 whitespace-nowrap">michael@example.com</td>
                            <td class="px-6 py-4 whitespace-nowrap">(555) 123-4567</td>
                            <td class="px-6 py-4 whitespace-nowrap">12</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-[#515DEF] hover:text-[#6A71F0] mr-3">View</a>
                                <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                        <!-- More rows would go here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


