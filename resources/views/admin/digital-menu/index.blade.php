@extends('layouts.admin')

@section('content')
<div >
    <div class="mx-auto px-4 py-8">
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
