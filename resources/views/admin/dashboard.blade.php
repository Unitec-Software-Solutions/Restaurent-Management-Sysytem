@extends('layouts.main')

@section('header-title', 'Dashboard')

@section('breadcrumb')
    <li aria-current="page">
        <div class="flex items-center">
            <svg class="w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4" />
            </svg>
            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Dashboard</span>
        </div>
    </li>
@endsection

@section('content')
@php $admin = auth('admin')->user(); @endphp
    <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
    <p>Welcome to the admin dashboard!</p>
    <a href="{{ route('admin.reservations.index') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Reservations</a>
    </div>
@endsection

@push('scripts')

@endpush