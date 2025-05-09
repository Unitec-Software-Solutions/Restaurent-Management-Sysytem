@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center">Our Menu</h1>
    <ul>
        @foreach ($categories as $category)
            <li>{{ $category->name }}</li>
        @endforeach
    </ul>
</div>
@endsection 