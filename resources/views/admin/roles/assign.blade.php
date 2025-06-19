@extends('layouts.admin')

@section('content')
<div class="bg-white rounded shadow p-6 max-w-md mx-auto">
    <form action="{{ route('roles.assign', $user->id) }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="role_id" class="block text-sm font-medium text-gray-700">Select Role</label>
            <select name="role_id" id="role_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Assign Role</button>
        </div>
    </form>
</div>
@endsection
