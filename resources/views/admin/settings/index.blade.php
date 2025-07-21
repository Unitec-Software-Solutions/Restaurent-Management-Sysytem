@extends('layouts.admin')

@section('content')
<div>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Settings - Sample Page</h1>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px" id="settings-tabs">
                    <button data-tab="general" class="tab-btn border-b-2 border-[#515DEF] text-[#515DEF] px-4 py-4 text-sm font-medium focus:outline-none">General</button>
                    <button data-tab="staff" class="tab-btn border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-4 py-4 text-sm font-medium focus:outline-none">Staff</button>
                    <button data-tab="notifications" class="tab-btn border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-4 py-4 text-sm font-medium focus:outline-none">Notifications</button>
                    <button data-tab="integrations" class="tab-btn border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-4 py-4 text-sm font-medium focus:outline-none">Integrations</button>
                </nav>
            </div>

            <div class="p-6">
                {{-- General Tab --}}
                <div id="tab-general" class="tab-content">
                    <form>
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 mb-4">General Settings</h2>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="restaurant-name" class="block text-sm font-medium text-gray-700">Restaurant Name</label>
                                        <input type="text" id="restaurant-name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="RM Systems Restaurant">
                                    </div>
                                    <div>
                                        <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                                        <select id="timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <option>(GMT-05:00) Eastern Time</option>
                                            <!-- More options -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200">
                                <button type="submit" class="bg-[#515DEF] text-white px-4 py-2 rounded-lg hover:bg-[#6A71F0] transition">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Staff Tab --}}
                <div id="tab-staff" class="tab-content hidden">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Staff Management</h2>
                    <p class="text-sm text-gray-600 mb-2">Manage your team members here.</p>
                    <ul class="list-disc pl-5 text-sm text-gray-700">
                        <li>John Doe – Admin</li>
                        <li>Jane Smith – Manager</li>
                        <li>Emily Johnson – Cashier</li>
                    </ul>
                </div>

                {{-- Notifications Tab --}}
                <div id="tab-notifications" class="tab-content hidden">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Notification Preferences</h2>
                    <form class="space-y-4">
                        <div>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" class="form-checkbox text-[#515DEF]" checked>
                                <span>Email Alerts</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" class="form-checkbox text-[#515DEF]">
                                <span>SMS Notifications</span>
                            </label>
                        </div>
                        <button type="submit" class="bg-[#515DEF] text-white px-4 py-2 rounded-lg hover:bg-[#6A71F0] transition">
                            Save Notifications
                        </button>
                    </form>
                </div>

                {{-- Integrations Tab --}}
                <div id="tab-integrations" class="tab-content hidden">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Third-party Integrations</h2>
                    <ul class="space-y-4 text-sm text-gray-700">
                        <li>
                            <strong>Stripe:</strong> Connected ✅
                        </li>
                        <li>
                            <strong>Google Analytics:</strong> Not Connected ❌
                        </li>
                        <li>
                            <strong>Slack:</strong> Connected ✅
                        </li>
                    </ul>
                    <button class="mt-4 bg-[#515DEF] text-white px-4 py-2 rounded-lg hover:bg-[#6A71F0] transition">
                        Manage Integrations
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tabButtons = document.querySelectorAll(".tab-btn");
        const tabContents = document.querySelectorAll(".tab-content");

        tabButtons.forEach(btn => {
            btn.addEventListener("click", () => {
                const target = btn.getAttribute("data-tab");

                tabButtons.forEach(b => b.classList.remove("border-[#515DEF]", "text-[#515DEF]"));
                btn.classList.add("border-[#515DEF]", "text-[#515DEF]");

                tabContents.forEach(tc => tc.classList.add("hidden"));
                document.getElementById(`tab-${target}`).classList.remove("hidden");
            });
        });
    });
</script>
@endsection
