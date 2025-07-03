<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('errorTitle', 'Error') | Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .bg-gradient-danger { background: linear-gradient(135deg, #EF5151 0%, #F06A6A 100%); }
        .bg-gradient-warning { background: linear-gradient(135deg, #FF9800 0%, #FFC107 100%); }
        .bg-gradient-info { background: linear-gradient(135deg, #2196F3 0%, #21CBF3 100%); }
        .bg-gradient-secondary { background: linear-gradient(135deg, #6c757d 0%, #9e9e9e 100%); }
    </style>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="@yield('headerClass', 'bg-gradient-danger') py-6 px-8 text-white">
                <h1 class="text-3xl font-bold flex items-center gap-3">
                    <i class="@yield('errorIcon', 'fas fa-exclamation-circle')"></i>
                    <span>@yield('errorCode', '500') - @yield('errorTitle', 'Server Error')</span>
                </h1>
            </div>

            <div class="p-8">
                <div class="text-center mb-6">
                    <div class="mx-auto w-40 h-40 @yield('iconBgClass', 'bg-red-100') rounded-full flex items-center justify-center mb-4">
                        <i class="@yield('mainIcon', 'fas fa-server') @yield('iconColor', 'text-red-500') text-6xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">@yield('errorHeading', 'Oops! Something went wrong')</h2>

                    <p class="text-gray-600">@yield('errorMessage', 'Our servers encountered an unexpected error. Please try again later.')</p>
                    <p class="text-sm text-gray-500 mt-2">Error @yield('errorCode', '500'): @yield('errorTitle', 'Internal Server Error')</p>
                </div>

                <div class="space-y-4">
                    <a href="{{ url('/') }}"
                        class="block w-full @yield('buttonClass', 'bg-[#EF5151] hover:bg-[#b93a3a]') text-white text-center py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-home mr-2"></i> Back to Home
                    </a>

                    <a href="{{ url()->previous() }}"
                        class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 text-center py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Return to Previous Page
                    </a>

                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                            class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center py-3 px-4 rounded-lg transition-colors">
                            <i class="fas fa-tachometer-alt mr-2"></i> Go to Dashboard
                        </a>
                    @endauth

                    <button onclick="window.location.reload()"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 text-center py-3 px-4 rounded-lg transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i> Reload Page
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Need help? Contact our support team at <a href="mailto:support@rms.lk"
                    class="text-[#EF5151] hover:underline">support@rms.lk</a></p>
            <p class="mt-2">&copy; {{ date('Y') }} Restaurant Management System</p>
        </div>
    </div>
</body>
</html>
