@extends('errors.generic')

@section('title', $errorTitle ?? 'Access Restricted')

@section('content')
<div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full {{ $iconBgClass ?? 'bg-orange-100' }}">
                    <i class="{{ $mainIcon ?? 'fas fa-lock' }} {{ $iconColor ?? 'text-orange-500' }}"></i>
                </div>
                <h1 class="mt-3 text-2xl font-bold text-gray-900">
                    {{ $errorHeading ?? 'Permission Required' }}
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $errorCode ?? '403' }} - Access Restricted
                </p>
            </div>

            {{-- Function Information --}}
            @if(isset($functionName))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-blue-800">Function Information</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p><strong>Function:</strong> {{ $functionName }}</p>
                            @if(isset($permission))
                                <p><strong>Required Permission:</strong> <code class="bg-blue-100 px-2 py-1 rounded">{{ $permission }}</code></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Admin Information --}}
            @if(isset($adminLevel))
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-user text-gray-500 mt-1 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-gray-800">Your Access Level</h3>
                        <div class="mt-2 text-sm text-gray-600">
                            <p><strong>Level:</strong> 
                                @switch($adminLevel)
                                    @case('super_admin')
                                        <span class="text-purple-600 font-semibold">Super Administrator</span>
                                        @break
                                    @case('org_admin')
                                        <span class="text-blue-600 font-semibold">Organization Administrator</span>
                                        @break
                                    @case('branch_admin')
                                        <span class="text-green-600 font-semibold">Branch Administrator</span>
                                        @break
                                    @default
                                        <span class="text-gray-600 font-semibold">Staff Member</span>
                                @endswitch
                            </p>
                            @if(isset($admin))
                                @if(isset($organizationName))
                                    <p><strong>Organization:</strong> {{ $organizationName }}</p>
                                @endif
                                @if(isset($branchName))
                                    <p><strong>Branch:</strong> {{ $branchName }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Main Message --}}
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-orange-500 mt-1 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-orange-800">Access Policy</h3>
                        <div class="mt-2 text-sm text-orange-700">
                            <p>{{ $errorMessage ?? 'This function is visible for system awareness but requires additional permissions to access.' }}</p>
                            
                            <div class="mt-3">
                                <p><strong>Why can I see this function?</strong></p>
                                <p class="mt-1">All system functions are displayed to administrators so you can understand the full capabilities of the system. However, access is controlled based on your permission level.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            @if(isset($contactInfo))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-phone text-green-500 mt-1 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-green-800">Request Access</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>To request access to this function, contact:</p>
                            <div class="mt-2">
                                {!! $contactInfo !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-3">
                <button onclick="history.back()" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $buttonClass ?? 'bg-indigo-600 hover:bg-indigo-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Go Back
                </button>
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
            </div>

            {{-- Debug Information (Development Only) --}}
            @if(config('app.debug') && isset($permission))
            <div class="mt-6 pt-4 border-t border-gray-200">
                <details class="text-xs text-gray-500">
                    <summary class="cursor-pointer hover:text-gray-700">Debug Information</summary>
                    <div class="mt-2 bg-gray-100 p-2 rounded">
                        <pre>{{ json_encode([
                            'permission' => $permission ?? 'N/A',
                            'admin_level' => $adminLevel ?? 'N/A',
                            'route' => request()->route()?->getName(),
                            'url' => request()->fullUrl(),
                            'timestamp' => now()->toISOString(),
                        ], JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </details>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Log permission denial for administrators
console.log('Permission Denied:', {
    function: '{{ $functionName ?? "Unknown" }}',
    permission: '{{ $permission ?? "N/A" }}',
    admin_level: '{{ $adminLevel ?? "N/A" }}',
    url: window.location.href,
    timestamp: new Date().toISOString()
});

// Auto-redirect after 30 seconds (optional)
setTimeout(function() {
    if (confirm('Would you like to return to the dashboard?')) {
        window.location.href = '{{ route("admin.dashboard") }}';
    }
}, 30000);
</script>
@endsection
