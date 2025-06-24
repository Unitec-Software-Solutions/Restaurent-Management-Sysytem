@extends('layouts.admin')

@section('header-title', 'Kitchen Production Management')

@section('content')
    <div class="p-4 space-y-8">
        <!-- Navigation Buttons -->
        <div class="rounded-lg">
            <x-nav-buttons :items="[
                ['name' => 'Production', 'link' => route('admin.production.index')],
                ['name' => 'Production Requests', 'link' => route('admin.production.requests.index')],
                ['name' => 'Production Orders', 'link' => route('admin.production.orders.index')],
                ['name' => 'Production Sessions', 'link' => route('admin.production.sessions.index')],
            ]" active="Production" />
        </div>


        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Production Requests</h2>
                    <p class="text-gray-500 text-sm">Manage branch production requests and approvals</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.production.requests.index') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-file-alt mr-2"></i> View All Requests
                    </a>
                    <a href="{{ route('admin.production.requests.create') }}"
                        class="bg-white border border-indigo-600 text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Request
                    </a>
                    <a href="{{ route('admin.production.requests.manage') }}"
                        class="bg-white border border-green-600 text-green-600 hover:bg-green-50 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-cogs mr-2"></i>Manage Requests
                    </a>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Production Orders</h2>
                    <p class="text-gray-500 text-sm">Manage kitchen production orders and operations</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.production.orders.index') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-box mr-2"></i> View All Orders
                    </a>
                    <a href="{{ route('admin.production.orders.aggregate') }}"
                        class="bg-white border border-green-600 text-green-600 hover:bg-green-50 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i>New Order
                    </a>

                </div>
            </div>


            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Production Sessions</h2>
                    <p class="text-gray-500 text-sm">Monitor and manage active production sessions</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.production.sessions.index') }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-clock mr-2"></i> View All Sessions
                    </a>
                    <a href="{{ route('admin.production.sessions.create') }}"
                        class="bg-white border border-yellow-500 text-yellow-500 hover:bg-yellow-50 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Session
                    </a>
                </div>
            </div>


        </div>


    </div>


@endsection
