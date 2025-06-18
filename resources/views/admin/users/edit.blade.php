@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="bg-white rounded shadow p-6">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch (Optional)</label>
                <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $user->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="role_id" class="block text-sm font-medium text-gray-700">User Role</label>
                <select id="role_id" name="role_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        @if($role->name === 'Super Admin' && !auth('admin')->user()->isSuperAdmin())
                            @continue
                        @endif
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-medium">Password <span class="text-gray-500 text-xs">(leave blank to keep current)</span></label>
                <div class="relative">
                    <input type="password" name="password" id="password" placeholder="Enter new password"
                           class="w-full border rounded px-3 py-2 pr-10">
                    <button type="button" onclick="togglePassword('password')" class="absolute right-2 top-2 text-gray-500">
                        👁️
                    </button>
                </div>
                @error('password')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-medium">Confirm Password</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Re-enter new password"
                           class="w-full border rounded px-3 py-2 pr-10">
                    <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-2 top-2 text-gray-500">
                        👁️
                    </button>
                </div>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <a href="{{ route('users.index') }}" class="mr-3 bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Cancel</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Update User</button>
        </div>
    </form>
</div>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endsection