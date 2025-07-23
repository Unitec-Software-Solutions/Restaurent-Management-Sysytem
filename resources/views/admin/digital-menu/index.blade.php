@extends('layouts.admin')

@section('content')
<div >
    <div class="mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Digital Menu Management - Sample Page</h1>
            <a href="#" class="bg-[#515DEF] text-white px-4 py-2 rounded-lg hover:bg-[#6A71F0] transition">
                Add Menu Item
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Menu Category Cards -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 bg-gray-50 border-b">
                    <h2 class="text-lg font-semibold text-gray-800">Appetizers</h2>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between">
                            <div>
                                <h3 class="font-medium">Bruschetta</h3>
                                <p class="text-sm text-gray-500">Tomato, basil, garlic on toasted bread</p>
                            </div>
                            <span class="font-medium">$8.99</span>
                        </div>
                    </li>
                    <!-- More menu items would go here -->
                </ul>
            </div>

            <!-- More category cards would go here -->
        </div>
    </div>
</div>
@endsection
