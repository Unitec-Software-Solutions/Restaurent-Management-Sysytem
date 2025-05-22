@extends('layouts.admin')

@section('content')
<div >
    <div class="p-4 rounded-lg">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Settings - Sample Page</h1>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <a href="#" class="border-b-2 border-[#515DEF] text-[#515DEF] px-4 py-4 text-sm font-medium">General</a>
                    <a href="#" class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-4 py-4 text-sm font-medium">Staff</a>
                    <a href="#" class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-4 py-4 text-sm font-medium">Notifications</a>
                    <a href="#" class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-4 py-4 text-sm font-medium">Integrations</a>
                </nav>
            </div>
            
            <div class="p-6">
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
                                        <!-- More options would go here -->
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
        </div>
    </div>
</div>
@endsection