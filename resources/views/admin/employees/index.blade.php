@extends('layouts.admin')

@section('title', 'Employees')

@section('content')
<div class="mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Employee Management</h1>
            <p class="text-gray-600 mt-1">Manage your restaurant staff and their roles</p>
        </div>
        <a href="{{ route('admin.employees.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition shadow-sm">
            <i class="fas fa-plus mr-2"></i> Add Employee
        </a>
    </div>

    <!-- Filters with Export -->
    <x-module-filters
        :action="route('admin.employees.index')"
        :export-permission="'export_employees'"
        :export-filename="'employees_export.xlsx'">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name, ID, email, phone..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All Roles</option>
                @foreach($roles as $key => $label)
                    <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
            <select name="branch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Shift Type</label>
            <select name="shift_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All Shifts</option>
                <option value="morning" {{ request('shift_type') === 'morning' ? 'selected' : '' }}>Morning</option>
                <option value="evening" {{ request('shift_type') === 'evening' ? 'selected' : '' }}>Evening</option>
                <option value="night" {{ request('shift_type') === 'night' ? 'selected' : '' }}>Night</option>
                <option value="flexible" {{ request('shift_type') === 'flexible' ? 'selected' : '' }}>Flexible</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Show Deleted</label>
            <div class="flex items-center h-[42px]">
                <input type="checkbox" name="show_deleted" value="1"
                       {{ request('show_deleted') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-600">Include deleted employees</span>
            </div>
        </div>
    </x-module-filters>

    <!-- Employees Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role & Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift & Availability</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-600 font-medium text-sm">
                                            {{ strtoupper(substr($employee->name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $employee->emp_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ ucfirst($employee->role) }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $employee->branch->name ?? 'No Branch' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                @if($employee->shift_type)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $employee->shift_type === 'morning' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $employee->shift_type === 'evening' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $employee->shift_type === 'night' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $employee->shift_type === 'flexible' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst($employee->shift_type) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">No shift</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">
                                @if($employee->availability_status)
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $employee->availability_status === 'available' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $employee->availability_status === 'busy' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $employee->availability_status === 'on_break' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $employee->availability_status === 'off_duty' ? 'bg-gray-100 text-gray-700' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $employee->availability_status)) }}
                                    </span>
                                    @if($employee->current_workload && $employee->current_workload > 0)
                                        <span class="ml-1 text-xs text-gray-500">({{ $employee->current_workload }} orders)</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $employee->email }}</div>
                            <div class="text-sm text-gray-500">{{ $employee->phone }}</div>
                        </td>                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($employee->trashed())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Deleted
                                </span>
                            @elseif($employee->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $employee->joined_date ? $employee->joined_date->format('M d, Y') : '-' }}
                        </td>                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                @if($employee->trashed())
                                    <!-- Restore Button for Deleted Employees -->
                                    <form method="POST" action="{{ route('admin.employees.restore', $employee->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900 p-1" title="Restore"
                                                onclick="return confirm('Are you sure you want to restore this employee?')">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                @else
                                    <!-- Normal Actions for Active Employees -->
                                    <a href="{{ route('admin.employees.show', $employee) }}"
                                       class="text-indigo-600 hover:text-indigo-900 p-1" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}"
                                       class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 p-1" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this employee?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-400 text-lg mb-2">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">No employees found</h3>
                            <p class="text-gray-500">Get started by adding your first employee.</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.employees.create') }}"
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Add Employee
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $employees->links() }}
        </div>
        @endif
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
        document.querySelector('.fixed.bottom-4').remove();
    }, 5000);
</script>
@endif

@if(session('error'))
<div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        {{ session('error') }}
    </div>
</div>

<script>
    setTimeout(() => {
        document.querySelector('.fixed.bottom-4').remove();
    }, 5000);
</script>
@endif
@endsection
