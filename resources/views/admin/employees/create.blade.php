@extends('layouts.admin')

@section('title', 'Create Employee')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Add New Employee</h1>
            <p class="text-gray-600 mt-1">Create a new employee profile</p>
        </div>
        <a href="{{ route('admin.employees.index') }}" 
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Employees
        </a>
    </div>

    <form method="POST" action="{{ route('admin.employees.store') }}" class="space-y-6">
        @csrf
        
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="emergency_contact" class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact</label>
                    <input type="tel" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('emergency_contact') border-red-500 @enderror">
                    @error('emergency_contact')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea id="address" name="address" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Work Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Work Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Primary Role *</label>
                    <select id="role" name="role" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('role') border-red-500 @enderror">
                        <option value="">Select Role</option>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="restaurant_role" class="block text-sm font-medium text-gray-700 mb-1">Restaurant Role *</label>
                    <select id="restaurant_role" name="restaurant_role" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('restaurant_role') border-red-500 @enderror">
                        <option value="">Select Restaurant Role</option>
                        @foreach($restaurantRoles as $role)
                            <option value="{{ $role->name }}" {{ old('restaurant_role') === $role->name ? 'selected' : '' }}>
                                {{ ucwords(str_replace('-', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('restaurant_role')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">This determines what operations the employee can perform</p>
                </div>
                
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                    <select id="branch_id" name="branch_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('branch_id') border-red-500 @enderror">
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position/Title</label>
                    <input type="text" id="position" name="position" value="{{ old('position') }}"
                           placeholder="e.g., Senior Chef, Head Waiter"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('position') border-red-500 @enderror">
                    @error('position')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="salary" class="block text-sm font-medium text-gray-700 mb-1">Monthly Salary (LKR)</label>
                    <input type="number" id="salary" name="salary" value="{{ old('salary') }}" min="0" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('salary') border-red-500 @enderror">
                    @error('salary')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate (LKR)</label>
                    <input type="number" id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate') }}" min="0" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('hourly_rate') border-red-500 @enderror">
                    @error('hourly_rate')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Used for overtime calculations</p>
                </div>
            </div>
        </div>

        <!-- Shift & Department Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Shift & Department Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="shift_type" class="block text-sm font-medium text-gray-700 mb-1">Shift Type</label>
                    <select id="shift_type" name="shift_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('shift_type') border-red-500 @enderror">
                        <option value="">Select Shift Type</option>
                        <option value="morning" {{ old('shift_type') === 'morning' ? 'selected' : '' }}>Morning (6:00 AM - 3:00 PM)</option>
                        <option value="evening" {{ old('shift_type') === 'evening' ? 'selected' : '' }}>Evening (3:00 PM - 10:00 PM)</option>
                        <option value="night" {{ old('shift_type') === 'night' ? 'selected' : '' }}>Night (10:00 PM - 6:00 AM)</option>
                        <option value="flexible" {{ old('shift_type') === 'flexible' ? 'selected' : '' }}>Flexible</option>
                    </select>
                    @error('shift_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select id="department" name="department"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('department') border-red-500 @enderror">
                        <option value="">Select Department</option>
                        <option value="front_of_house" {{ old('department') === 'front_of_house' ? 'selected' : '' }}>Front of House</option>
                        <option value="kitchen" {{ old('department') === 'kitchen' ? 'selected' : '' }}>Kitchen</option>
                        <option value="bar" {{ old('department') === 'bar' ? 'selected' : '' }}>Bar</option>
                        <option value="management" {{ old('department') === 'management' ? 'selected' : '' }}>Management</option>
                        <option value="support" {{ old('department') === 'support' ? 'selected' : '' }}>Support</option>
                    </select>
                    @error('department')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="shift_start_time" class="block text-sm font-medium text-gray-700 mb-1">Shift Start Time</label>
                    <input type="time" id="shift_start_time" name="shift_start_time" value="{{ old('shift_start_time') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('shift_start_time') border-red-500 @enderror">
                    @error('shift_start_time')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Leave empty for flexible shift</p>
                </div>

                <div>
                    <label for="shift_end_time" class="block text-sm font-medium text-gray-700 mb-1">Shift End Time</label>
                    <input type="time" id="shift_end_time" name="shift_end_time" value="{{ old('shift_end_time') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('shift_end_time') border-red-500 @enderror">
                    @error('shift_end_time')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Leave empty for flexible shift</p>
                </div>

                <div>
                    <label for="availability_status" class="block text-sm font-medium text-gray-700 mb-1">Initial Availability Status</label>
                    <select id="availability_status" name="availability_status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('availability_status') border-red-500 @enderror">
                        <option value="available" {{ old('availability_status', 'available') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="off_duty" {{ old('availability_status') === 'off_duty' ? 'selected' : '' }}>Off Duty</option>
                        <option value="on_break" {{ old('availability_status') === 'on_break' ? 'selected' : '' }}>On Break</option>
                    </select>
                    @error('availability_status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>        <!-- Permissions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Restaurant Role Details</h3>
            <p class="text-gray-600 text-sm mb-4">The selected restaurant role will automatically grant appropriate permissions for restaurant operations</p>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-800">Restaurant Role Permissions</h4>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Host/Hostess:</strong> Manage reservations, view table status, customer service</li>
                                <li><strong>Servers:</strong> Take orders, modify orders, process payments, customer service</li>
                                <li><strong>Bartenders:</strong> Manage bar inventory, prepare beverages, cash handling</li>
                                <li><strong>Cashiers:</strong> Process payments, handle refunds, print receipts</li>
                                <li><strong>Chefs:</strong> View kitchen orders, update order status, manage inventory</li>
                                <li><strong>Dishwashers:</strong> Kitchen support, equipment maintenance</li>
                                <li><strong>Kitchen Managers:</strong> Manage kitchen staff, operations, reports</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
            
            <div class="space-y-4">
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="4" 
                              placeholder="Any additional notes about the employee..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                        Employee is active
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ route('admin.employees.index') }}" 
               class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200 text-center">
                Cancel
            </a>
            <button type="submit" 
                    class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                <i class="fas fa-save mr-2"></i> Create Employee
            </button>
        </div>
    </form>
</div>
@endsection
