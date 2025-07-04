@extends('layouts.admin')

@section('title', 'Subscription Plan Details')

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
                <h1 class="text-2xl font-bold text-gray-900">{{ $subscriptionPlan->name }}</h1>
                <p class="text-gray-600 mt-1">Subscription plan details and analytics</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.subscription-plans.edit', $subscriptionPlan) }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Plan
                </a>
                <form action="{{ route('admin.subscription-plans.destroy', $subscriptionPlan) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this subscription plan? This action cannot be undone.')"
                      class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-trash mr-2"></i>
                        Delete Plan
                    </button>
                </form>
                <a href="{{ route('admin.subscription-plans.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Plans
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Plan Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Plan Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Plan Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $subscriptionPlan->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $subscriptionPlan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $subscriptionPlan->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Price</label>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $subscriptionPlan->currency ?? 'USD' }} {{ number_format($subscriptionPlan->price) }}
                            <span class="text-sm font-normal text-gray-500">/month</span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Currency</label>
                        <p class="text-lg text-gray-900">{{ $subscriptionPlan->currency ?? 'USD' }}</p>
                    </div>
                    
                    @if($subscriptionPlan->max_branches)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Max Branches</label>
                        <p class="text-lg text-gray-900">{{ $subscriptionPlan->max_branches }}</p>
                    </div>
                    @endif
                    
                    @if($subscriptionPlan->max_employees)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Max Employees</label>
                        <p class="text-lg text-gray-900">{{ $subscriptionPlan->max_employees }}</p>
                    </div>
                    @endif
                </div>
                
                @if($subscriptionPlan->description)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
                    <p class="text-gray-700">{{ $subscriptionPlan->description }}</p>
                </div>
                @endif
            </div>

            <!-- Included Modules -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Included Modules</h2>
                
                @php
                    $modules = $subscriptionPlan->getModulesWithNames();
                @endphp
                
                @if(!empty($modules))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($modules as $module)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-medium text-gray-900">{{ $module['name'] }}</h3>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ ($module['tier'] ?? 'basic') === 'enterprise' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ ($module['tier'] ?? 'basic') === 'premium' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ ($module['tier'] ?? 'basic') === 'basic' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($module['tier'] ?? 'basic') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-4xl mb-4">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <p class="text-gray-500">No modules assigned to this plan</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="space-y-6">
            <!-- Usage Statistics -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Usage Statistics</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Organizations</span>
                        <span class="text-2xl font-bold text-indigo-600">{{ $subscriptionPlan->organizations_count ?? 0 }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Active Subscriptions</span>
                        <span class="text-2xl font-bold text-green-600">{{ $subscriptionPlan->active_subscriptions_count ?? 0 }}</span>
                    </div>
                    
                    @if($subscriptionPlan->is_trial)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Trial Period</span>
                        <span class="text-lg font-semibold text-gray-900">{{ $subscriptionPlan->trial_period_days ?? 0 }} days</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Plan Features -->
            @if($subscriptionPlan->features && !empty($subscriptionPlan->features))
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Features</h2>
                
                <ul class="space-y-2">
                    @foreach($subscriptionPlan->features as $feature)
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span class="text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Trial Information -->
            @if($subscriptionPlan->is_trial)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center mb-2">
                    <i class="fas fa-clock text-yellow-600 mr-2"></i>
                    <h3 class="font-semibold text-yellow-800">Trial Plan</h3>
                </div>
                <p class="text-sm text-yellow-700">
                    This is a trial plan with a {{ $subscriptionPlan->trial_period_days ?? 0 }}-day trial period.
                </p>
            </div>
            @endif

            <!-- Plan Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                
                <div class="space-y-3">
                    @if(auth('admin')->user()->isSuperAdmin())
                        <a href="{{ route('admin.subscription-plans.edit', $subscriptionPlan) }}" 
                           class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Plan
                        </a>
                        
                        @if($subscriptionPlan->organizations_count == 0)
                        <form action="{{ route('admin.subscription-plans.destroy', $subscriptionPlan) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this subscription plan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Plan
                            </button>
                        </form>
                        @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <p class="text-xs text-gray-600 text-center">
                                Cannot delete plan with {{ $subscriptionPlan->organizations_count }} active organizations
                            </p>
                        </div>
                        @endif
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <p class="text-xs text-gray-600 text-center">
                                Only super administrators can edit or delete subscription plans
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
