@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $menuItem->name }}</h1>
    <p>{{ $menuItem->description }}</p>
    <p>Price: ${{ $menuItem->price }}</p>
    <p>Timeslots Availability: {{ $menuItem->timeslots_availability }}</p>
    <!-- Add more details here as needed -->
</div>
@endsection 