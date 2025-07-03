@extends('layouts.admin')

@section('title', 'Activate Organization')

@section('content')
<div class="max-w-2xl mx-auto mt-10 bg-white p-8 rounded-2xl shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">
            {{ $organization->is_active ? 'Manage' : 'Activate' }} Organization
        </h2>
        <a href="{{ route('admin.organizations.index') }}"
           class="inline-block bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 transition font-semibold">
            ← Back to Organizations
        </a>
    </div>

    <!-- Flash Messages -->
    @if(session('error'))
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded flex items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif
    
    @if(session('success'))
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Organization Details -->
    <div class="organization-card mb-6">
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-building {{ $organization->is_active ? 'text-green-500' : 'text-red-500' }} mr-2"></i>
                {{ $organization->name }}
            </h3>
            
            <div class="flex flex-wrap gap-2 text-sm">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                    <i class="fas fa-envelope mr-1 text-xs"></i>
                    {{ $organization->email }}
                </span>
                
                @if($organization->contact_person)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800">
                    <i class="fas fa-user mr-1 text-xs"></i>
                    {{ $organization->contact_person }}
                </span>
                @endif
                
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full {{ $organization->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    <i class="fas {{ $organization->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1 text-xs"></i>
                    {{ $organization->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg mb-4">
            <h4 class="font-medium text-gray-700 mb-2">Organization Details</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-500">Phone:</span>
                    <span class="font-medium">{{ $organization->phone ?? 'Not provided' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Created:</span>
                    <span class="font-medium">{{ $organization->created_at->format('M d, Y') }}</span>
                </div>
                @if($organization->activated_at)
                <div>
                    <span class="text-gray-500">Activated:</span>
                    <span class="font-medium">{{ $organization->activated_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
                <div>
                    <span class="text-gray-500">Activation Key:</span>
                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $organization->activation_key }}</span>
                </div>
            </div>
        </div>

        @if($organization->is_active)
            <!-- Deactivation Form -->
            <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded mb-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-orange-500 mr-2"></i>
                    <p class="text-orange-700">This organization is currently active. You can deactivate it if needed.</p>
                </div>
            </div>
            
            <form action="{{ route('admin.organizations.activate', $organization) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="activation_key" value="{{ $organization->activation_key }}">
                
                <div class="flex gap-3">
                    <button type="submit" onclick="return confirm('Are you sure you want to deactivate this organization? This will also deactivate all its branches.')"
                            class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-times mr-2"></i>Deactivate Organization
                    </button>
                </div>
            </form>
        @else
            <!-- Activation Form -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded mb-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <p class="text-blue-700">This organization is currently inactive. As a super admin, you can activate it.</p>
                </div>
            </div>
            
            <form action="{{ route('admin.organizations.activate', $organization) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="activate">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Activation Key <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="activation_key"
                            value="{{ $organization->activation_key }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            placeholder="Enter activation key or use the pre-filled one"
                            required
                        >
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-key text-gray-400"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        The activation key is pre-filled. You can modify it or use the existing one.
                    </p>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" 
                            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-semibold">
                        <i class="fas fa-check mr-2"></i>Activate Organization
                    </button>
                    <a href="{{ route('admin.organizations.show', $organization) }}" 
                       class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-semibold">
                        <i class="fas fa-eye mr-2"></i>View Details
                    </a>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection