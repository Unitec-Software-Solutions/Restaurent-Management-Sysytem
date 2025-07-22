@extends('layouts.admin')

@section('title', 'Inventory Reports Dashboard')
@section('header-title', 'Inventory Reports Dashboard')

@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Navigation Buttons -->
        <div class="rounded-lg">
            <x-nav-buttons :items="[
                ['name' => '<< Back', 'link' => route('admin.reports.index')],
            ['name' => 'Inventory Report', 'link' => route('admin.reports.inventory.index')],
            ['name' => 'Stock Report', 'link' => route('admin.reports.inventory.stock')],
            ['name' => 'Category Report', 'link' => route('admin.reports.inventory.category')],
            ['name' => 'Goods Transfer Note Report', 'link' => route('admin.reports.inventory.gtn')],
            ['name' => 'Goods Receipt Note Report', 'link' => route('admin.reports.inventory.grn')],
            ['name' => 'Stock Release Note Report', 'link' => route('admin.reports.inventory.srn')],
                // ['name' => 'Inventory items Report', 'link' => route('admin.reports.inventory.items.index')],
            ]" active="Inventory Report" />
        </div>


    </div>
@endsection
