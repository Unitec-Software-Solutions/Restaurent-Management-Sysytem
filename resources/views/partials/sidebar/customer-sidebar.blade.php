<aside id="sidebar"
    class="fixed top-0 left-0 z-5 flex flex-col w-64 h-full pt-16 bg-[#515DEF] border-r border-[#515DEF] dark:border-[#515DEF] transition-transform -translate-x-full lg:translate-x-0"
    aria-label="Sidebar">

    <div class="flex flex-col h-full text-white">

        {{-- Top Section --}}
        <div class="flex-1 overflow-y-auto">
            <div class="flex items-center gap-2 px-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#515DEF]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <span class="text-white font-bold text-xl">Customer Panel</span>
            </div>

            <div class="px-4 py-4">
                <ul class="space-y-2">
                    <li>
                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2 rounded-xl border bg-white text-gray-700 border-white">
                            @include('partials.icons.calendar-clock')
                            <span class="font-medium">My Reservations</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2 rounded-xl border text-white border-white hover:bg-white/10">
                            @include('partials.icons.shopping-cart')
                            <span class="font-medium">My Orders</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom Section --}}
        <div class="px-4 py-4 border-t border-[#6A71F0]">
            <ul class="space-y-2">
                <li>
                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2 rounded-xl border text-white border-white hover:bg-white/10">
                        @include('partials.icons.menu')
                        <span class="font-medium">Digital Menu</span>
                    </a>
                </li>
                <li>
                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2 rounded-xl border text-white border-white hover:bg-white/10">
                        @include('partials.icons.settings')
                        <span class="font-medium">Settings</span>
                    </a>
                </li>
                <li class="pt-4">

                    @csrf
                    <button type="submit"
                        class="w-full text-left flex items-center border gap-3 rounded-xl px-3 py-2 transition-colors hover:bg-[#6A71F0]">
                        @include('partials.icons.log-out')
                        <span>Sign Out</span>
                    </button>

                </li>
            </ul>
        </div>
    </div>
</aside>
