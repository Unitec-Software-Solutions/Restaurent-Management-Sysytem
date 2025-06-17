@extends('layouts.admin')
@section('title', 'Subscription Plan Details')
@section('content')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <h1 class="text-2xl font-bold mb-6">Subscription Plan Details</h1>
    <div class="bg-white rounded-xl shadow p-6">
        <ul class="mb-4">
            <li><strong>Name:</strong> {{ $subscriptionPlan->name }}</li>
            <li><strong>Modules:</strong> 
                {{ implode(', ', is_array($subscriptionPlan->modules) ? $subscriptionPlan->modules : json_decode($subscriptionPlan->modules, true) ?? []) }}
            </li>
            <li><strong>Price:</strong> {{ number_format($subscriptionPlan->price/100, 2) }} {{ $subscriptionPlan->currency }}</li>
            <li><strong>Description:</strong> {{ $subscriptionPlan->description }}</li>
            <li><strong>Created At:</strong> {{ $subscriptionPlan->created_at }}</li>
            <li><strong>Updated At:</strong> {{ $subscriptionPlan->updated_at }}</li>
        </ul>
        <a href="{{ route('admin.subscription-plans.edit', $subscriptionPlan->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Edit</a>
        <a href="{{ route('admin.subscription-plans.index') }}" class="ml-4 text-gray-600 hover:underline">Back</a>
    </div>
</div>
@endsection