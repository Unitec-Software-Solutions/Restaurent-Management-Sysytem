<div id="logoutModal" class="fixed inset-0 flex z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center mb-4">
        <div class="bg-red-100 p-3 rounded-xl mr-3">
                <i class="fas fa-sign-out-alt text-red-500 text-xl"></i>
            </div>
            <h2 class="text-xl font-semibold text-gray-800">Confirm Logout</h2>
        </div>
        <p class="mb-6 text-gray-700">Are you sure you want to log out of your account?</p>
        <form method="POST" action="{{ route('admin.logout.action') }}">
            @csrf
            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Yes, Logout
                </button>
                <button type="button" onclick="toggleLogoutModal()"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>