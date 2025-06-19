@extends('layouts.admin')

@section('title', 'Activate Organization')

@section('content')
<div class="max-w-2xl mx-auto mt-10 bg-white p-8 rounded-2xl shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Activate Organization</h2>
        <a href="{{ route('admin.organizations.index') }}"
           class="inline-block bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 transition font-semibold">
            ← Back to Organizations
        </a>
    </div>

    <!-- Flash Messages -->
    <div id="flash-messages" class="mb-6">
        @if(session('error'))
            <div class="flash-message mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <div>{{ session('error') }}</div>
                <button onclick="this.parentElement.remove()" class="ml-auto text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
        
        @if(session('success'))
            <div class="flash-message mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <div>{{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="ml-auto text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
    </div>
    
    <!-- Organization List -->
    <div>
        @if(isset($organizations) && $organizations->count())
            @foreach($organizations as $organization)
                <div class="organization-card mb-6 pb-6 border-b border-gray-200 last:border-b-0 last:mb-0 last:pb-0 fade-in">
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-building {{ $organization->is_active ? 'text-blue-500' : 'text-gray-500' }} mr-2"></i>
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
                            
                            @if($organization->contact_person_designation)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-id-card mr-1 text-xs"></i>
                                {{ $organization->contact_person_designation }}
                            </span>
                            @endif
                            
                            @if($organization->contact_person_phone)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-phone mr-1 text-xs"></i>
                                {{ $organization->contact_person_phone }}
                            </span>
                            @endif
                            
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full {{ $organization->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas {{ $organization->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1 text-xs"></i>
                                {{ $organization->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    
                    @if($organization->is_active)
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                <p class="text-blue-700">This organization is already active.</p>
                            </div>
                        </div>
                    @else
                        <!-- Activation Form -->
                        <form action="{{ route('admin.organizations.activate.submit') }}" method="POST" class="space-y-4 mt-4">
                            @csrf
                            <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                            
                            <!-- Activation Key Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Activation Key <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        name="activation_key" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus focus:outline-none focus:ring-2 focus:ring-blue-200" 
                                        required 
                                        pattern=".{10,}" 
                                        placeholder="Enter 10-character activation key"
                                    >
                                    <button type="button" class="absolute right-3 top-2 text-gray-400 hover:text-blue-500" onclick="generateKey(this)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Minimum 10 characters required</p>
                                @error('activation_key')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit"
    style="background: linear-gradient(to right, #2563eb, #4f46e5); color: #fff;"
    class="px-6 py-2 rounded shadow font-semibold">
    Activate
</button>
                            </div>
                        </form>
                    @endif
                </div>
            @endforeach
        @else
            <!-- No Organizations -->
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                <p class="text-lg">No organizations found.</p>
            </div>
        @endif
    </div>
</div>

<style>
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .card-hover {
        transition: all 0.2s ease;
    }
    
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    
    .input-focus {
        transition: all 0.2s ease;
    }
    
    .input-focus:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Flash message auto-dismiss
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(msg => {
            setTimeout(() => {
                msg.classList.add('opacity-0', 'transition', 'duration-500');
                setTimeout(() => msg.remove(), 500);
            }, 5000);
        });
    });
    
    // Generate a sample activation key
    function generateKey(button) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let key = '';
        for (let i = 0; i < 10; i++) {
            key += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        const input = button.closest('.relative').querySelector('input[name="activation_key"]');
        input.value = key;
        
        // Show a temporary tooltip
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.remove('text-gray-400');
        button.classList.add('text-green-500');
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('text-green-500');
            button.classList.add('text-gray-400');
        }, 1000);
    }
</script>
@endsection