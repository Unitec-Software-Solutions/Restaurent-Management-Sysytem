@extends('layouts.admin')

@section('header-title', 'Supplier Payments Management')
@section('content')
<div class="p-4 rounded-lg">
    <!-- Header with navigation buttons -->
    <x-nav-buttons :items="[
        ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
        ['name' => 'Items Management', 'link' => route('admin.inventory.items.index')],
        ['name' => 'Stocks Management', 'link' => route('admin.inventory.stock.index')],
        ['name' => 'Transactions Management', 'link' => route('admin.inventory.stock.transactions.index')],
        ['name' => 'Suppliers Management', 'link' => route('admin.suppliers.index')],
        ['name' => 'Supplier Payments', 'link' => route('admin.payments.index')],
    ]" active="Supplier Payments" />


    <!-- Payments Table -->
    
   
    Create payemt 
    



</div>
@endsection