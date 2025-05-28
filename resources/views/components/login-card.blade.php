@props([
    'title' => 'Login',
    'subtitle' => 'Login to access your account',
    'formAction' => '',
    'logo' => true,
    'forgotPassword' => true,
    'rememberMe' => true,
])

<div class="bg-white rounded-lg p-8 shadow-md w-full max-w-md mx-4">
    @if($logo)
    <div class="flex items-center mb-6">
        <div class="bg-indigo-500 p-2 rounded-lg mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
        </div>
        <span class="text-indigo-500 font-bold text-xl">RM SYSTEMS</span>
    </div>
    @endif

    <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $title }}</h1>
    <p class="text-gray-600 mb-6">{{ $subtitle }}</p>

    {{ $slot }}

    <form method="POST" action="{{ $formAction }}">
        @csrf

        <!-- Email Input -->
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" id="email" placeholder="user@rms.lk"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                required value="{{ old('email') }}">
        </div>

        <!-- Password Input -->
        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
                <input type="password" id="password" name="password" placeholder="••••••••••••••••••••"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                <button type="button" onclick="togglePassword(this)"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i id="eye-icon" class="fas fa-eye text-gray-400"></i>
                </button>
            </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        @if($rememberMe || $forgotPassword)
        <div class="flex items-center justify-between mb-6">
            @if($rememberMe)
            <div class="flex items-center">
                <input id="remember_me" name="remember" type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="remember_me" class="ml-2 block text-sm text-gray-700">Remember me</label>
            </div>
            @endif
            
            @if($forgotPassword)
            <a href="#" class="text-red-500 text-sm">Forgot Password?</a>
            @endif
        </div>
        @endif

        <!-- Submit Button -->
        <button type="submit"
            class="w-full bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mb-4 transition-colors duration-200">
            Login
        </button>
    </form>
</div>

@push('scripts')
<script>
    function togglePassword(button) {
        const passwordInput = button.parentElement.querySelector('input');
        const eyeIcon = button.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
</script>
@endpush