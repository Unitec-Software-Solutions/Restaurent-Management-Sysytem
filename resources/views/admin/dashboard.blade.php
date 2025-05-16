@extends('layouts.admin')
@section('content')
@php $admin = auth('admin')->user(); @endphp
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
    <p>Welcome to the admin dashboard!</p>
    <a href="{{ route('admin.reservations.index') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Reservations</a>
</div>
@endsection