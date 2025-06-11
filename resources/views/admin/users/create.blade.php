@extends('layouts.admin')

@section('content')
<form action="{{ route('users.store', $branch->id) }}" method="POST">
    @csrf
    <input type="text" name="name" placeholder="User Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Create User</button>
</form>
@endsection