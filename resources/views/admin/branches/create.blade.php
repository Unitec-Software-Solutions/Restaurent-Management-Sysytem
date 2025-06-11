@extends('layouts.admin')

@section('content')
<form action="{{ route('branches.store', $organization->id) }}" method="POST">
    @csrf
    <input type="text" name="name" placeholder="Branch Name" required>
    <input type="text" name="address" placeholder="Address" required>
    <input type="text" name="phone" placeholder="Phone" required>
    <input type="time" name="opening_time" placeholder="Opening Time" required>
    <input type="time" name="closing_time" placeholder="Closing Time" required>
    <input type="number" name="total_capacity" placeholder="Total Capacity" required>
    <input type="number" name="reservation_fee" placeholder="Reservation Fee" required>
    <input type="number" name="cancellation_fee" placeholder="Cancellation Fee" required>
    <button type="submit">Create Branch</button>
</form>
@endsection