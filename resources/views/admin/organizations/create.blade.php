@extends('layouts.admin')

@section('title', 'Add Organization')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold mb-6">Add New Organization</h1>
        <a href="{{ route('admin.organizations.index') }}"
           class="inline-block mb-6 bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 transition font-semibold">
            ← Back to Organizations
        </a>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($noPlans) && $noPlans && auth('admin')->user()->isSuperAdmin())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                No subscription plans available. Please <a href="{{ route('admin.subscription-plans.create') }}" class="underline text-blue-700">create a subscription plan</a> before adding an organization.
            </div>
        @endif

        @if(!$plans->isEmpty())
            <form action="{{ route('admin.organizations.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Organization Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email <span class="text-red-600">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="example@email.com">
                    @if($errors->has('email'))
                        <span class="text-red-600 text-sm">{{ $errors->first('email') }}</span>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Phone <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required pattern="\d{10,15}" maxlength="15"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="0712345678">
                    @if($errors->has('phone'))
                        <span class="text-red-600 text-sm">{{ $errors->first('phone') }}</span>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-10">
                        <button type="button" onclick="togglePassword('password')" tabindex="-1"
                            class="absolute right-2 top-2 text-gray-500 focus:outline-none">
                            <svg id="password-eye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm Password <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-10">
                        <button type="button" onclick="togglePassword('password_confirmation')" tabindex="-1"
                            class="absolute right-2 top-2 text-gray-500 focus:outline-none">
                            <svg id="password_confirmation-eye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Address <span class="text-red-600">*</span>
                    </label>
                    <textarea name="address" rows="4" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Line 1&#10;Line 2&#10;Line 3&#10;Line 4">{{ old('address', $organization->address ?? '') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contact Person <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contact Person Designation <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="contact_person_designation" value="{{ old('contact_person_designation') }}" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contact Person Phone <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="contact_person_phone" value="{{ old('contact_person_phone') }}" required pattern="\d{10,15}" maxlength="15"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="0712345678">
                    @if($errors->has('contact_person_phone'))
                        <span class="text-red-600 text-sm">{{ $errors->first('contact_person_phone') }}</span>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-600">*</span>
                    </label>
                    <select name="is_active" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">
                        Subscription Plan <span class="text-red-600">*</span>
                    </label>
                    <select name="subscription_plan_id" required class="w-full border rounded px-3 py-2">
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} ({{ number_format($plan->price, 2) }} {{ $plan->currency }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Discount (%) <span class="text-red-600">*</span>
                    </label>
                    <input type="number" name="discount_percentage" value="{{ old('discount_percentage', $organization->discount_percentage ?? 0) }}"
                        min="0" max="100" step="0.01" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="0.00">
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('admin.organizations.index') }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create Organization</button>
                </div>
            </form>
        @else
            {{-- Optionally hide the form if no plans --}}
        @endif
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const eye = document.getElementById(id + '-eye');
    if (input.type === "password") {
        input.type = "text";
        eye.classList.add('text-indigo-600');
    } else {
        input.type = "password";
        eye.classList.remove('text-indigo-600');
    }
}
</script>
@endsection
