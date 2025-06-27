{{-- Guest Sidebar - Minimal version for unauthenticated users --}}
<aside id="sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-full pt-16 bg-[#515DEF] border-r border-[#515DEF] transition-transform duration-300 ease-in-out transform -translate-x-full lg:translate-x-0"
    aria-label="Sidebar">

    <div class="flex flex-col h-full text-white">
        {{-- Logo/Header --}}
        <div class="flex items-center gap-2 px-4 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#515DEF]" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
            </div>
            <span class="text-white font-bold text-xl">RM SYSTEMS</span>
        </div>

        {{-- Navigation --}}
        <div class="px-4 py-4">
            <div class="mb-6">
                <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Navigation</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ url('/') }}"
                            class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors bg-transparent text-white border-white hover:bg-white/10">
                            <i class="fas fa-home w-5 text-center"></i>
                            <span class="font-medium">Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('login') }}"
                            class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors bg-white text-gray-700 border-white">
                            <i class="fas fa-sign-in-alt w-5 text-center"></i>
                            <span class="font-medium">Login</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Spacer --}}
        <div class="flex-1"></div>

        {{-- Footer --}}
        <div class="px-4 py-4 border-t border-[#6A71F0]">
            <div class="text-xs text-indigo-300 text-center">
                Please login to access features
            </div>
        </div>
    </div>
</aside>
