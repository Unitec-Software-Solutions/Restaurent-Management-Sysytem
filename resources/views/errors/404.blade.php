<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #515DEF 0%, #6A71F0 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-primary py-6 px-8 text-white">
                <h1 class="text-3xl font-bold flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>404 - Page Not Found</span>
                </h1>
            </div>
            
            <div class="p-8">
                <div class="text-center mb-6">
                    <div class="mx-auto w-40 h-40 bg-red-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-map-marker-alt text-red-500 text-6xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Oops! Lost in the kitchen?</h2>
                    <p class="text-gray-600">The page you're looking for doesn't exist or has been moved.</p>
                </div>
                
                <div class="space-y-4">
                    <a href="{{ url('/') }}" 
                       class="block w-full bg-[#515DEF] hover:bg-[#3a41b9] text-white text-center py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-home mr-2"></i> Back to Home
                    </a>
                    
                    <a href="{{ url()->previous() }}" 
                       class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 text-center py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Return to Previous Page
                    </a>
                    
                    @auth
                    <a href="{{ route('admin.dashboard') }}">Go to Dashboard</a>
                    @endauth
                </div>
            </div>
        </div>
        
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Need help? Contact our support team at <a href="mailto:support@restaurant.com" class="text-[#515DEF] hover:underline">support@rms.lk</a></p>
            <p class="mt-2">&copy; {{ date('Y') }} Restaurant Management System</p>
        </div>
    </div>
</body>
</html>