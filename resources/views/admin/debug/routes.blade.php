@extends('layouts.admin')

@section('title', 'Route Debugger')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">🔍 Route Debugger</h1>
                <p class="text-gray-600 mt-1">Debug and analyze application routes</p>
            </div>
            <div class="flex gap-3">
                <button onclick="exportRoutes('json')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    📄 Export JSON
                </button>
                <button onclick="exportRoutes('csv')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    📊 Export CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-route text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Routes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-tag text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Named Routes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['named'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Invalid Controllers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['invalid_controllers'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-shield-alt text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Middleware Types</p>
                    <p class="text-2xl font-bold text-gray-900">{{ count($stats['middleware']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <form method="GET" class="flex gap-2">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Search routes, URIs, or controllers..."
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                        Search
                    </button>
                    @if($search)
                        <a href="{{ route('admin.debug.routes') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Sort -->
            <div class="flex gap-2">
                <select onchange="window.location.href = this.value" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="{{ route('admin.debug.routes', array_merge(request()->query(), ['sort' => 'name'])) }}" 
                            {{ $sort === 'name' ? 'selected' : '' }}>Sort by Name</option>
                    <option value="{{ route('admin.debug.routes', array_merge(request()->query(), ['sort' => 'uri'])) }}" 
                            {{ $sort === 'uri' ? 'selected' : '' }}>Sort by URI</option>
                    <option value="{{ route('admin.debug.routes', array_merge(request()->query(), ['sort' => 'action'])) }}" 
                            {{ $sort === 'action' ? 'selected' : '' }}>Sort by Controller</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Route Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Route Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            URI
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Methods
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Controller
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Middleware
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($routes as $route)
                        <tr class="hover:bg-gray-50">
                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($route['controller_exists'] && $route['method_exists'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓ Valid
                                    </span>
                                @elseif($route['controller_exists'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⚠ Method Missing
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ✗ Invalid
                                    </span>
                                @endif
                            </td>

                            <!-- Route Name -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($route['name'])
                                    <div class="text-sm font-medium text-gray-900">{{ $route['name'] }}</div>
                                @else
                                    <span class="text-sm text-gray-400 italic">Unnamed</span>
                                @endif
                            </td>

                            <!-- URI -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono">{{ $route['uri'] }}</div>
                                @if($route['parameters'])
                                    <div class="text-xs text-gray-500">
                                        Parameters: {{ implode(', ', $route['parameters']) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Methods -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($route['methods'] as $method)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $method === 'GET' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $method === 'POST' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $method === 'PUT' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $method === 'PATCH' ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $method === 'DELETE' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ !in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ $method }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Controller -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 break-all">{{ $route['action'] }}</div>
                            </td>

                            <!-- Middleware -->
                            <td class="px-6 py-4">
                                @if($route['middleware'])
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($route['middleware'] as $middleware)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $middleware }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">None</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($route['name'])
                                    <button onclick="testRoute('{{ $route['name'] }}')" 
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        Test
                                    </button>
                                @endif
                                <button onclick="copyRoute('{{ addslashes($route['name'] ?? $route['uri']) }}')" 
                                        class="text-gray-600 hover:text-gray-900">
                                    Copy
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No routes found matching your search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Generate Section -->
    <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">🚀 Quick Route Generator</h3>
        <form id="route-generator" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URI</label>
                <input type="text" id="gen-uri" placeholder="/admin/example" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Method</label>
                <select id="gen-method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="DELETE">DELETE</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Route Name</label>
                <input type="text" id="gen-name" placeholder="admin.example.index" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Controller</label>
                <input type="text" id="gen-controller" placeholder="ExampleController" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                <input type="text" id="gen-action" placeholder="index" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-end">
                <button type="button" onclick="generateRoute()" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                    Generate
                </button>
            </div>
        </form>

        <!-- Generated Output -->
        <div id="generated-output" class="mt-4 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Generated Route Definition</label>
            <div class="bg-gray-100 p-3 rounded-lg">
                <code id="generated-code" class="text-sm"></code>
            </div>
            <div class="mt-2 flex gap-2">
                <button onclick="copyGenerated()" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded">
                    Copy to Clipboard
                </button>
                <span id="suggested-file" class="text-sm text-gray-600"></span>
            </div>
        </div>
    </div>
</div>

<!-- Test Route Modal -->
<div id="test-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Route Test Result</h3>
                <div id="test-result"></div>
                <div class="mt-4 flex justify-end">
                    <button onclick="closeTestModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testRoute(routeName) {
    fetch(`{{ route('admin.debug.routes.test') }}?route=${routeName}`)
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('test-result');
            if (data.status === 'success') {
                resultDiv.innerHTML = `
                    <div class="bg-green-50 p-3 rounded-lg">
                        <p class="text-green-800 font-medium">✓ Route is valid</p>
                        <div class="mt-2 text-sm text-green-700">
                            <p><strong>URL:</strong> ${data.url}</p>
                            <p><strong>Methods:</strong> ${data.methods.join(', ')}</p>
                            <p><strong>Controller:</strong> ${data.controller}</p>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="bg-red-50 p-3 rounded-lg">
                        <p class="text-red-800 font-medium">✗ Route test failed</p>
                        <p class="text-sm text-red-700 mt-1">${data.message}</p>
                    </div>
                `;
            }
            document.getElementById('test-modal').classList.remove('hidden');
        });
}

function closeTestModal() {
    document.getElementById('test-modal').classList.add('hidden');
}

function copyRoute(routeName) {
    navigator.clipboard.writeText(routeName).then(() => {
        alert('Route name copied to clipboard!');
    });
}

function generateRoute() {
    const uri = document.getElementById('gen-uri').value;
    const method = document.getElementById('gen-method').value;
    const name = document.getElementById('gen-name').value;
    const controller = document.getElementById('gen-controller').value;
    const action = document.getElementById('gen-action').value;

    if (!uri || !controller || !action) {
        alert('Please fill in all required fields');
        return;
    }

    const params = new URLSearchParams({
        uri, method, name, controller, action
    });

    fetch(`{{ route('admin.debug.routes.generate') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('generated-code').textContent = data.definition;
            document.getElementById('suggested-file').textContent = `Suggested file: ${data.file_suggestion}`;
            document.getElementById('generated-output').classList.remove('hidden');
        });
}

function copyGenerated() {
    const code = document.getElementById('generated-code').textContent;
    navigator.clipboard.writeText(code).then(() => {
        alert('Route definition copied to clipboard!');
    });
}

function exportRoutes(format) {
    window.open(`{{ route('admin.debug.routes.export') }}?format=${format}`, '_blank');
}
</script>
@endsection
