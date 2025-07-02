@extends('layouts.admin')

@section('header-title', 'Select Organization - Create GTN')

@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden max-w-2xl mx-auto">
            <!-- Card Header -->
            <div class="p-6 border-b">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 mb-4">
                        <i class="fas fa-building text-indigo-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Select Organization</h2>
                    <p class="text-sm text-gray-500 mt-2">Choose an organization to create a Goods Transfer Note</p>
                </div>
            </div>

            <!-- Form Container -->
            <div class="p-6">
                @if ($organizations->count() > 0)
                    <form method="GET" action="{{ route('admin.inventory.gtn.create') }}">
                        <div class="mb-6">
                            <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-3">
                                Select Organization *
                            </label>
                            <div class="space-y-3">
                                @foreach ($organizations as $organization)
                                    <label
                                        class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="organization_id" value="{{ $organization->id }}"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" required>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $organization->name }}
                                                    </p>
                                                    @if ($organization->description)
                                                        <p class="text-sm text-gray-500">
                                                            {{ $organization->description }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="text-right">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $organization->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                        {{ $organization->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                    @if ($organization->branches_count ?? 0 > 0)
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            {{ $organization->branches_count ?? 0 }} branches
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-between gap-3 pt-4 border-t">
                            <a href="{{ route('admin.inventory.gtn.index') }}"
                                class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                                <i class="fas fa-arrow-left mr-2"></i> Back to GTNs
                            </a>
                            <button type="submit"
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-center">
                                <i class="fas fa-arrow-right mr-2"></i> Continue to Create GTN
                            </button>
                        </div>
                    </form>
                @else
                    <!-- No Organizations Available -->
                    <div class="text-center py-8">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Organizations Available</h3>
                        <p class="text-sm text-gray-500 mb-6">
                            There are no active organizations available for creating GTNs.
                        </p>
                        <a href="{{ route('admin.inventory.gtn.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-arrow-left mr-2"></i> Back to GTNs
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Super Admin Info -->
        <div class="max-w-2xl mx-auto mt-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Super Admin Access</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>As a super admin, you can create GTNs for any organization. Select an organization above to
                                continue with GTN creation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit form when organization is selected (optional UX improvement)
            const radioButtons = document.querySelectorAll('input[name="organization_id"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Optional: Auto-submit after a short delay to allow user to see selection
                    // setTimeout(() => {
                    //     this.closest('form').submit();
                    // }, 500);
                });
            });
        });
    </script>
@endpush
