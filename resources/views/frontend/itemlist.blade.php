@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Item List</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Category ID</th>
                <th>Name</th>
                <th>Unicode Name</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->item_category_id }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->unicode_name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection 