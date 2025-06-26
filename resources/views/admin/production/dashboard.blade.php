@extends('layouts.admin')

@section('title', 'Kitchen Production Management')
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
                ['name' => 'Production Recipes', 'link' => route('admin.production.recipes.index')],
                ['name' => 'Ingredient Management', 'link' => '#', 'disabled' => true],
            ]" active="Production" />
        </div>


        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Production Requests --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Production Requests</h2>
                    <p class="text-gray-500 text-sm">Manage branch production requests and approvals</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.production.requests.index') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-400 transition text-white px-4 py-2 rounded-lg flex items-center shadow-sm">
                        <i class="fas fa-file-alt mr-2"></i> View All Requests
                    </a>
                    <a href="{{ route('admin.production.requests.create') }}"
                        class="bg-white border border-indigo-600 text-indigo-600 hover:bg-indigo-50 focus:ring-2 focus:ring-indigo-200 transition px-4 py-2 rounded-lg flex items-center shadow-sm">
                        <i class="fas fa-plus mr-2"></i> New Request
                    </a>
                    <a href="{{ route('admin.production.requests.manage') }}"
                        class="bg-indigo-50 border border-indigo-500 text-indigo-700 hover:bg-indigo-100 focus:ring-2 focus:ring-indigo-200 transition px-4 py-2 rounded-lg flex items-center shadow-sm">
                        <i class="fas fa-cogs mr-2"></i> Manage Requests
                    </a>
                </div>
            </div>

            {{-- Production Orders --}}
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

            {{-- Production Sessions --}}
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

            {{-- Production Recipes --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Recipe Management</h2>
                    <p class="text-gray-500 text-sm">Manage production recipes and bill of materials</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.production.recipes.index') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-book mr-2"></i> View All Recipes
                    </a>
                    <a href="{{ route('admin.production.recipes.create') }}"
                        class="bg-white border border-blue-500 text-blue-500 hover:bg-blue-50 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Recipe
                    </a>
                </div>
            </div>

            {{-- Ingredient Management --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4 opacity-60 pointer-events-none select-none">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Ingredient Management</h2>
                    <p class="text-gray-500 text-sm">Track ingredient usage and requirements</p>
                </div>
                <div class="flex gap-2">
                    <a href="#" tabindex="-1"
                        class="bg-purple-500 text-white px-4 py-2 rounded-lg flex items-center cursor-not-allowed">
                        <i class="fas fa-leaf mr-2"></i> View Ingredients
                    </a>
                    <a href="#" tabindex="-1"
                        class="bg-white border border-purple-500 text-purple-500 px-4 py-2 rounded-lg flex items-center cursor-not-allowed">
                        <i class="fas fa-plus mr-2"></i> Add Ingredient
                    </a>
                </div>
            </div>
        </div>


    </div>


@endsection
