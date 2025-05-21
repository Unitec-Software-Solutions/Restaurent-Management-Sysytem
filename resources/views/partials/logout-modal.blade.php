<div id="logoutModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <div class="flex items-center mb-4">
            <div class="bg-red-500 p-2 rounded-lg mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-red-600">Confirm Logout</h2>
        </div>
        <p class="mb-6 text-gray-700">Are you sure you want to log out?</p>

        <form method="POST" action="{{ route('admin.logout.action') }}">
            @csrf
            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded mb-3">
                Yes, Log me out
            </button>
        </form>

        <button onclick="toggleLogoutModal()" class="w-full text-center text-indigo-500 hover:underline text-sm">
            Cancel
        </button>
    </div>
</div>
