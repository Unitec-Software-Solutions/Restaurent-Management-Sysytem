@extends('layouts.admin')

@section('content')
<div class="flex-1 p-4 rounded-lg md:p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6 md:mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Profile</h1>
    </div>

    <!-- Bento Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Profile Card -->
        <div class="card bg-white rounded-xl shadow-sm p-4 md:p-6 col-span-1">
            <div class="flex flex-col items-center">
                <div class="relative mb-4">
                    <div class="h-24 w-24 md:h-32 md:w-32 rounded-full bg-[#D9DCFF] flex items-center justify-center text-[#515DEF] text-3xl md:text-4xl">
                        <span>{{ strtoupper(substr($admin->name, 0, 2)) }}</span>
                    </div>
                </div>
                <h2 class="text-xl font-bold text-center">{{ $admin->name }}</h2>
                {{-- User Role - Staff Role Etc --}}
                {{-- <p class="text-gray-600 text-center mb-4">Super Administrator</p> --}}
                
                <div class="w-full border-t border-gray-200 pt-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Joined</span>
                        <span class="font-medium text-gray-800">{{ $admin->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Login</span>
                        <span class="font-medium text-gray-800">{{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status</span>
                        <span class="text-green-500 font-medium">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Details Card -->
        <div class="card bg-white rounded-xl shadow-sm p-4 md:p-6 md:col-span-1 lg:col-span-2">
            <h3 class="text-lg font-bold mb-4 md:mb-6 text-gray-800">Account Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Full Name</label>
                    <p class="text-gray-800 font-medium">{{ $admin->name }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                    <p class="text-gray-800 font-medium">{{ $admin->email }}</p>
                </div>
                @if($admin->branch)
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Branch</label>
                    <p class="text-gray-800 font-medium">{{ $admin->branch->name }}</p>
                </div>
                @endif
                @if($admin->organization)
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Organization</label>
                    <p class="text-gray-800 font-medium">{{ $admin->organization->name }}</p>
                </div>
                @endif
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Username</label>
                    <p class="text-gray-800 font-medium">{{ $admin->username ?? $admin->email }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Role</label>
                    <p class="text-gray-800 font-medium">Super Administrator</p>
                </div>
            </div>
        </div>

        <!-- Branch Information Card -->
        @if($admin->branch)
        <div class="card bg-white rounded-xl shadow-sm p-4 md:p-6 md:col-span-2">
            <h3 class="text-lg font-bold mb-4 md:mb-6 text-gray-800">Branch Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Branch Name</label>
                    <p class="text-gray-800 font-medium">{{ $admin->branch->name }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Branch Email</label>
                    <p class="text-gray-800 font-medium">{{ $admin->branch->email }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Branch Phone</label>
                    <p class="text-gray-800 font-medium">{{ $admin->branch->phone }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Branch Address</label>
                    <p class="text-gray-800 font-medium">{{ $admin->branch->address }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Security Card -->
        <div class="card bg-white rounded-xl shadow-sm p-4 md:p-6 md:col-span-2">
            <h3 class="text-lg font-bold mb-4 md:mb-6 text-gray-800">Security</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                    <div>
                        <h4 class="font-medium text-gray-800">Two-Factor Authentication</h4>
                        <p class="text-sm text-gray-500">Enabled for extra security</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Not Active
                    </span>
                </div>
                {{-- <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                    <div>
                        <h4 class="font-medium text-gray-800">Password</h4>
                        <p class="text-sm text-gray-500">Last changed 3 months ago</p>
                    </div>
                    <button class="text-[#515DEF] hover:text-[#3f4ed5] text-sm font-medium">
                        Change Password
                    </button>
                </div> --}}
                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                    <div>
                        <h4 class="font-medium text-gray-800">Active Sessions</h4>
                        <p class="text-sm text-gray-500">1 active session</p>
                    </div>
                    {{-- <button class="text-[#515DEF] hover:text-[#3f4ed5] text-sm font-medium">
                        !! View Sessions get session data from the database session table
                    </button> --}}
                </div>
            </div>
        </div>

        <!-- Activity Card -->
        <div class="card bg-white rounded-xl shadow-sm p-4 md:p-6">
            <div class="flex justify-between items-center mb-4 md:mb-6">
                <h3 class="text-lg font-bold text-gray-800">Recent Activity</h3>
                <button class="text-[#515DEF] hover:text-[#3f4ed5] text-sm font-medium">
                    View All
                </button>
            </div>
            <div class="space-y-4">
                {{-- <div class="flex items-start">
                    <div class="bg-[#515DEF]/10 p-2 rounded-full mr-3">
                        <i class="fas fa-sign-in-alt text-[#515DEF] text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Logged in</p>
                        <p class="text-xs text-gray-500">{{ $admin->last_login_at ? $admin->last_login_at->format('M d, h:i A') : 'Never' }}</p>
                    </div>
                </div> --}}
                <div class="flex items-start">
                    <div class="bg-[#515DEF]/10 p-2 rounded-full mr-3">
                        <i class="fas fa-user-edit text-[#515DEF] text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Updated profile</p>
                        <p class="text-xs text-gray-500">{{ $admin->updated_at->format('M d, h:i A') }}</p>
                    </div>
                </div>
                {{-- <div class="flex items-start">
                    <div class="bg-[#515DEF]/10 p-2 rounded-full mr-3">
                        <i class="fas fa-key text-[#515DEF] text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Password changed</p>
                        <p class="text-xs text-gray-500">3 months ago</p>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection