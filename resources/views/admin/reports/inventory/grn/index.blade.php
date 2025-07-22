
@extends('layouts.admin')
@section('header-title', 'GRN Reports')
@section('content')
<div class="container mx-auto px-4 py-8">
            <div class="rounded-lg">
            <x-nav-buttons :items="[
                ['name' => '<< Back', 'link' => route('admin.reports.index')],
                ['name' => 'Inventory Report', 'link' => route('admin.reports.inventory.index')],
                ['name' => 'Goods Transfer Note Report', 'link' => route('admin.reports.inventory.gtn')],
                ['name' => 'Goods Receipt Note Report', 'link' => route('admin.reports.inventory.grn')],
                ['name' => 'Stock Release Note Report', 'link' => route('admin.reports.inventory.srn')],
                ['name' => 'Stock Report', 'link' => route('admin.reports.inventory.stock')],
                // ['name' => 'Inventory items Report', 'link' => route('admin.reports.inventory.items.index')],
            ]" active="Goods Receipt Note Report" />
        </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">GRN Report index - blade</h2>
        <div class="h-64 bg-gray-100 rounded flex items-center justify-center">
            <!-- Placeholder for GRN chart -->
            <p class="text-gray-500">GRN chart will be displayed here</p>
        </div>
    </div>
</div>
@endsection
