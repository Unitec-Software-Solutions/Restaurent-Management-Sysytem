@extends('layouts.admin')

@section('title', 'Employee Details')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div class="flex items-center space-x-4">
            <div class="h-16 w-16 flex-shrink-0">
                <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-indigo-600 font-bold text-xl">
                        {{ strtoupper(substr($employee->name, 0, 2)) }}
                    </span>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $employee->name }}</h1>
                <p class="text-gray-600">{{ $employee->emp_id }} • {{ ucfirst($employee->role) }}</p>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.employees.edit', $employee) }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition">
                <i class="fas fa-edit mr-2"></i> Edit Employee
            </a>
            <a href="{{ route('admin.employees.index') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Full Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Employee ID</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->emp_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="mailto:{{ $employee->email }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ $employee->email }}
                            </a>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="tel:{{ $employee->phone }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ $employee->phone }}
                            </a>
                        </p>
                    </div>
                    @if($employee->emergency_contact)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Emergency Contact</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->emergency_contact }}</p>
                    </div>
                    @endif
                    @if($employee->address)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Address</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->address }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Work Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Work Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Role</label>
                        <p class="mt-1 text-sm text-gray-900">{{ ucfirst($employee->role) }}</p>
                    </div>
                    @if($employee->position)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Position</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->position }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Branch</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $employee->branch->name ?? 'No Branch Assigned' }}</p>
                    </div>
                    @if($employee->department)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Department</label>
                        <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $employee->department)) }}</p>
                    </div>
                    @endif
                    @if($employee->salary)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Monthly Salary</label>
                        <p class="mt-1 text-sm text-gray-900">LKR {{ number_format($employee->salary, 2) }}</p>
                    </div>
                    @endif
                    @if($employee->hourly_rate)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Hourly Rate</label>
                        <p class="mt-1 text-sm text-gray-900">LKR {{ number_format($employee->hourly_rate, 2) }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Joined Date</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $employee->joined_date ? $employee->joined_date->format('F d, Y') : 'Not specified' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Shift Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Shift & Availability</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Shift Type</label>
                        <div class="mt-1">
                            @if($employee->shift_type)
                                <span class="px-3 py-1 text-sm font-medium rounded-full 
                                    {{ $employee->shift_type === 'morning' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $employee->shift_type === 'evening' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $employee->shift_type === 'night' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $employee->shift_type === 'flexible' ? 'bg-blue-100 text-blue-800' : '' }}">
                                    {{ ucfirst($employee->shift_type) }} Shift
                                </span>
                            @else
                                <span class="text-gray-400">No shift assigned</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Availability Status</label>
                        <div class="mt-1">
                            @if($employee->availability_status)
                                <span class="px-3 py-1 text-sm font-medium rounded-full 
                                    {{ $employee->availability_status === 'available' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $employee->availability_status === 'busy' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $employee->availability_status === 'on_break' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $employee->availability_status === 'off_duty' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $employee->availability_status)) }}
                                </span>
                            @else
                                <span class="text-gray-400">Status not set</span>
                            @endif
                        </div>
                    </div>
                    @if($employee->shift_start_time && $employee->shift_end_time)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Shift Hours</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $employee->shift_start_time->format('g:i A') }} - {{ $employee->shift_end_time->format('g:i A') }}
                        </p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Current Workload</label>
                        <div class="mt-1 flex items-center">
                            <span class="text-sm text-gray-900">{{ $employee->current_workload ?? 0 }} active orders</span>
                            @if($employee->current_workload > 0)
                                <div class="ml-2 w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(($employee->current_workload / 10) * 100, 100) }}%"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($employee->notes)
            <!-- Notes -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $employee->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Account Status</span>
                        @if($employee->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Inactive
                            </span>
                        @endif
                    </div>
                    
                    @if($employee->shift_type)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">On Shift</span>
                        @if($employee->isOnShift())
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Yes
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                No
                            </span>
                        @endif
                    </div>
                    @endif
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Can Take Orders</span>
                        @if($employee->canTakeOrder())
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Yes
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                No
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Restaurant Role -->
            @if($employee->employeeRole)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Restaurant Role</h3>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-tag text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800">
                                {{ ucwords(str_replace('-', ' ', $employee->employeeRole->name)) }}
                            </h4>
                            @if($employee->employeeRole->permissions->count() > 0)
                                <div class="mt-1 text-xs text-blue-700">
                                    {{ $employee->employeeRole->permissions->count() }} permissions assigned
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                
                <div class="space-y-2">
                    @if($employee->availability_status !== 'available')
                    <form method="POST" action="{{ route('admin.employees.update-availability', $employee) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="availability_status" value="available">
                        <button type="submit" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">
                            <i class="fas fa-check mr-2"></i> Set Available
                        </button>
                    </form>
                    @endif
                    
                    @if($employee->availability_status !== 'on_break')
                    <form method="POST" action="{{ route('admin.employees.update-availability', $employee) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="availability_status" value="on_break">
                        <button type="submit" 
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition">
                            <i class="fas fa-coffee mr-2"></i> Set On Break
                        </button>
                    </form>
                    @endif
                    
                    @if($employee->availability_status !== 'off_duty')
                    <form method="POST" action="{{ route('admin.employees.update-availability', $employee) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="availability_status" value="off_duty">
                        <button type="submit" 
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
                            <i class="fas fa-user-clock mr-2"></i> Set Off Duty
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                
                @if($employee->orders && $employee->orders->count() > 0)
                    <div class="space-y-3">
                        @foreach($employee->orders->take(5) as $order)
                        <div class="flex items-center justify-between text-sm">
                            <div>
                                <p class="font-medium text-gray-900">Order #{{ $order->id }}</p>
                                <p class="text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $order->status === 'preparing' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $order->status === 'submitted' ? 'bg-blue-100 text-blue-800' : '' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-4">
                        <i class="fas fa-clipboard-list text-2xl mb-2"></i>
                        <p class="text-sm">No recent orders</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
    </div>
</div>

<script>
    setTimeout(() => {
        const notification = document.querySelector('.fixed.bottom-4');
        if (notification) notification.remove();
    }, 5000);
</script>
@endif
@endsection
