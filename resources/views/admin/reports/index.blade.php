@extends('layouts.admin')

@section('title', 'Reports Dashboard')
@section('header-title', 'Reports Dashboard')

@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Navigation Buttons -->
        <div class="rounded-lg">
            <x-nav-buttons :items="[
                ['name' => 'Dashboard', 'link' => route('admin.reports.index')],
                ['name' => 'Sales Report', 'link' => route('admin.reports.sales.index')],
                ['name' => 'Inventory Report', 'link' => route('admin.reports.inventory.index')],
            ]" active="Dashboard" />
        </div>


    </div>
@endsection
