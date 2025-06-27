@extends('layouts.app')

@section('title', 'Route Not Found')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-lg w-full">
        <!-- Error Icon -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-4">
                <i class="fas fa-route text-red-600 text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Route Not Found</h1>
            <p class="text-gray-600">
                The page you're looking for doesn't exist or has been moved.
            </p>
        </div>

        <!-- Error Details Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-start gap-3 mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 mb-1">Attempted Route</h3>
                    <p class="text-sm text-gray-600 font-mono bg-gray-50 px-2 py-1 rounded">
                        {{ strtoupper($method) }} /{{ $attempted_path }}
                    </p>
                </div>
            </div>

            {{-- @if(config('app.debug'))
                <div class="mt-4 p-3 bg-blue-50 rounded border-l-4 border-blue-400">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">
                        <i class="fas fa-bug mr-1"></i> Debug Information
                    </h4>
                    <div class="text-xs text-blue-800 space-y-1">
                        <div>Environment: {{ app()->environment() }}</div>
                        <div>Timestamp: {{ now()->format('Y-m-d H:i:s') }}</div>
                        <div>User Agent: {{ substr(request()->userAgent(), 0, 60) }}...</div>
                    </div>
                </div>
            @endif --}}
        </div>

        <!-- Route Suggestions -->
        @if(!empty($suggestions))
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Did you mean?
                </h3>
                <div class="space-y-2">
                    @foreach($suggestions as $suggestion)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $suggestion['name'] }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $suggestion['uri'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @foreach($suggestion['methods'] as $method)
                                    <span class="px-2 py-1 text-xs font-semibold rounded
                                        {{ $method === 'GET' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $method === 'POST' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $method === 'PUT' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $method === 'DELETE' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $method }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="space-y-3">
            <button onclick="history.back()" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Go Back
            </button>
            
            @if($is_admin)
                <a href="{{ route('admin.dashboard') }}" 
                   class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 text-center py-3 px-4 rounded-lg transition-colors">
                    <i class="fas fa-home mr-2"></i> Admin Dashboard
                </a>
            @else
                <a href="{{ route('home') }}" 
                   class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 text-center py-3 px-4 rounded-lg transition-colors">
                    <i class="fas fa-home mr-2"></i> Homepage
                </a>
            @endif
        </div>

        <!-- Help Section -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Need help? Contact our support team at 
                <a href="mailto:support@rms.lk" class="text-indigo-600 hover:underline">support@rms.lk</a>
            </p>
            <p class="mt-2">&copy; {{ date('Y') }} Restaurant Management System</p>
        </div>
    </div>
</div>

@if(config('app.debug'))
    <!-- Route Development Tools -->
    <div class="fixed bottom-4 right-4 max-w-sm">
        <div class="bg-gray-900 text-white p-4 rounded-lg shadow-lg">
            <h4 class="font-medium mb-2">
                <i class="fas fa-tools mr-1"></i> Route Development Tools
            </h4>
            <div class="space-y-2 text-sm">
                <button onclick="copyToClipboard('{{ $attempted_path }}')" 
                        class="block w-full text-left p-2 bg-gray-700 hover:bg-gray-600 rounded">
                    📋 Copy Route Path
                </button>
                <button onclick="generateRoute()" 
                        class="block w-full text-left p-2 bg-gray-700 hover:bg-gray-600 rounded">
                    ⚡ Generate Route
                </button>
                <button onclick="showRouteList()" 
                        class="block w-full text-left p-2 bg-gray-700 hover:bg-gray-600 rounded">
                    📋 List All Routes
                </button>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Route path copied to clipboard!');
            });
        }

        function generateRoute() {
            const path = '{{ $attempted_path }}';
            const suggestion = `Route::get('${path}', [Controller::class, 'method'])->name('${path.replace(/\//g, '.')}');`;
            navigator.clipboard.writeText(suggestion).then(() => {
                alert('Route definition copied to clipboard!');
            });
        }

        function showRouteList() {
            window.open('/admin/routes/debug', '_blank');
        }
    </script>
@endif
@endsection
