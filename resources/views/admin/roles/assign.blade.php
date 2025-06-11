@extends('layouts.admin')

@section('content')
<form action="{{ route('roles.assign', $user->id) }}" method="POST">
    @csrf
    <select name="role_id" required>
        @foreach($roles as $role)
            <option value="{{ $role->id }}">{{ $role->name }}</option>
        @endforeach
    </select>
    <button type="submit">Assign Role</button>
</form>
@endsection
