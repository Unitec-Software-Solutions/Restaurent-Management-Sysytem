@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">All Submitted Orders</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            @foreach($branches as $branch)
                <a href="{{ route('admin.orders.branch', $branch) }}" class="bg-gray-100 p-4 rounded-lg hover:bg-gray-200">
                    <h3 class="font-semibold">{{ $branch->name }}</h3>
                    <p>{{ $branch->orders()->count() }} orders</p>
                </a>
            @endforeach
        </div>
        <!-- Orders Table with branch details -->
    </div>
</div>
@endsection