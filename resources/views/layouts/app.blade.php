<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMS - Restaurant Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a2e0e6fa71.js" crossorigin="anonymous"></script>
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 1s ease-out forwards;
        }

        .nav-gradient {
            background: linear-gradient(135deg, #D9DCFF 0%, #ffffff 100%);
        }

        .footer-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #D9DCFF 100%);
        }
    </style>
</head>

<body class="bg-[#e3e4f8] text-gray-800">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="nav-gradient shadow-sm border-b border-[#515DEF]/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <a href="{{ url('/') }}" class="flex items-center">
                        <span class="text-2xl font-bold text-[#515DEF]">ORG</span>
                        <span class="ml-2 text-gray-600">Name</span>
                    </a>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <!-- 🔘 Test Page Button -->
                        <a href="{{ route('admin.testpage') }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                            🧪 Test Page
                        </a>
                        <a href="{{ route('admin.login') }}"
                            class="px-4 py-2 text-gray-700 hover:text-[#515DEF] transition-colors font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Sign In (admin login - test )
                        </a>
                        <a href="{{ route('reservations.create') }}"
                            class="ml-4 px-4 py-2 bg-[#515DEF]/10 text-[#515DEF] rounded-md hover:bg-[#515DEF]/20 transition-colors font-medium">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        <!-- Footer Section - Only shown if yielded -->
        @hasSection('show-footer')
            @include('partials.footer.welcome-footer')
        @endif

    </div>
</body>

</html>