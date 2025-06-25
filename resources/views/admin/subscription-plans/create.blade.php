@extends('layouts.admin')
@section('title', 'Create Subscription Plan')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Subscription Plan</h1>
            <p class="text-gray-600">Set up a new subscription plan with modules and pricing</p>
        </div>
        <a href="{{ route('admin.subscription-plans.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Plans
        </a>
    </div>

    <!-- Error Display -->
    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
            <h3 class="font-medium mb-2">Validation Errors</h3>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('admin.subscription-plans.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Basic Information -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                           placeholder="e.g., Basic Plan, Premium Plan" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency <span class="text-red-500">*</span></label>
                    <select name="currency" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="LKR" {{ old('currency', 'LKR') == 'LKR' ? 'selected' : '' }}>LKR - Sri Lankan Rupee</option>
                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                          placeholder="Brief description of what this plan includes...">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- Modules Selection -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Included Modules <span class="text-red-500">*</span></h3>
            
            @if($modules->isEmpty())
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-4">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <p class="text-gray-500">No modules available. Please create modules first.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($modules as $module)
                        <label class="flex items-start space-x-3 bg-white p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition">
                            <input type="checkbox" name="modules[]" value="{{ $module->id }}" 
                                   class="mt-1 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                   {{ in_array($module->id, old('modules', [])) ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $module->name }}</div>
                                @if($module->description)
                                    <div class="text-sm text-gray-500 mt-1">{{ $module->description }}</div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Pricing and Limits -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Pricing & Limits</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price <span class="text-red-500">*</span></label>
                    <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="0.00" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Branches</label>
                    <input type="number" name="max_branches" value="{{ old('max_branches') }}" min="1" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Unlimited">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for unlimited</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Employees</label>
                    <input type="number" name="max_employees" value="{{ old('max_employees') }}" min="1" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Unlimited">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for unlimited</p>
                </div>
            </div>
        </div>

        <!-- Trial Options -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Trial Options</h3>
            
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_trial" value="1" 
                           class="text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                           {{ old('is_trial') ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Enable trial period</span>
                </label>
                
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-700">Trial Days:</label>
                    <input type="number" name="trial_period_days" value="{{ old('trial_period_days', 30) }}" 
                           min="1" max="365" 
                           class="w-20 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                </div>
            </div>
        </div>

        <!-- Status Options -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Status</h3>
            
            <div class="flex items-center">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           class="text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Plan is active and available for subscription</span>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 pt-6">
            <a href="{{ route('admin.subscription-plans.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                Cancel
            </a>
            <button type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-save mr-2"></i> Create Plan
            </button>
        </div>
    </form>
</div>
@endsection


