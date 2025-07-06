{{-- resources/views/admin/subscription-plans/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('content')
<div class="container-fluid py-6">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Subscription Plans</h1>
                <p class="text-gray-600 mt-1">Manage subscription tiers and module access</p>
            </div>
            @if(auth('admin')->user()->isSuperAdmin())
                <a href="{{ route('admin.subscription-plans.create') }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Create Plan
                </a>
            @endif
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <!-- Plan Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div class="text-3xl font-bold text-gray-900 mb-1">
                        {{ $plan->currency ?? 'USD' }} {{ number_format($plan->price, 2) }}
                        <span class="text-sm font-normal text-gray-500">/month</span>
                    </div>
                    
                    @if($plan->description)
                        <p class="text-sm text-gray-600">{{ $plan->description }}</p>
                    @endif
                </div>

                <!-- Plan Stats -->
                <div class="px-6 py-4 bg-gray-50">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-indigo-600">{{ $plan->organizations_count ?? 0 }}</div>
                            <div class="text-xs text-gray-500">Organizations</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">{{ $plan->active_subscriptions_count ?? 0 }}</div>
                            <div class="text-xs text-gray-500">Active Subs</div>
                        </div>
                    </div>
                </div>

                <!-- Modules -->
                <div class="p-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Included Modules</h4>
                    <div class="space-y-2">
                        @forelse($plan->getModulesWithNames() as $module)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ $module['name'] }}</span>
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ ($module['tier'] ?? 'basic') === 'enterprise' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ ($module['tier'] ?? 'basic') === 'premium' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ ($module['tier'] ?? 'basic') === 'basic' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst($module['tier'] ?? 'basic') }}
                                </span>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No modules selected</div>
                        @endforelse
                    </div>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex gap-2">
                        <a href="{{ route('admin.subscription-plans.show', $plan) }}" 
                           class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-2 rounded text-sm">
                            View
                        </a>
                        @if(auth('admin')->user()->isSuperAdmin())
                            <a href="{{ route('admin.subscription-plans.edit', $plan) }}" 
                               class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm">
                                Edit
                            </a>
                            @if(($plan->organizations_count ?? 0) == 0)
                                <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST" 
                                      class="flex-1" onsubmit="return confirm('Are you sure you want to delete this plan?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <button type="button" 
                                        disabled
                                        title="Cannot delete plan with {{ $plan->organizations_count }} organizations"
                                        class="flex-1 bg-gray-400 text-gray-600 px-3 py-2 rounded text-sm cursor-not-allowed">
                                    Delete
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12">
                    <div class="text-gray-400 text-5xl mb-4">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No subscription plans</h3>
                    <p class="text-gray-500 max-w-md mx-auto">
                        Get started by creating your first subscription plan.
                    </p>
                    <div class="mt-6">
                        @if(auth('admin')->user()->isSuperAdmin())
                            <a href="{{ route('admin.subscription-plans.create') }}" 
                               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-plus mr-2"></i> Create Plan
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($plans->hasPages())
        <div class="mt-6">
            {{ $plans->links() }}
        </div>
    @endif
</div>
@endsection