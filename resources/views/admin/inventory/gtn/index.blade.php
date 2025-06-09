@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Goods Transfer Notes</h2>
    <a href="{{ route('admin.gtn.create') }}" class="btn btn-primary mb-3">Create GTN</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>GTN Number</th>
                <th>From</th>
                <th>To</th>
                <th>Transfer Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gtns as $gtn)
                <tr>
                    <td>{{ $gtn->gtn_number }}</td>
                    <td>{{ $gtn->fromBranch->name ?? '-' }}</td>
                    <td>{{ $gtn->toBranch->name ?? '-' }}</td>
                    <td>{{ $gtn->transfer_date }}</td>
                    <td>{{ $gtn->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
